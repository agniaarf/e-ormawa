<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pdo = db();
$user_id = (int) $_SESSION['user_id'];
$org_id = (int) ($_GET['org_id'] ?? 0);

$org = $pdo->prepare("SELECT * FROM organisasi WHERE id=? AND deleted_at IS NULL LIMIT 1");
$org->execute([$org_id]); $org = $org->fetch();
if (!$org) { set_flash('error', 'Organisasi tidak ditemukan.'); redirect('/organisasi'); }

require_can('permintaan.manage', $org_id);

$page_title = 'Permintaan · ' . $org['nama'];
$current_page = 'org_permintaan';
$current_org_id = $org_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); redirect('/organisasi/' . $org_id . '/permintaan'); }
    require_can('permintaan.manage', $org_id);
    $rid = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $req = $pdo->prepare("SELECT * FROM permintaan_bergabung WHERE id=? AND organisasi_id=? AND status='menunggu' LIMIT 1");
    $req->execute([$rid, $org_id]); $req = $req->fetch();

    if ($req) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE permintaan_bergabung SET status='diterima', responded_at=NOW(), responded_by=? WHERE id=?")->execute([$user_id, $rid]);
            $pdo->prepare("INSERT INTO user_organisasi (user_id, organisasi_id, role, status, created_at) VALUES (?, ?, 'member', 'aktif', NOW()) ON DUPLICATE KEY UPDATE status='aktif'")
                ->execute([$req['user_id'], $org_id]);
            send_notification((int)$req['user_id'], 'Permintaan Diterima', 'Anda telah diterima sebagai member ' . $org['nama'] . '.', 'success');
            log_activity($user_id, 'Terima Permintaan', "Org: $org_id, User: {$req['user_id']}");
            set_flash('success', 'Permintaan diterima. Member ditambahkan.');
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE permintaan_bergabung SET status='ditolak', responded_at=NOW(), responded_by=? WHERE id=?")->execute([$user_id, $rid]);
            send_notification((int)$req['user_id'], 'Permintaan Ditolak', 'Maaf, permintaan Anda ke ' . $org['nama'] . ' ditolak.', 'info');
            log_activity($user_id, 'Tolak Permintaan', "Org: $org_id, User: {$req['user_id']}");
            set_flash('success', 'Permintaan ditolak.');
        }
    }
    redirect('/organisasi/' . $org_id . '/permintaan');
}

$pending = $pdo->prepare("SELECT pb.*, u.nama, u.nim, u.jurusan FROM permintaan_bergabung pb JOIN users u ON pb.user_id=u.id WHERE pb.organisasi_id=? AND pb.status='menunggu' ORDER BY pb.created_at ASC");
$pending->execute([$org_id]); $pending = $pending->fetchAll();

$history = $pdo->prepare("SELECT pb.*, u.nama, u.nim FROM permintaan_bergabung pb JOIN users u ON pb.user_id=u.id WHERE pb.organisasi_id=? AND pb.status<>'menunggu' ORDER BY pb.responded_at DESC LIMIT 20");
$history->execute([$org_id]); $history = $history->fetchAll();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-5xl mx-auto space-y-6">
        <a href="<?= url('organisasi/' . $org_id) ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            <?= e($org['nama']) ?>
        </a>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant"><h3 class="font-bold text-on-surface">Permintaan Menunggu (<?= count($pending) ?>)</h3></div>
            <div class="divide-y divide-outline-variant">
                <?php foreach ($pending as $r): ?>
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold text-sm text-on-surface"><?= e($r['nama']) ?> <span class="text-xs text-on-surface-variant">· <?= e($r['nim']) ?></span></p>
                        <p class="text-xs text-on-surface-variant"><?= e($r['jurusan'] ?: '-') ?></p>
                        <?php if ($r['motivasi']): ?><p class="text-sm text-on-surface-variant mt-1 italic">"<?= e($r['motivasi']) ?>"</p><?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="" class="inline"><?= csrf_input() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="approve"><button class="btn-primary !h-8 !px-3 !text-xs">Terima</button></form>
                        <form method="POST" action="" class="inline" onsubmit="return confirm('Tolak permintaan ini?')"><?= csrf_input() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="reject"><button class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Tolak</button></form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($pending)): ?><div class="px-6 py-8 text-center text-on-surface-variant text-sm">Tidak ada permintaan menunggu.</div><?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant"><h3 class="font-bold text-on-surface">Riwayat</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>NIM</th><th>Status</th><th>Direspons</th></tr></thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($h['nama']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($h['nim']) ?></td>
                            <td><span class="badge <?= $h['status']==='diterima'?'bg-green-100 text-green-700':'bg-red-100 text-red-700' ?>"><?= ucfirst($h['status']) ?></span></td>
                            <td class="text-sm text-on-surface-variant"><?= $h['responded_at'] ? e(date('d M Y H:i', strtotime($h['responded_at']))) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($history)): ?><tr><td colspan="4" class="text-center text-on-surface-variant py-8">Belum ada riwayat</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
