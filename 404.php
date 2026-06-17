<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - E-ORMAWA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#244539', light: '#3B5D50' }, accent: { DEFAULT: '#D4B483' } }, fontFamily: { jakarta: ['"Plus Jakarta Sans"', 'sans-serif'] } } } }
    </script>
    <style>body{font-family:'Plus Jakarta Sans',sans-serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-surface">
    <div class="text-center p-8">
        <div class="w-20 h-20 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        </div>
        <h1 class="text-4xl font-extrabold text-on-surface mb-2">404</h1>
        <p class="text-lg text-on-surface-variant mb-6">Halaman Tidak Ditemukan</p>
        <p class="text-sm text-on-surface-variant mb-8 max-w-md mx-auto">Halaman yang Anda cari tidak tersedia atau telah dipindahkan. Silakan kembali ke beranda.</p>
        <a href="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-lg font-semibold text-sm hover:bg-primary-light transition-colors">Kembali ke Beranda</a>
    </div>
</body>
</html>
