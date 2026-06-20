<?php
declare(strict_types=1);require __DIR__.'/../includes/bootstrap.php';require_role('student');set_flash('info','Student accounts and module registrations are managed by the administrator.');redirect('student/modules.php');
