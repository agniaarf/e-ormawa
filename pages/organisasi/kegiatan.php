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

require_can('kegiatan.read', $org_id);

$page_title = 'Kegiatan · ' . $org['nama'];
$current_page = 'org_kegiatan';
$current_org_id = $org_id;
$can_manage = can('kegiatan.manage', $org_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); redirect('/organisasi/' . $org_id . '/kegiatan'); }
    require_can('kegiatan.manage', $org_id);
    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tipe = $_POST['tipe'] ?? 'proker';
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $lokasi = trim($_POST['lokasi'] ?? '');
    $status = $_POST['status'] ?? 'rencana';

    if ($nama === '' || $tanggal_mulai === '') { set_flash('error', 'Nama dan tanggal mulai wajib diisi.'); }
    elseif ($id) {
        $pdo->prepare("UPDATE kegiatan SET nama=?, deskripsi=?, tipe=?, tanggal_mulai=?, tanggal_selesai=?, lokasi=?, status=? WHERE id=? AND organisasi_id=?")
            ->execute([$nama, $deskripsi, $tipe, $tanggal_mulai, $tanggal_selesai ?: null, $lokasi, $status, $id, $org_id]);
        log_activity($user_id, 'Update Kegiatan', "ID: $id");
        set_flash('success', 'Kegiatan diperbarui.');
    } else {
        $pdo->prepare("INSERT INTO kegiatan (organisasi_id, nama, deskripsi, tipe, tanggal_mulai, tanggal_selesai, lokasi, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$org_id, $nama, $deskripsi, $tipe, $tanggal_mulai, $tanggal_selesai ?: null, $lokasi, $status, $user_id]);
        log_activity($user_id, 'Tambah Kegiatan', $nama);
        set_flash('success', 'Kegiatan ditambahkan.');
    }
    redirect('/organisasi/' . $org_id . '/kegiatan');
}

if ($can_manage && isset($_GET['delete'])) {
    $pdo->prepare("UPDATE kegiatan SET deleted_at=NOW() WHERE id=? AND organisasi_id=?")->execute([(int)$_GET['delete'], $org_id]);
    log_activity($user_id, 'Hapus Kegiatan (soft)', "ID: {$_GET['delete']}");
    set_flash('success', 'Kegiatan diarsipkan.');
    redirect('/organisasi/' . $org_id . '/kegiatan');
}

$list = $pdo->prepare("SELECT * FROM kegiatan WHERE organisasi_id=? AND deleted_at IS NULL ORDER BY tanggal_mulai DESC");
$list->execute([$org_id]); $list = $list->fetchAll();

$edit = null;
if ($can_manage && isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM kegiatan WHERE id=? AND organisasi_id=? LIMIT 1");
    $edit->execute([(int)$_GET['edit'], $org_id]); $edit = $edit->fetch();
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <a href="<?= url('organisasi/' . $org_id) ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            <?= e($org['nama']) ?>
        </a>
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-on-surface">Daftar Kegiatan</h3>
                    <?php if ($can_manage): ?><button onclick="openModal('modalKegiatan')" type="button" class="btn-primary !h-8 !px-3 !text-xs">+ Tambah</button><?php endif; ?>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>Tipe</th><th>Mulai</th><th>Lokasi</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= ucfirst($row['tipe']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($row['tanggal_mulai']))) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['lokasi'] ?: '-') ?></td>
                            <td><span class="badge <?= $row['status']==='berlangsung'?'bg-green-100 text-green-700':($row['status']==='selesai'?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-600') ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td class="text-right whitespace-nowrap">
                                <a href="<?= url('organisasi/' . $org_id . '/kegiatan/' . $row['id']) ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-surface-low text-on-surface-variant text-xs font-semibold hover:bg-surface-high mr-1">Detail</a>
                                <?php if ($can_manage): ?>
                                <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Arsipkan kegiatan ini?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?><tr><td colspan="6" class="text-center text-on-surface-variant py-8">Belum ada kegiatan</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($can_manage):
$modal_id = 'modalKegiatan'; $modal_title = $edit ? 'Edit Kegiatan' : 'Tambah Kegiatan'; ob_start();
?>
<form method="POST" action="" class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <?= csrf_input() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Nama Kegiatan</label>
        <input type="text" name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="form-input">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Tipe</label>
        <select name="tipe" class="form-input">
            <?php foreach (['rapat'=>'Rapat','pelatihan'=>'Pelatihan','proker'=>'Proker','lomba'=>'Lomba','sosial'=>'Sosial','lainnya'=>'Lainnya'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($edit['tipe']??'')===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-3">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="form-input py-2"><?= e($edit['deskripsi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Tanggal Mulai</label>
        <input type="datetime-local" name="tanggal_mulai" required value="<?= e($edit['tanggal_mulai'] ? date('Y-m-d\TH:i', strtotime($edit['tanggal_mulai'])) : '') ?>" class="form-input">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Tanggal Selesai</label>
        <input type="datetime-local" name="tanggal_selesai" value="<?= e($edit['tanggal_selesai'] ? date('Y-m-d\TH:i', strtotime($edit['tanggal_selesai'])) : '') ?>" class="form-input">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Lokasi</label>
        <input type="text" name="lokasi" value="<?= e($edit['lokasi'] ?? '') ?>" class="form-input">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
        <select name="status" class="form-input">
            <?php foreach (['rencana'=>'Rencana','berlangsung'=>'Berlangsung','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($edit['status']??'')===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalKegiatan')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<?php if ($edit): ?><script>document.addEventListener('DOMContentLoaded',()=>openModal('modalKegiatan'));</script><?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../../components/footer.php'; ?>
