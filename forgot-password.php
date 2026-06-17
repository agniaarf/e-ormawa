<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) redirect('/');

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $result = send_reset_token(trim($_POST['email'] ?? ''));
        if ($result['success']) {
            $message = $result['message'];
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
    <title>Lupa Password - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
        <h2 class="text-xl font-bold text-on-surface mb-1">Lupa Password</h2>
        <p class="text-sm text-on-surface-variant mb-6">Masukkan email Anda, kami akan kirimkan link reset.</p>
        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <?= csrf_input() ?>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1.5">Email</label>
                <input type="email" name="email" required class="form-input" placeholder="nama@email.com">
            </div>
            <button type="submit" class="btn-primary w-full">Kirim Link Reset</button>
        </form>
        <p class="mt-6 text-center text-sm text-on-surface-variant">
            <a href="<?= BASE_URL ?>/login.php" class="font-semibold text-primary hover:underline">Kembali ke Login</a>
        </p>
    </div>
</div>
</body>
</html>
