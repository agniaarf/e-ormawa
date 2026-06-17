<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('/login.php');
}

$role = $_SESSION['role'] ?? '';
if ($role === 'Super Admin') redirect('/pages/super_admin/dashboard.php');
if ($role === 'Admin Organisasi') redirect('/pages/admin_organisasi/dashboard.php');
redirect('/pages/mahasiswa/dashboard.php');
