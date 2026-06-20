<?php
$user = current_user();
$unread_count = db_value('SELECT COUNT(*) FROM notifications WHERE user_id = :id AND is_read = 0', ['id' => $user['id'] ?? null]) ?? 0;
$app_layout_open = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? app_config('short_name')) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="<?= url('/') ?>" class="brand-link">
                    <span class="brand-text"><?= e(app_config('short_name')) ?></span>
                </a>
            </div>

            <div class="navbar-menu">
                <div class="navbar-menu-items">
                    <?php if ($user): ?>
                        <a href="<?= url(role_home($user['role'])) ?>" class="nav-link">Dashboard</a>
                        
                        <div class="nav-notifications">
                            <a href="<?= url('notifications.php') ?>" class="nav-link notification-link">
                                <span class="notification-icon">🔔</span>
                                <?php if ($unread_count > 0): ?>
                                    <span class="notification-badge"><?= min($unread_count, 9) ?></span>
                                <?php endif; ?>
                            </a>
                        </div>

                        <div class="nav-user">
                            <div class="user-menu">
                                <button class="user-menu-toggle">
                                    <span><?= e($user['name']) ?></span>
                                    <span class="role-badge"><?= e($user['role']) ?></span>
                                </button>
                                <div class="user-menu-dropdown">
                                    <a href="<?= url('profile.php') ?>" class="user-menu-item">My Profile</a>
                                    <a href="<?= url('settings.php') ?>" class="user-menu-item">Settings</a>
                                    <hr class="user-menu-divider">
                                    <a href="<?= url('logout.php') ?>" class="user-menu-item">Logout</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('login.php') ?>" class="btn btn-sm btn-primary">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-wrapper">
        <?php if ($user): ?>
            <aside class="sidebar">
                <nav class="sidebar-nav">
                    <?php
                    $nav_items = [];
                    
                    if ($user['role'] === 'admin') {
                        $nav_items = [
                            ['url' => 'admin/dashboard.php', 'label' => '📊 Dashboard'],
                            ['url' => 'admin/users.php', 'label' => '👥 Users'],
                            ['url' => 'admin/classes.php', 'label' => '🏫 Classes'],
                            ['url' => 'admin/semesters.php', 'label' => '📅 Semesters'],
                            ['url' => 'admin/modules.php', 'label' => '📚 Modules'],
                            ['url' => 'admin/students.php', 'label' => '🎓 Students'],
                            ['url' => 'admin/lecturers.php', 'label' => '👨‍🏫 Lecturers'],
                            ['url' => 'admin/attendance.php', 'label' => '📍 Attendance'],
                            ['url' => 'admin/grades.php', 'label' => '📊 Grades'],
                            ['url' => 'admin/clearances.php', 'label' => '✅ Clearances'],
                            ['url' => 'admin/reports.php', 'label' => '📈 Reports'],
                            ['url' => 'admin/logs.php', 'label' => '📋 Activity Logs'],
                        ];
                    } elseif ($user['role'] === 'student') {
                        $nav_items = [
                            ['url' => 'student/dashboard.php', 'label' => '📊 Dashboard'],
                            ['url' => 'student/modules.php', 'label' => '📚 My Modules'],
                            ['url' => 'student/grades.php', 'label' => '📊 Grades'],
                            ['url' => 'student/attendance.php', 'label' => '📍 Attendance'],
                            ['url' => 'student/clearances.php', 'label' => '✅ Clearances'],
                        ];
                    } elseif ($user['role'] === 'lecturer') {
                        $nav_items = [
                            ['url' => 'lecturer/dashboard.php', 'label' => '📊 Dashboard'],
                            ['url' => 'lecturer/modules.php', 'label' => '📚 My Modules'],
                            ['url' => 'lecturer/attendance.php', 'label' => '📍 Attendance'],
                            ['url' => 'lecturer/grades.php', 'label' => '📊 Grades'],
                            ['url' => 'lecturer/students.php', 'label' => '👥 Students'],
                        ];
                    } elseif ($user['role'] === 'finance') {
                        $nav_items = [
                            ['url' => 'finance/dashboard.php', 'label' => '📊 Dashboard'],
                            ['url' => 'finance/students.php', 'label' => '👥 Students'],
                            ['url' => 'finance/clearances.php', 'label' => '✅ Clearances'],
                            ['url' => 'finance/reports.php', 'label' => '📊 Reports'],
                        ];
                    }
                    
                    $current_page = basename($_SERVER['REQUEST_URI'] ?? '', '.php');
                    ?>
                    
                    <?php foreach ($nav_items as $item): ?>
                        <?php if (!is_file(BASE_PATH . '/' . $item['url'])) continue; ?>
                        <a href="<?= url($item['url']) ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', $item['url']) !== false ? 'active' : '' ?>">
                            <?= $item['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>
        <?php endif; ?>

        <main class="main-content">
            <?php
            $flashes = flashes();
            foreach ($flashes as $flash):
            ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="alert-close" data-dismiss="alert">×</button>
                </div>
            <?php endforeach; ?>
