<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

require_login();
$page_title = 'My Profile - SMIS';

$user = current_user();
$profile_type = null;
$profile_data = null;

if ($user['role'] === 'student') {
    $profile_data = db_one('SELECT * FROM students WHERE user_id = :id', ['id' => $user['id']]);
    $profile_type = 'student';
} elseif ($user['role'] === 'lecturer') {
    $profile_data = db_one('SELECT * FROM lecturers WHERE user_id = :id', ['id' => $user['id']]);
    $profile_type = 'lecturer';
} elseif ($user['role'] === 'finance') {
    $profile_data = db_one('SELECT * FROM finance_officers WHERE user_id = :id', ['id' => $user['id']]);
    $profile_type = 'finance';
}

?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
    <h1>My Profile</h1>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">Profile Information</div>
    <div class="card-body">
        <form style="display: grid; gap: 20px;">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" class="form-control" value="<?= e($user['name']) ?>" disabled>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
            </div>

            <div class="form-group">
                <label>Role</label>
                <input type="text" class="form-control" value="<?= ucfirst(e($user['role'])) ?>" disabled>
            </div>

            <div class="form-group">
                <label>Account Status</label>
                <input type="text" class="form-control" value="<?= ucfirst(e($user['status'])) ?>" disabled>
            </div>

            <?php if ($profile_type === 'student' && $profile_data): ?>
                <hr>
                <h3>Student Details</h3>
                
                <div class="form-group">
                    <label>Student Number</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['student_number']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['gender'] ?? 'N/A') ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['phone'] ?? 'N/A') ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['address'] ?? 'N/A') ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="text" class="form-control" value="<?= $profile_data['date_of_birth'] ? formatDate($profile_data['date_of_birth']) : 'N/A' ?>" disabled>
                </div>
            <?php elseif ($profile_type === 'lecturer' && $profile_data): ?>
                <hr>
                <h3>Lecturer Details</h3>
                
                <div class="form-group">
                    <label>Staff Number</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['staff_number']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['department'] ?? 'N/A') ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['phone'] ?? 'N/A') ?>" disabled>
                </div>
            <?php elseif ($profile_type === 'finance' && $profile_data): ?>
                <hr>
                <h3>Finance Officer Details</h3>
                
                <div class="form-group">
                    <label>Staff Number</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['staff_number']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" value="<?= e($profile_data['phone'] ?? 'N/A') ?>" disabled>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Last Login</label>
                <input type="text" class="form-control" value="<?= $user['last_login_at'] ? formatDateTime($user['last_login_at']) : 'Never' ?>" disabled>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="<?= url('settings.php') ?>" class="btn btn-secondary">Edit Settings</a>
                <a href="<?= url('/') ?>" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 20px;
}

.page-header h1 {
    margin-bottom: 5px;
}

h3 {
    font-size: 16px;
    margin: 15px 0 10px 0;
    color: #333;
}

hr {
    border: none;
    border-top: 1px solid #dee2e6;
    margin: 20px 0;
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
