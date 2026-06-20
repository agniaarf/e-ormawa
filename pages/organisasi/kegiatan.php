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

$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM kegiatan WHERE organisasi_id=? AND deleted_at IS NULL";
$params = [$org_id];
if ($search !== '') { $sql .= " AND (nama LIKE ? OR lokasi LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY tanggal_mulai DESC";
$list = $pdo->prepare($sql); $list->execute($params); $list = $list->fetchAll();

if (($_GET['ajax'] ?? '') === 'table') {
    include __DIR__ . '/../../components/tables/kegiatan.php';
    exit;
}

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
        <div class="flex items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-on-surface-variant" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
                <form method="GET" action="" class="w-full">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-10 !pl-10 !text-sm w-full" placeholder="Cari kegiatan..." autocomplete="off" data-live-search data-target="#kegiatan-table-body">
                </form>
            </div>
            <?php if ($can_manage): ?><button onclick="openModal('modalKegiatan')" type="button" class="btn-primary !w-10 !h-10 !p-0 !rounded-full flex items-center justify-center" title="Tambah kegiatan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button><?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant">
                <h3 class="font-bold text-on-surface">Daftar Kegiatan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>Tipe</th><th>Mulai</th><th>Lokasi</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody id="kegiatan-table-body">
                        <?php include __DIR__ . '/../../components/tables/kegiatan.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($can_manage):
$modal_id = 'modalKegiatan'; $modal_title = $edit ? 'Edit Kegiatan' : 'Tambah Kegiatan'; ob_start();
?>
<form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
        <select name="status" class="form-input">
            <?php foreach (['rencana'=>'Rencana','berlangsung'=>'Berlangsung','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($edit['status']??'')===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2">
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
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Lokasi</label>
        <input type="text" name="lokasi" value="<?= e($edit['lokasi'] ?? '') ?>" class="form-input">
    </div>
    <div class="md:col-span-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalKegiatan')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<?php if ($edit): ?><script>document.addEventListener('DOMContentLoaded',()=>openModal('modalKegiatan'));</script><?php endif; ?>
<?php endif; ?>

<div id="detailModal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-lg" id="detailModalTitle">Detail Kegiatan</h3>
                <button type="button" onclick="closeDetailModal()" class="p-1.5 rounded-lg hover:bg-surface-low text-on-surface-variant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4" id="detailModalContent"></div>
        </div>
    </div>
</div>

<script>
function openDetailModal(keg) {
    const fmt = (d) => d ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
    const statusClass = keg.status === 'berlangsung' ? 'bg-green-100 text-green-700' : (keg.status === 'selesai' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600');
    document.getElementById('detailModalTitle').textContent = keg.nama || 'Detail Kegiatan';
    document.getElementById('detailModalContent').innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <h2 class="text-xl font-bold text-on-surface">${keg.nama || '-'}</h2>
            <span class="badge ${statusClass}">${keg.status ? keg.status.charAt(0).toUpperCase() + keg.status.slice(1) : '-'}</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="badge bg-primary/10 text-primary text-xs">${keg.tipe ? keg.tipe.charAt(0).toUpperCase() + keg.tipe.slice(1) : '-'}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-on-surface-variant">Tanggal Mulai</p>
                <p class="font-semibold text-on-surface">${fmt(keg.tanggal_mulai)}</p>
            </div>
            <div>
                <p class="text-on-surface-variant">Tanggal Selesai</p>
                <p class="font-semibold text-on-surface">${fmt(keg.tanggal_selesai)}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-on-surface-variant">Lokasi</p>
                <p class="font-semibold text-on-surface">${keg.lokasi || '-'}</p>
            </div>
        </div>
        <div>
            <p class="text-sm text-on-surface-variant mb-1">Deskripsi</p>
            <p class="text-sm text-on-surface leading-relaxed">${keg.deskripsi ? keg.deskripsi.replace(/\n/g, '<br>') : 'Tidak ada deskripsi.'}</p>
        </div>
    `;
    document.getElementById('detailModal').classList.remove('hidden');
}
function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}
</script>

<?php require __DIR__ . '/../../components/footer.php'; ?>
