<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Admin Organisasi');

$page_title = 'Dashboard';
$current_page = 'dashboard';

$user_id = $_SESSION['user_id'];
$stmt = db()->prepare("SELECT o.* FROM organisasi o JOIN anggota a ON o.id = a.organisasi_id WHERE a.user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$organisasi = $stmt->fetch();
$org_id = $organisasi['id'] ?? 0;

$stmt = db()->prepare("SELECT COUNT(*) FROM anggota WHERE organisasi_id = ? AND status = 'aktif'");
$stmt->execute([$org_id]); $total_anggota = $stmt->fetchColumn();
$stmt = db()->prepare("SELECT COUNT(*) FROM pendaftaran_organisasi WHERE organisasi_id = ? AND status = 'menunggu'");
$stmt->execute([$org_id]); $total_pendaftar = $stmt->fetchColumn();
$stmt = db()->prepare("SELECT COUNT(*) FROM kegiatan WHERE organisasi_id = ? AND status IN ('rencana','berlangsung')");
$stmt->execute([$org_id]); $total_kegiatan = $stmt->fetchColumn();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <?php if ($organisasi): ?>
        <div class="bg-gradient-to-r from-primary to-primary-light rounded-2xl p-6 text-white shadow-card">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold"><?= e($organisasi['nama']) ?></h2>
                    <p class="text-white/80 text-sm"><?= e($organisasi['singkatan'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-primary/10 text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-on-surface"><?= $total_anggota ?></p>
                        <p class="text-sm text-on-surface-variant">Anggota Aktif</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-accent/20 text-accent-dark">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 3h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-on-surface"><?= $total_pendaftar ?></p>
                        <p class="text-sm text-on-surface-variant">Pendaftar Menunggu</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-secondary/10 text-secondary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-on-surface"><?= $total_kegiatan ?></p>
                        <p class="text-sm text-on-surface-variant">Kegiatan Aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
