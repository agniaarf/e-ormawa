<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Super Admin');

$page_title = 'Dashboard';
$current_page = 'dashboard';

// Stats
$total_organisasi = db()->query("SELECT COUNT(*) FROM organisasi WHERE status = 'aktif'")->fetchColumn();
$total_users = db()->query("SELECT COUNT(*) FROM users WHERE status = 'aktif'")->fetchColumn();
$total_mahasiswa = db()->query("SELECT COUNT(*) FROM users WHERE role_id = 3 AND status = 'aktif'")->fetchColumn();
$total_kegiatan = db()->query("SELECT COUNT(*) FROM kegiatan WHERE status = 'berlangsung'")->fetchColumn();

$recent_logs = db()->query("SELECT al.*, u.nama FROM activity_log al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10")->fetchAll();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-primary/10 text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant bg-surface-low px-2 py-1 rounded">Aktif</span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_organisasi ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Organisasi</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-secondary/10 text-secondary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant bg-surface-low px-2 py-1 rounded">Aktif</span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_users ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Pengguna</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-accent/20 text-accent-dark">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.499 5.221 50.552 50.552 0 00-2.658.813m-15.482 0a50.697 50.697 0 0012.84 3.178M12 12.904a48.627 48.627 0 00-8.232 4.41 60.436 60.436 0 00.491-6.347m8.232 1.963a60.436 60.436 0 00.491 6.347 48.627 48.627 0 008.232-4.41m0 0a60.46 60.46 0 00-.491-6.347m0 0a50.57 50.57 0 002.658.813 59.905 59.905 0 0010.499-5.221 50.552 50.552 0 00-2.658-.813"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant bg-surface-low px-2 py-1 rounded">Aktif</span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_mahasiswa ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Mahasiswa</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-primary-fixed/20 text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant bg-surface-low px-2 py-1 rounded">Berlangsung</span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_kegiatan ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Kegiatan Aktif</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Aktivitas Terbaru</h3>
                <a href="<?= BASE_URL ?>/pages/super_admin/laporan.php" class="text-sm font-medium text-primary hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr>
                            <th class="text-left">Pengguna</th>
                            <th class="text-left">Aksi</th>
                            <th class="text-left">Detail</th>
                            <th class="text-left">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($log['nama'] ?? 'Sistem') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($log['aksi']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($log['detail'] ?? '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($log['created_at']))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_logs)): ?>
                        <tr><td colspan="4" class="text-center text-on-surface-variant py-8">Belum ada aktivitas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
