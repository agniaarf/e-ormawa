<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Admin Organisasi');

$page_title = 'Kelola Anggota';
$current_page = 'anggota';
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
        $id = $_POST['id'] ?? '';
        $anggota_user_id = (int) ($_POST['user_id'] ?? 0);
        $jabatan = trim($_POST['jabatan'] ?? 'Anggota');
        $divisi = trim($_POST['divisi'] ?? '');
        $status = $_POST['status'] ?? 'aktif';

        if ($id) {
            $pdo->prepare("UPDATE anggota SET jabatan=?, divisi=?, status=? WHERE id=? AND organisasi_id=?")->execute([$jabatan, $divisi, $status, $id, $org_id]);
            set_flash('success', 'Anggota diperbarui.');
        } else {
            $cek = $pdo->prepare("SELECT id FROM anggota WHERE user_id=? AND organisasi_id=? LIMIT 1");
            $cek->execute([$anggota_user_id, $org_id]);
            if ($cek->fetch()) { set_flash('error', 'Pengguna sudah menjadi anggota organisasi ini.'); }
            else {
                $pdo->prepare("INSERT INTO anggota (user_id, organisasi_id, jabatan, divisi, status, tanggal_masuk) VALUES (?, ?, ?, ?, ?, CURDATE())")
                    ->execute([$anggota_user_id, $org_id, $jabatan, $divisi, $status]);
                set_flash('success', 'Anggota berhasil ditambahkan.');
            }
        }
    }
    redirect('/pages/admin_organisasi/anggota.php');
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM anggota WHERE id=? AND organisasi_id=?")->execute([(int)$_GET['delete'], $org_id]);
    set_flash('success', 'Anggota dihapus.');
    redirect('/pages/admin_organisasi/anggota.php');
}

$search = $_GET['search'] ?? '';
$sql = "SELECT a.*, u.nama, u.nim, u.email FROM anggota a JOIN users u ON a.user_id = u.id WHERE a.organisasi_id = ?";
$params = [$org_id];
if ($search) { $sql .= " AND (u.nama LIKE ? OR u.nim LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $list = $stmt->fetchAll();

$users = $pdo->query("SELECT id, nama, nim FROM users WHERE role_id=3 AND status='aktif' ORDER BY nama")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT a.*, u.nama, u.nim FROM anggota a JOIN users u ON a.user_id = u.id WHERE a.id=? AND a.organisasi_id=? LIMIT 1");
    $edit->execute([(int)$_GET['edit'], $org_id]); $edit = $edit->fetch();
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-on-surface">Daftar Anggota</h3>
                    <button onclick="openModal('modalAnggota')" type="button" class="btn-primary !h-8 !px-3 !text-xs">+ Tambah</button>
                </div>
                <form method="GET" action="" class="flex gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-9 !text-sm" placeholder="Cari anggota...">
                    <button type="submit" class="btn-primary !h-9 !px-3 !text-sm">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>NIM</th><th>Jabatan</th><th>Divisi</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['nim']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['jabatan']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['divisi'] ?: '-') ?></td>
                            <td><span class="badge <?= $row['status']==='aktif'?'bg-green-100 text-green-700':($row['status']==='alumni'?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-600') ?>"><?= e($row['status']) ?></span></td>
                            <td class="text-right">
                                <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?><tr><td colspan="6" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
$modal_id = 'modalAnggota';
$modal_title = $edit ? 'Edit Anggota' : 'Tambah Anggota';
ob_start();
?>
<form method="POST" action="" class="grid grid-cols-1 md:grid-cols-4 gap-5">
    <?= csrf_input() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><input type="hidden" name="user_id" value="<?= $edit['user_id'] ?>"><?php endif; ?>
    <?php if (!$edit): ?>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Pilih Mahasiswa</label>
        <select name="user_id" required class="form-input">
            <option value="">-- Pilih Mahasiswa --</option>
            <?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['nama']) ?> (<?= e($u['nim']) ?>)</option><?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Jabatan</label>
        <input type="text" name="jabatan" value="<?= e($edit['jabatan'] ?? 'Anggota') ?>" class="form-input" placeholder="Jabatan">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Divisi</label>
        <input type="text" name="divisi" value="<?= e($edit['divisi'] ?? '') ?>" class="form-input" placeholder="Divisi">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
        <select name="status" class="form-input">
            <option value="aktif" <?= ($edit['status']??'')==='aktif'?'selected':'' ?>>Aktif</option>
            <option value="nonaktif" <?= ($edit['status']??'')==='nonaktif'?'selected':'' ?>>Nonaktif</option>
            <option value="alumni" <?= ($edit['status']??'')==='alumni'?'selected':'' ?>>Alumni</option>
        </select>
    </div>
    <div class="md:col-span-4 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalAnggota')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    </div>
</form>
<?php
$modal_content = ob_get_clean();
require __DIR__ . '/../../components/modal.php';
?>
<?php if ($edit): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modalAnggota'); });</script>
<?php endif; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
