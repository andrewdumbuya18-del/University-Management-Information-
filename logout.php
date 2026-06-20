<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

logout_user();
set_flash('success', 'You have been logged out successfully.');
redirect('login.php');
