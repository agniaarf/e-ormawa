<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_can('log.view');

$page_title = 'Log Aktivitas';
$current_page = 'log';
$pdo = db();

$search = trim($_GET['search'] ?? '');
$pageNum = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$sql = "SELECT al.*, u.nama FROM activity_log al LEFT JOIN users u ON al.user_id=u.id WHERE 1=1";
$params = [];
if ($search !== '') { $sql .= " AND (u.nama LIKE ? OR al.aksi LIKE ? OR al.detail LIKE ?)"; $params = ["%$search%","%$search%","%$search%"]; }
$sql .= " ORDER BY al.created_at DESC";
$result = fetchPaginated($pdo, $sql, $params, $pageNum, $perPage);
$list = $result['list'];
$p = $result['p'];

if (($_GET['ajax'] ?? '') === 'table') {
    include __DIR__ . '/../../components/tables/log.php';
    exit;
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-bold text-on-surface">Audit Trail</h3>
                <div class="relative max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-on-surface-variant" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <form method="GET" action="" class="w-full">
                        <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-10 !pl-10 !text-sm w-full" placeholder="Cari aktivitas..." autocomplete="off" data-live-search data-target="#log-table-body">
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Pengguna</th><th>Aksi</th><th>Detail</th><th>IP</th><th>Waktu</th></tr></thead>
                    <tbody id="log-table-body">
                        <?php include __DIR__ . '/../../components/tables/log.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
