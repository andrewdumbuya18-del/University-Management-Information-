<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_role('admin');
$page_title = 'User Accounts - SMIS';

$roles = ['student', 'lecturer', 'finance', 'admin'];
$action = (string) query('action');
$edit_id = (int) query('id');
$errors = [];

if (is_post()) {
    enforce_csrf();
    $operation = (string) post('action');
    $id = (int) post('id');

    if (in_array($operation, ['activate', 'deactivate'], true)) {
        if ($id === (int) current_user()['id'] && $operation === 'deactivate') {
            respond_error('You cannot deactivate your own account.');
        }
        db_execute('UPDATE users SET status = :status WHERE id = :id', [
            'status' => $operation === 'activate' ? 'active' : 'inactive',
            'id' => $id,
        ]);
        audit_log($operation, 'users', (string) $id);
        respond_success('Account status updated.', 'admin/users.php');
    }

    if ($operation === 'reset_password') {
        $password = (string) post('password');
        if (strlen($password) < 8) {
            respond_error('The temporary password must contain at least 8 characters.');
        }
        db_execute('UPDATE users SET password = :password WHERE id = :id', [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
        audit_log('reset_password', 'users', (string) $id);
        respond_success('Password reset successfully.', 'admin/users.php');
    }

    if ($operation === 'save') {
        $name = trim((string) post('name'));
        $email = trim((string) post('email'));
        $role = (string) post('role');
        $password = (string) post('password');
        $identifier = trim((string) post('identifier'));
        $phone = trim((string) post('phone'));
        $department = trim((string) post('department'));
        $errors = validate_required(compact('name', 'email', 'role'), ['name' => 'Name', 'email' => 'Email', 'role' => 'Role']);
        $errors += validate_email_field(['email' => $email]);
        $errors += validate_in(['role' => $role], 'role', $roles, 'Role');
        if ($id === 0 && strlen($password) < 8) $errors['password'] = 'Password must contain at least 8 characters.';
        if (in_array($role, ['student', 'lecturer', 'finance'], true) && $identifier === '') $errors['identifier'] = 'Student or staff number is required.';
        $duplicate = (int) db_value('SELECT COUNT(*) FROM users WHERE email = :email AND id <> :id', ['email' => $email, 'id' => $id]);
        if ($duplicate) $errors['email'] = 'That email address is already in use.';

        if (!$errors) {
            db()->beginTransaction();
            try {
                if ($id > 0) {
                    db_execute('UPDATE users SET name=:name,email=:email,role=:role WHERE id=:id', compact('name', 'email', 'role', 'id'));
                    if ($password !== '') db_execute('UPDATE users SET password=:password WHERE id=:id', ['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
                } else {
                    db_execute('INSERT INTO users(name,email,password,role,status) VALUES(:name,:email,:password,:role,"active")', [
                        'name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role,
                    ]);
                    $id = (int) db_insert_id();
                }
                if ($role === 'student') {
                    db_execute('INSERT INTO students(user_id,student_number,phone) VALUES(:uid,:num,:phone) ON DUPLICATE KEY UPDATE student_number=VALUES(student_number),phone=VALUES(phone)', ['uid'=>$id,'num'=>$identifier,'phone'=>$phone]);
                } elseif ($role === 'lecturer') {
                    db_execute('INSERT INTO lecturers(user_id,staff_number,department,phone) VALUES(:uid,:num,:department,:phone) ON DUPLICATE KEY UPDATE staff_number=VALUES(staff_number),department=VALUES(department),phone=VALUES(phone)', ['uid'=>$id,'num'=>$identifier,'department'=>$department,'phone'=>$phone]);
                } elseif ($role === 'finance') {
                    db_execute('INSERT INTO finance_officers(user_id,staff_number,phone) VALUES(:uid,:num,:phone) ON DUPLICATE KEY UPDATE staff_number=VALUES(staff_number),phone=VALUES(phone)', ['uid'=>$id,'num'=>$identifier,'phone'=>$phone]);
                }
                db()->commit();
                audit_log($edit_id ? 'update' : 'create', 'users', (string) $id);
                respond_success('User account saved successfully.', 'admin/users.php');
            } catch (Throwable $e) {
                db()->rollBack();
                $errors['save'] = 'The account could not be saved. Check that the student or staff number is unique.';
            }
        }
    }
}

$editing = $edit_id ? db_one('SELECT u.*, COALESCE(s.student_number,l.staff_number,f.staff_number) identifier, COALESCE(s.phone,l.phone,f.phone) phone, l.department FROM users u LEFT JOIN students s ON s.user_id=u.id LEFT JOIN lecturers l ON l.user_id=u.id LEFT JOIN finance_officers f ON f.user_id=u.id WHERE u.id=:id', ['id'=>$edit_id]) : null;
$search = trim((string) query('search'));
$filter_role = (string) query('role');
$where = []; $params = [];
if ($search !== '') { $where[]='(u.name LIKE :n OR u.email LIKE :e)'; $params += ['n'=>"%$search%",'e'=>"%$search%"]; }
if (in_array($filter_role,$roles,true)) { $where[]='u.role=:role'; $params['role']=$filter_role; }
$sql_where = $where ? ' WHERE '.implode(' AND ',$where) : '';
$users = db_all('SELECT u.id,u.name,u.email,u.role,u.status,u.last_login_at,COALESCE(s.student_number,l.staff_number,f.staff_number) identifier FROM users u LEFT JOIN students s ON s.user_id=u.id LEFT JOIN lecturers l ON l.user_id=u.id LEFT JOIN finance_officers f ON f.user_id=u.id'.$sql_where.' ORDER BY u.created_at DESC', $params);
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>User Accounts</h1><p>Administrator-controlled access for every system role.</p></div><a class="btn btn-primary" href="<?= url('admin/users.php?action=create') ?>">Create account</a></div>

<?php if ($action === 'create' || $editing): ?>
<div class="card"><div class="card-header"><?= $editing ? 'Edit account' : 'Create account' ?></div><div class="card-body">
<?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($editing['id']??0) ?>">
<div class="grid grid-col-2">
<div class="form-group"><label>Full name</label><input class="form-control" name="name" required value="<?= e(post('name',$editing['name']??'')) ?>"></div>
<div class="form-group"><label>Email address</label><input class="form-control" type="email" name="email" required value="<?= e(post('email',$editing['email']??'')) ?>"></div>
<div class="form-group"><label>Role</label><select class="form-control" name="role"><?php foreach($roles as $role): ?><option value="<?= $role ?>" <?= post('role',$editing['role']??'student')===$role?'selected':'' ?>><?= ucfirst($role) ?></option><?php endforeach ?></select></div>
<div class="form-group"><label><?= $editing ? 'New password (optional)' : 'Temporary password' ?></label><input class="form-control" type="password" name="password" <?= $editing?'':'required' ?>></div>
<div class="form-group"><label>Student / staff number</label><input class="form-control" name="identifier" value="<?= e(post('identifier',$editing['identifier']??'')) ?>"></div>
<div class="form-group"><label>Phone</label><input class="form-control" name="phone" value="<?= e(post('phone',$editing['phone']??'')) ?>"></div>
<div class="form-group"><label>Department (lecturers)</label><input class="form-control" name="department" value="<?= e(post('department',$editing['department']??'')) ?>"></div>
</div><button class="btn btn-primary">Save account</button> <a class="btn btn-secondary" href="<?= url('admin/users.php') ?>">Cancel</a></form>
</div></div>
<?php endif ?>

<div class="card"><div class="card-body"><form method="get" class="grid grid-col-3">
<div class="form-group"><label>Search</label><input class="form-control" name="search" value="<?= e($search) ?>" placeholder="Name or email"></div>
<div class="form-group"><label>Role</label><select class="form-control" name="role"><option value="">All roles</option><?php foreach($roles as $role): ?><option value="<?= $role ?>" <?= $filter_role===$role?'selected':'' ?>><?= ucfirst($role) ?></option><?php endforeach ?></select></div>
<div class="form-group" style="align-self:end"><button class="btn btn-secondary">Filter accounts</button></div></form></div></div>
<div class="card"><div class="card-body"><table class="table"><thead><tr><th>Name</th><th>Identifier</th><th>Role</th><th>Status</th><th>Last login</th><th>Actions</th></tr></thead><tbody>
<?php foreach($users as $account): ?><tr><td><strong><?= e($account['name']) ?></strong><br><small><?= e($account['email']) ?></small></td><td><?= e($account['identifier']??'-') ?></td><td><?= status_badge($account['role']) ?></td><td><?= status_badge($account['status']) ?></td><td><?= format_datetime($account['last_login_at']) ?></td><td><div class="table-actions">
<a class="action-btn action-edit" href="<?= url('admin/users.php?action=edit&id='.$account['id']) ?>">Edit</a>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $account['status']==='active'?'deactivate':'activate' ?>"><input type="hidden" name="id" value="<?= $account['id'] ?>"><button class="action-btn action-view"><?= $account['status']==='active'?'Deactivate':'Activate' ?></button></form>
<form method="post" onsubmit="return confirm('Reset password to password123?')"><?= csrf_field() ?><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="<?= $account['id'] ?>"><input type="hidden" name="password" value="password123"><button class="action-btn action-delete">Reset password</button></form>
</div></td></tr><?php endforeach ?>
</tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
