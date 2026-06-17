<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'Super Admin') redirect('/pages/super_admin/dashboard.php');
    if ($role === 'Admin Organisasi') redirect('/pages/admin_organisasi/dashboard.php');
    redirect('/pages/mahasiswa/dashboard.php');
}

$error = '';
$flash = get_flash(); // consume any leftover flash (e.g. from require_login redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $result = login_user($identifier, $password);
        if ($result['success']) {
            $role = $result['user']['role_name'];
            if ($role === 'Super Admin') redirect('/pages/super_admin/dashboard.php');
            if ($role === 'Admin Organisasi') redirect('/pages/admin_organisasi/dashboard.php');
            redirect('/pages/mahasiswa/dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#244539', light: '#3B5D50' }, accent: { DEFAULT: '#D4B483' } }, fontFamily: { jakarta: ['"Plus Jakarta Sans"', 'sans-serif'] } } } }
    </script>
    <style>body{font-family:'Plus Jakarta Sans',sans-serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-surface">
<div class="w-full max-w-md p-8">
    <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-8">
        <div class="flex items-center gap-3 justify-center mb-8">
            <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center">
                <span class="text-accent font-extrabold text-lg">EO</span>
            </div>
            <span class="text-2xl font-extrabold text-primary tracking-tight">E-ORMAWA</span>
        </div>
        <h2 class="text-xl font-bold text-on-surface mb-1">Selamat Datang</h2>
        <p class="text-sm text-on-surface-variant mb-6">Masuk ke akun Anda untuk melanjutkan</p>
        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($flash): ?>
            <div class="mb-4 p-3 rounded-lg <?= $flash['type']==='error' ? 'bg-red-50 text-red-700' : ($flash['type']==='success' ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700') ?> text-sm"><?= e($flash['message']) ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <?= csrf_input() ?>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1.5">Email / NIM</label>
                <input type="text" name="identifier" required class="form-input" placeholder="Masukkan email atau NIM">
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1.5">Password</label>
                <input type="password" name="password" required class="form-input" placeholder="Masukkan password">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-on-surface-variant cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-outline text-primary focus:ring-primary">
                    Ingat saya
                </label>
                <a href="<?= BASE_URL ?>/forgot-password.php" class="text-sm font-medium text-primary hover:underline">Lupa Password?</a>
            </div>
            <button type="submit" class="btn-primary w-full">Masuk</button>
        </form>
        <p class="mt-6 text-center text-sm text-on-surface-variant">
            Belum punya akun? <a href="<?= BASE_URL ?>/register.php" class="font-semibold text-primary hover:underline">Daftar Sekarang</a>
        </p>
    </div>
</div>
</body>
</html>
