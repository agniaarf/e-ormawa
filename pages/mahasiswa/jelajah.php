<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Mahasiswa');

$page_title = 'Jelajah Organisasi';
$current_page = 'jelajah';
$pdo = db();

$list = $pdo->query("SELECT * FROM organisasi WHERE status='aktif' ORDER BY nama")->fetchAll();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($list as $row): ?>
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card card-hover p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm">
                        <?= e(strtoupper(substr($row['nama'], 0, 2))) ?>
                    </div>
                    <div>
                        <h4 class="font-bold text-on-surface"><?= e($row['nama']) ?></h4>
                        <p class="text-xs text-on-surface-variant"><?= e($row['singkatan'] ?? '') ?></p>
                    </div>
                </div>
                <p class="text-sm text-on-surface-variant line-clamp-3 flex-1"><?= e($row['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
                <a href="<?= BASE_URL ?>/pages/mahasiswa/daftar_organisasi.php?id=<?= $row['id'] ?>" class="btn-primary mt-4 text-center">Lihat Detail</a>
            </div>
            <?php endforeach; ?>
            <?php if (empty($list)): ?>
            <div class="col-span-full text-center text-on-surface-variant py-12">Belum ada organisasi tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
