<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_can('log.view');

$page_title = 'Log Aktivitas';
$current_page = 'log';
$pdo = db();

$search = trim($_GET['search'] ?? '');
$sql = "SELECT al.*, u.nama FROM activity_log al LEFT JOIN users u ON al.user_id=u.id WHERE 1=1";
$params = [];
if ($search !== '') { $sql .= " AND (u.nama LIKE ? OR al.aksi LIKE ? OR al.detail LIKE ?)"; $params = ["%$search%","%$search%","%$search%"]; }
$sql .= " ORDER BY al.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $list = $stmt->fetchAll();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-bold text-on-surface">Audit Trail (200 terbaru)</h3>
                <form method="GET" action="" class="flex gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-9 !text-sm" placeholder="Cari aktivitas...">
                    <button type="submit" class="btn-primary !h-9 !px-3 !text-sm">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Pengguna</th><th>Aksi</th><th>Detail</th><th>IP</th><th>Waktu</th></tr></thead>
                    <tbody>
                        <?php foreach ($list as $log): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($log['nama'] ?? 'Sistem') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($log['aksi']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($log['detail'] ?? '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($log['ip_address'] ?? '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($log['created_at']))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?><tr><td colspan="5" class="text-center text-on-surface-variant py-8">Belum ada aktivitas</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
