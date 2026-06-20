<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_login();

$page_title = 'Dashboard';
$current_page = 'dashboard';
$pdo = db();
$user_id = (int) $_SESSION['user_id'];

if (is_super_admin()) {
    $total_organisasi = $pdo->query("SELECT COUNT(*) FROM organisasi WHERE status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $total_mahasiswa = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id=3 AND status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $total_kegiatan = $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE status='berlangsung' AND deleted_at IS NULL")->fetchColumn();
    $recent_logs = $pdo->query("SELECT al.*, u.nama FROM activity_log al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10")->fetchAll();
} else {
    $orgs = my_organisasi($user_id);
    $total_organisasi = $pdo->query("SELECT COUNT(*) FROM organisasi WHERE status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $my_org_count = count($orgs);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM permintaan_bergabung WHERE user_id=? AND status='menunggu'");
    $stmt->execute([$user_id]); $pending_req = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT p.*, o.nama AS org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id=o.id WHERE p.tipe='global' OR p.organisasi_id IN (SELECT organisasi_id FROM user_organisasi WHERE user_id=? AND status='aktif') ORDER BY p.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]); $pengumuman = $stmt->fetchAll();
}
?>
<?php require __DIR__ . '/../components/head.php'; ?>
<?php require __DIR__ . '/../components/sidebar.php'; ?>
<?php require __DIR__ . '/../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
    <?php if (is_super_admin()): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_organisasi ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Organisasi</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_users ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Pengguna</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_mahasiswa ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Mahasiswa</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_kegiatan ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Kegiatan Aktif</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Aktivitas Terbaru</h3>
                <a href="<?= url('log') ?>" class="text-sm font-medium text-primary hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th class="text-left">Pengguna</th><th class="text-left">Aksi</th><th class="text-left">Detail</th><th class="text-left">Waktu</th></tr></thead>
                    <tbody>
                        <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($log['nama'] ?? 'Sistem') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($log['aksi']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($log['detail'] ?? '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($log['created_at']))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_logs)): ?><tr><td colspan="4" class="text-center text-on-surface-variant py-8">Belum ada aktivitas</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_organisasi ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Organisasi Tersedia</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $my_org_count ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Organisasi Saya</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $pending_req ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Permintaan Menunggu</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Organisasi Saya</h3>
                <a href="<?= url('organisasi') ?>" class="text-sm font-medium text-primary hover:underline">Jelajah Organisasi</a>
            </div>
            <div class="divide-y divide-outline-variant">
                <?php foreach ($orgs as $o): ?>
                <a href="<?= url('organisasi/' . $o['id']) ?>" class="flex items-center justify-between px-6 py-4 hover:bg-surface-low transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm"><?= e(strtoupper(substr($o['nama'],0,2))) ?></div>
                        <div>
                            <p class="font-semibold text-sm text-on-surface"><?= e($o['nama']) ?></p>
                            <p class="text-xs text-on-surface-variant"><?= e($o['singkatan'] ?: '-') ?></p>
                        </div>
                    </div>
                    <span class="badge bg-primary/10 text-primary text-xs"><?= e(org_role_label($o['my_role'])) ?></span>
                </a>
                <?php endforeach; ?>
                <?php if (empty($orgs)): ?>
                <div class="px-6 py-8 text-center text-on-surface-variant text-sm">Anda belum bergabung dengan organisasi mana pun. <a href="<?= url('organisasi') ?>" class="text-primary font-semibold hover:underline">Jelajahi sekarang</a>.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant"><h3 class="font-bold text-on-surface">Pengumuman Terbaru</h3></div>
            <div class="divide-y divide-outline-variant">
                <?php foreach ($pengumuman as $p): ?>
                <div class="px-6 py-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 w-2 h-2 rounded-full bg-primary flex-shrink-0"></div>
                        <div>
                            <p class="font-semibold text-sm text-on-surface"><?= e($p['judul']) ?></p>
                            <p class="text-xs text-on-surface-variant mt-0.5"><?= e($p['org_nama'] ?? 'Umum') ?> &middot; <?= e(date('d M Y', strtotime($p['created_at']))) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($pengumuman)): ?><div class="px-6 py-8 text-center text-on-surface-variant text-sm">Belum ada pengumuman</div><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../components/footer.php'; ?>
