<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Admin Organisasi');

$page_title = 'Presensi';
$current_page = 'presensi';
$pdo = db();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT o.* FROM organisasi o JOIN anggota a ON o.id = a.organisasi_id WHERE a.user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$my_org = $stmt->fetch();
$org_id = $my_org['id'] ?? 0;

if (!$org_id) { set_flash('error', 'Anda belum terhubung dengan organisasi.'); redirect('/pages/admin_organisasi/dashboard.php'); }

$kegiatan_id = (int) ($_GET['kegiatan_id'] ?? 0);
$kegiatan_list = $pdo->prepare("SELECT * FROM kegiatan WHERE organisasi_id = ? ORDER BY tanggal_mulai DESC");
$kegiatan_list->execute([$org_id]); $kegiatan_list = $kegiatan_list->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kegiatan_id) {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); }
    else {
        foreach ($_POST['presensi'] ?? [] as $user_id_post => $data) {
            $status = $data['status'] ?? 'hadir';
            $keterangan = trim($data['keterangan'] ?? '');
            $stmt = $pdo->prepare("INSERT INTO presensi (kegiatan_id, user_id, status, keterangan, waktu) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE status=?, keterangan=?");
            $stmt->execute([$kegiatan_id, (int)$user_id_post, $status, $keterangan, $status, $keterangan]);
        }
        set_flash('success', 'Presensi berhasil disimpan.');
    }
    redirect('/pages/admin_organisasi/presensi.php?kegiatan_id=' . $kegiatan_id);
}

$peserta = [];
if ($kegiatan_id) {
    $peserta = $pdo->prepare("SELECT u.id, u.nama, u.nim, pk.status as daftar_status, pr.status as presensi_status, pr.keterangan
        FROM users u
        JOIN peserta_kegiatan pk ON u.id = pk.user_id
        LEFT JOIN presensi pr ON pr.user_id = u.id AND pr.kegiatan_id = ?
        WHERE pk.kegiatan_id = ?");
    $peserta->execute([$kegiatan_id, $kegiatan_id]);
    $peserta = $peserta->fetchAll();
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6">
            <h3 class="font-bold text-on-surface mb-4">Pilih Kegiatan</h3>
            <form method="GET" action="" class="flex gap-3">
                <select name="kegiatan_id" required class="form-input !max-w-md" onchange="this.form.submit()">
                    <option value="">-- Pilih Kegiatan --</option>
                    <?php foreach ($kegiatan_list as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $kegiatan_id===$k['id']?'selected':'' ?>><?= e($k['nama']) ?> (<?= e(date('d M Y', strtotime($k['tanggal_mulai']))) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($kegiatan_id): ?>
        <form method="POST" action="">
            <?= csrf_input() ?>
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-bold text-on-surface">Daftar Peserta</h3>
                    <button type="submit" class="btn-primary">Simpan Presensi</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full data-table">
                        <thead><tr><th>Nama</th><th>NIM</th><th>Status</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <?php foreach ($peserta as $p): ?>
                            <tr>
                                <td class="text-sm font-medium text-on-surface"><?= e($p['nama']) ?></td>
                                <td class="text-sm text-on-surface-variant"><?= e($p['nim']) ?></td>
                                <td>
                                    <select name="presensi[<?= $p['id'] ?>][status]" class="form-input !h-9 !text-sm !w-32">
                                        <?php foreach (['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha'] as $k=>$v): ?>
                                        <option value="<?= $k ?>" <?= ($p['presensi_status']??'')===$k?'selected':'' ?>><?= $v ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="presensi[<?= $p['id'] ?>][keterangan]" value="<?= e($p['keterangan'] ?? '') ?>" class="form-input !h-9 !text-sm" placeholder="Keterangan">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($peserta)): ?><tr><td colspan="4" class="text-center text-on-surface-variant py-8">Belum ada peserta terdaftar.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
