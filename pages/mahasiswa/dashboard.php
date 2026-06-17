<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Mahasiswa');

$page_title = 'Dashboard';
$current_page = 'dashboard';

$user_id = $_SESSION['user_id'];

$total_organisasi = db()->query("SELECT COUNT(*) FROM organisasi WHERE status = 'aktif'")->fetchColumn();
$stmt = db()->prepare("SELECT COUNT(*) FROM anggota WHERE user_id = ? AND status = 'aktif'");
$stmt->execute([$user_id]); $my_organisasi = $stmt->fetchColumn();
$stmt = db()->prepare("SELECT COUNT(*) FROM peserta_kegiatan WHERE user_id = ? AND status = 'terdaftar'");
$stmt->execute([$user_id]); $my_kegiatan = $stmt->fetchColumn();

$stmt = db()->prepare("SELECT p.*, o.nama as org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id = o.id WHERE p.tipe = 'global' OR p.organisasi_id IN (SELECT organisasi_id FROM anggota WHERE user_id = ?) ORDER BY p.created_at DESC LIMIT 5");
$stmt->execute([$user_id]); $pengumuman = $stmt->fetchAll();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-primary/10 text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-on-surface"><?= $total_organisasi ?></p>
                        <p class="text-sm text-on-surface-variant">Organisasi Tersedia</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-secondary/10 text-secondary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-on-surface"><?= $my_organisasi ?></p>
                        <p class="text-sm text-on-surface-variant">Organisasi Saya</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-accent/20 text-accent-dark">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-on-surface"><?= $my_kegiatan ?></p>
                        <p class="text-sm text-on-surface-variant">Kegiatan Terdaftar</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Pengumuman Terbaru</h3>
                <a href="<?= BASE_URL ?>/pages/mahasiswa/notifikasi.php" class="text-sm font-medium text-primary hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-outline-variant">
                <?php foreach ($pengumuman as $p): ?>
                <div class="px-6 py-4 hover:bg-surface-low transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 w-2 h-2 rounded-full bg-primary flex-shrink-0"></div>
                        <div>
                            <p class="font-semibold text-sm text-on-surface"><?= e($p['judul']) ?></p>
                            <p class="text-xs text-on-surface-variant mt-0.5"><?= e($p['org_nama'] ?? 'Umum') ?> &middot; <?= e(date('d M Y', strtotime($p['created_at']))) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($pengumuman)): ?>
                <div class="px-6 py-8 text-center text-on-surface-variant text-sm">Belum ada pengumuman</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
