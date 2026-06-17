<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) redirect('/');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            $result = register_user(
                trim($_POST['nama'] ?? ''),
                $email,
                trim($_POST['nim'] ?? ''),
                $_POST['password'] ?? '',
                3,
                trim($_POST['no_hp'] ?? ''),
                trim($_POST['jurusan'] ?? ''),
                trim($_POST['angkatan'] ?? '')
            );
                if ($result['success']) {
                set_flash('success', $result['message']);
                redirect('/login.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#244539', light: '#3B5D50' }, accent: { DEFAULT: '#D4B483' } }, fontFamily: { jakarta: ['"Plus Jakarta Sans"', 'sans-serif'] } } } }
    </script>
    <style>body{font-family:'Plus Jakarta Sans',sans-serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-surface py-8">
<div class="w-full max-w-lg p-4">
    <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-8">
        <div class="flex items-center gap-3 justify-center mb-8">
            <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center">
                <span class="text-accent font-extrabold text-lg">EO</span>
            </div>
            <span class="text-2xl font-extrabold text-primary tracking-tight">E-ORMAWA</span>
        </div>
        <h2 class="text-xl font-bold text-on-surface mb-1">Buat Akun</h2>
        <p class="text-sm text-on-surface-variant mb-6">Isi data diri Anda untuk mendaftar</p>
        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <?= csrf_input() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama" required class="form-input" placeholder="Nama lengkap">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">NIM</label>
                    <input type="text" name="nim" required class="form-input" placeholder="Nomor Induk Mahasiswa">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1.5">Email</label>
                <input type="email" name="email" required class="form-input" placeholder="nama@email.com">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Jurusan</label>
                    <input type="text" name="jurusan" class="form-input" placeholder="Jurusan">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Angkatan</label>
                    <input type="text" name="angkatan" class="form-input" placeholder="Contoh: 2024">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1.5">No. HP</label>
                <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1.5">Password</label>
                <input type="password" name="password" required minlength="6" class="form-input" placeholder="Minimal 6 karakter">
            </div>
            <button type="submit" class="btn-primary w-full">Daftar</button>
        </form>
        <p class="mt-6 text-center text-sm text-on-surface-variant">
            Sudah punya akun? <a href="<?= BASE_URL ?>/login.php" class="font-semibold text-primary hover:underline">Masuk</a>
        </p>
    </div>
</div>
</body>
</html>
