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

    $req = $pdo->prepare("SELECT * FROM permintaan_bergabung WHERE id=? AND organisasi_id=? AND status IN ('menunggu','administrasi','wawancara') LIMIT 1");
    $req->execute([$rid, $org_id]); $req = $req->fetch();

    if ($req) {
        if ($action === 'administrasi') {
            $pdo->prepare("UPDATE permintaan_bergabung SET status='administrasi', responded_at=NOW(), responded_by=? WHERE id=?")->execute([$user_id, $rid]);
            send_notification((int)$req['user_id'], 'Administrasi', 'Permintaan Anda ke ' . $org['nama'] . ' lolos seleksi administrasi.', 'info');
            log_activity($user_id, 'Administrasi Permintaan', "Org: $org_id, User: {$req['user_id']}");
            set_flash('success', 'Permintaan diproses ke tahap administrasi.');
        } elseif ($action === 'interview') {
            $pdo->prepare("UPDATE permintaan_bergabung SET status='wawancara', responded_at=NOW(), responded_by=? WHERE id=?")->execute([$user_id, $rid]);
            send_notification((int)$req['user_id'], 'Wawancara', 'Permintaan Anda ke ' . $org['nama'] . ' masuk tahap wawancara.', 'info');
            log_activity($user_id, 'Wawancara Permintaan', "Org: $org_id, User: {$req['user_id']}");
            set_flash('success', 'Permintaan diproses ke tahap wawancara.');
        } elseif ($action === 'approve') {
            $pdo->prepare("UPDATE permintaan_bergabung SET status='diterima', responded_at=NOW(), responded_by=? WHERE id=?")->execute([$user_id, $rid]);
            $pdo->prepare("INSERT INTO user_organisasi (user_id, organisasi_id, role, status, created_at) VALUES (?, ?, 'member', 'aktif', NOW()) ON DUPLICATE KEY UPDATE status='aktif'")
                ->execute([$req['user_id'], $org_id]);
            send_notification((int)$req['user_id'], 'Permintaan Diterima', 'Anda telah diterima sebagai member ' . $org['nama'] . '. Kartu anggota dapat dilihat di menu Kartu Anggota.', 'success');
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

$select = "SELECT pb.*, u.nama, u.nim, u.jurusan FROM permintaan_bergabung pb JOIN users u ON pb.user_id=u.id WHERE pb.organisasi_id=?";
$menunggu = $pdo->prepare($select . " AND pb.status='menunggu' ORDER BY pb.created_at ASC");
$menunggu->execute([$org_id]); $menunggu = $menunggu->fetchAll();

$administrasi = $pdo->prepare($select . " AND pb.status='administrasi' ORDER BY pb.responded_at DESC");
$administrasi->execute([$org_id]); $administrasi = $administrasi->fetchAll();

$wawancara = $pdo->prepare($select . " AND pb.status='wawancara' ORDER BY pb.responded_at DESC");
$wawancara->execute([$org_id]); $wawancara = $wawancara->fetchAll();

$history = $pdo->prepare("SELECT pb.*, u.nama, u.nim FROM permintaan_bergabung pb JOIN users u ON pb.user_id=u.id WHERE pb.organisasi_id=? AND pb.status IN ('diterima','ditolak') ORDER BY pb.responded_at DESC LIMIT 20");
$history->execute([$org_id]); $history = $history->fetchAll();

function badgeClass(string $status): string {
    return match ($status) {
        'diterima' => 'bg-green-100 text-green-700',
        'ditolak' => 'bg-red-100 text-red-700',
        default => 'bg-yellow-100 text-yellow-700',
    };
}

function requestInfo(array $r): string {
    ob_start();
    ?>
    <p class="font-semibold text-sm text-on-surface"><?= e($r['nama']) ?> <span class="text-xs text-on-surface-variant">· <?= e($r['nim']) ?></span></p>
    <p class="text-xs text-on-surface-variant"><?= e($r['jurusan'] ?: '-') ?></p>
    <?php if ($r['motivasi']): ?><p class="text-sm text-on-surface-variant mt-1 italic">"<?= e($r['motivasi']) ?>"</p><?php endif; ?>
    <?php
    return ob_get_clean();
}
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

        <h2 class="text-xl font-bold text-on-surface">Permintaan Bergabung</h2>

        <?php
        $stepDefinitions = [
            ['num' => 1, 'status' => 'menunggu', 'title' => 'Pengajuan', 'data' => $menunggu, 'action' => 'administrasi', 'btn' => 'Administrasi'],
            ['num' => 2, 'status' => 'administrasi', 'title' => 'Administrasi', 'data' => $administrasi, 'action' => 'interview', 'btn' => 'Wawancara'],
            ['num' => 3, 'status' => 'wawancara', 'title' => 'Wawancara', 'data' => $wawancara, 'action' => 'approve', 'btn' => 'Terima'],
        ];
        ?>
        <div class="space-y-8 relative">
            <?php foreach ($stepDefinitions as $step): ?>
            <div class="relative pl-10">
                <div class="absolute left-0 top-0 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shadow-sm"><?= $step['num'] ?></div>
                <?php if ($step['num'] < 3): ?><div class="absolute left-4 top-8 bottom-[-32px] w-0.5 bg-outline-variant"></div><?php endif; ?>
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="font-bold text-on-surface"><?= e($step['title']) ?></h3>
                        <span class="badge bg-primary/10 text-primary text-xs"><?= count($step['data']) ?> kandidat</span>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($step['data'] as $r): ?>
                        <div class="bg-white rounded-xl border border-outline-variant p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div><?= requestInfo($r) ?></div>
                            <div class="flex items-center gap-2 flex-wrap shrink-0">
                                <form method="POST" action="" class="inline"><?= csrf_input() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="<?= $step['action'] ?>"><button class="btn-primary !h-8 !px-3 !text-xs"><?= e($step['btn']) ?></button></form>
                                <form method="POST" action="" class="inline" onsubmit="return confirm('Tolak permintaan ini?')"><?= csrf_input() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="reject"><button class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Tolak</button></form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($step['data'])): ?><p class="text-sm text-on-surface-variant italic">Tidak ada kandidat di tahap ini.</p><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
                            <td><span class="badge <?= badgeClass($h['status']) ?>"><?= ucfirst($h['status']) ?></span></td>
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
