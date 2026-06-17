<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Admin Organisasi');

$page_title = 'Rekrutmen';
$current_page = 'rekrutmen';
$pdo = db();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT o.* FROM organisasi o JOIN anggota a ON o.id = a.organisasi_id WHERE a.user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$my_org = $stmt->fetch();
$org_id = $my_org['id'] ?? 0;

if (!$org_id) { set_flash('error', 'Anda belum terhubung dengan organisasi.'); redirect('/pages/admin_organisasi/dashboard.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); }
    else {
        $pendaftaran_id = (int) ($_POST['pendaftaran_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($status === 'wawancara') {
            $jadwal = $_POST['jadwal'] ?? '';
            $lokasi = trim($_POST['lokasi'] ?? '');
            $catatan = trim($_POST['catatan'] ?? '');
            $pdo->prepare("UPDATE pendaftaran_organisasi SET status='wawancara' WHERE id=? AND organisasi_id=?")->execute([$pendaftaran_id, $org_id]);
            $pdo->prepare("INSERT INTO wawancara (pendaftaran_id, jadwal, lokasi, catatan, hasil, interviewer_id) VALUES (?, ?, ?, ?, 'menunggu', ?)")
                ->execute([$pendaftaran_id, $jadwal, $lokasi, $catatan, $user_id]);
            $usr = $pdo->prepare("SELECT user_id FROM pendaftaran_organisasi WHERE id=? LIMIT 1");
            $usr->execute([$pendaftaran_id]); $u = $usr->fetch();
            if ($u) {
                send_notification((int)$u['user_id'], 'Jadwal Wawancara', 'Anda dijadwalkan wawancara untuk ' . ($my_org['nama'] ?? 'organisasi') . ' pada ' . date('d M Y H:i', strtotime($jadwal)), 'info');
            }
            set_flash('success', 'Jadwal wawancara ditetapkan.');
        } elseif (in_array($status, ['diterima','ditolak'])) {
            $pdo->prepare("UPDATE pendaftaran_organisasi SET status=? WHERE id=? AND organisasi_id=?")->execute([$status, $pendaftaran_id, $org_id]);
            $pdo->prepare("UPDATE wawancara SET hasil=? WHERE pendaftaran_id=? ORDER BY id DESC LIMIT 1")->execute([$status==='diterima'?'lulus':'tidak_lulus', $pendaftaran_id]);
            $usr = $pdo->prepare("SELECT user_id FROM pendaftaran_organisasi WHERE id=? LIMIT 1");
            $usr->execute([$pendaftaran_id]); $u = $usr->fetch();
            if ($u) {
                if ($status === 'diterima') {
                    $pdo->prepare("INSERT INTO anggota (user_id, organisasi_id, jabatan, status, tanggal_masuk) VALUES (?, ?, 'Anggota', 'aktif', CURDATE())")
                        ->execute([$u['user_id'], $org_id]);
                }
                $msg = $status === 'diterima'
                    ? 'Selamat! Anda diterima di ' . ($my_org['nama'] ?? 'organisasi') . '.'
                    : 'Maaf, pendaftaran Anda di ' . ($my_org['nama'] ?? 'organisasi') . ' ditolak.';
                send_notification((int)$u['user_id'], 'Hasil Rekrutmen', $msg, $status==='diterima'?'success':'error');
            }
            set_flash('success', 'Status pendaftaran diperbarui.');
        }
    }
    redirect('/pages/admin_organisasi/rekrutmen.php');
}

$filter = $_GET['filter'] ?? 'menunggu';
$sql = "SELECT po.*, u.nama, u.nim, u.jurusan, u.angkatan FROM pendaftaran_organisasi po JOIN users u ON po.user_id = u.id WHERE po.organisasi_id = ? AND po.status = ? ORDER BY po.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute([$org_id, $filter]); $list = $stmt->fetchAll();

$wawancara_map = [];
if ($filter === 'wawancara') {
    $w = $pdo->prepare("SELECT w.*, po.id as pid FROM wawancara w JOIN pendaftaran_organisasi po ON w.pendaftaran_id = po.id WHERE po.organisasi_id = ? AND po.status = 'wawancara'");
    $w->execute([$org_id]);
    foreach ($w->fetchAll() as $x) $wawancara_map[$x['pid']] = $x;
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex gap-2">
            <?php foreach (['menunggu'=>'Menunggu','wawancara'=>'Wawancara','diterima'=>'Diterima','ditolak'=>'Ditolak'] as $k=>$v): ?>
            <a href="?filter=<?= $k ?>" class="px-4 py-2 rounded-lg text-sm font-semibold <?= $filter===$k ? 'bg-primary text-white' : 'bg-white text-on-surface border border-outline-variant' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant">
                <h3 class="font-bold text-on-surface">Pendaftaran — <?= ucfirst($filter) ?></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>NIM</th><th>Jurusan</th><th>Angkatan</th><th>Motivasi</th><th>Tanggal Daftar</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['nim']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['jurusan'] ?: '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['angkatan'] ?: '-') ?></td>
                            <td class="text-sm text-on-surface-variant max-w-xs truncate"><?= e($row['motivasi'] ?: '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e(date('d M Y', strtotime($row['created_at']))) ?></td>
                            <td class="text-right whitespace-nowrap">
                                <?php if ($row['status'] === 'menunggu'): ?>
                                <form method="POST" action="" class="inline">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="pendaftaran_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="status" value="wawancara">
                                    <input type="datetime-local" name="jadwal" required class="form-input !h-8 !text-xs !w-40 inline-block">
                                    <input type="text" name="lokasi" class="form-input !h-8 !text-xs !w-28 inline-block" placeholder="Lokasi">
                                    <button type="submit" class="btn-primary !h-8 !px-2 !text-xs inline-block">Jadwalkan</button>
                                </form>
                                <?php elseif ($row['status'] === 'wawancara'): ?>
                                    <?php $w = $wawancara_map[$row['id']] ?? null; ?>
                                    <?php if ($w): ?><p class="text-xs text-on-surface-variant mb-1"><?= e(date('d M Y H:i', strtotime($w['jadwal']))) ?> — <?= e($w['lokasi'] ?: '-') ?></p><?php endif; ?>
                                    <form method="POST" action="" class="inline">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="pendaftaran_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="status" value="diterima">
                                        <button type="submit" class="btn-primary !h-8 !px-2 !text-xs inline-block mr-1">Terima</button>
                                    </form>
                                    <form method="POST" action="" class="inline">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="pendaftaran_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="status" value="ditolak">
                                        <button type="submit" class="btn-secondary !h-8 !px-2 !text-xs inline-block">Tolak</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge <?= $row['status']==='diterima'?'bg-green-100 text-green-700':'bg-red-100 text-red-700' ?>"><?= e($row['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?><tr><td colspan="7" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
