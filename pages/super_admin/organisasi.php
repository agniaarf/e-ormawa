<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Super Admin');

$page_title = 'Kelola Organisasi';
$current_page = 'organisasi';

$pdo = db();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Token CSRF tidak valid.');
    } else {
        $id = $_POST['id'] ?? '';
        $nama = trim($_POST['nama'] ?? '');
        $singkatan = trim($_POST['singkatan'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $visi = trim($_POST['visi'] ?? '');
        $misi = trim($_POST['misi'] ?? '');
        $status = $_POST['status'] ?? 'aktif';
        $ketua_id = !empty($_POST['ketua_id']) ? (int)$_POST['ketua_id'] : null;

        if (empty($nama)) {
            set_flash('error', 'Nama organisasi wajib diisi.');
        } else {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE organisasi SET nama=?, singkatan=?, deskripsi=?, visi=?, misi=?, status=?, ketua_id=? WHERE id=?");
                $stmt->execute([$nama, $singkatan, $deskripsi, $visi, $misi, $status, $ketua_id, $id]);
                if ($ketua_id) {
                    $pdo->prepare("INSERT INTO anggota (user_id, organisasi_id, jabatan, status, tanggal_masuk) VALUES (?, ?, 'Ketua', 'aktif', CURDATE()) ON DUPLICATE KEY UPDATE jabatan='Ketua'")
                        ->execute([$ketua_id, $id]);
                }
                log_activity($_SESSION['user_id'], 'Update Organisasi', "ID: $id");
                set_flash('success', 'Organisasi berhasil diperbarui.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO organisasi (nama, singkatan, deskripsi, visi, misi, status, ketua_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$nama, $singkatan, $deskripsi, $visi, $misi, $status, $ketua_id]);
                $new_id = (int) $pdo->lastInsertId();
                if ($ketua_id) {
                    $pdo->prepare("INSERT INTO anggota (user_id, organisasi_id, jabatan, status, tanggal_masuk) VALUES (?, ?, 'Ketua', 'aktif', CURDATE())")
                        ->execute([$ketua_id, $new_id]);
                }
                log_activity($_SESSION['user_id'], 'Tambah Organisasi', $nama);
                set_flash('success', 'Organisasi berhasil ditambahkan.');
            }
        }
    }
    redirect('/pages/super_admin/organisasi.php');
}

// Handle delete via GET param
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM organisasi WHERE id=?")->execute([$id]);
    log_activity($_SESSION['user_id'], 'Hapus Organisasi', "ID: $id");
    set_flash('success', 'Organisasi berhasil dihapus.');
    redirect('/pages/super_admin/organisasi.php');
}

// Data
$search = $_GET['search'] ?? '';
$sql = "SELECT o.*, u.nama as ketua_nama FROM organisasi o LEFT JOIN users u ON o.ketua_id = u.id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (o.nama LIKE ? OR o.singkatan LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM organisasi WHERE id=? LIMIT 1");
    $edit->execute([(int)$_GET['edit']]);
    $edit = $edit->fetch();
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- List -->
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-on-surface">Daftar Organisasi</h3>
                    <button onclick="openModal('modalOrganisasi')" type="button" class="btn-primary !h-8 !px-3 !text-xs">+ Tambah</button>
                </div>
                <form method="GET" action="" class="flex gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-9 !text-sm" placeholder="Cari organisasi...">
                    <button type="submit" class="btn-primary !h-9 !px-3 !text-sm">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr><th>Nama</th><th>Singkatan</th><th>Ketua</th><th>Status</th><th>Dibuat</th><th class="text-right">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['singkatan'] ?? '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['ketua_nama'] ?? '-') ?></td>
                            <td>
                                <span class="badge <?= $row['status']==='aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>"><?= e($row['status']) ?></span>
                            </td>
                            <td class="text-sm text-on-surface-variant"><?= e(date('d M Y', strtotime($row['created_at']))) ?></td>
                            <td class="text-right">
                                <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?>
                        <tr><td colspan="5" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
// Modal content capture
$modal_id = 'modalOrganisasi';
$modal_title = $edit ? 'Edit Organisasi' : 'Tambah Organisasi';
ob_start();
?>
<form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <?= csrf_input() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Nama Organisasi</label>
        <input type="text" name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="form-input" placeholder="Nama lengkap organisasi">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Singkatan</label>
        <input type="text" name="singkatan" value="<?= e($edit['singkatan'] ?? '') ?>" class="form-input" placeholder="Contoh: BEM, HIMTI">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
        <select name="status" class="form-input">
            <option value="aktif" <?= ($edit['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= ($edit['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Ketua Organisasi</label>
        <select name="ketua_id" class="form-input">
            <option value="">-- Pilih Ketua --</option>
            <?php
            $users_list = $pdo->query("SELECT id, nama, nim FROM users WHERE status='aktif' ORDER BY nama")->fetchAll();
            foreach ($users_list as $u):
            ?>
            <option value="<?= $u['id'] ?>" <?= ($edit['ketua_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['nama']) ?> (<?= e($u['nim']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="form-input py-2" placeholder="Deskripsi singkat organisasi"><?= e($edit['deskripsi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Visi</label>
        <textarea name="visi" rows="2" class="form-input py-2" placeholder="Visi organisasi"><?= e($edit['visi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Misi</label>
        <textarea name="misi" rows="2" class="form-input py-2" placeholder="Misi organisasi"><?= e($edit['misi'] ?? '') ?></textarea>
    </div>
    <div class="md:col-span-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalOrganisasi')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Tambah Organisasi' ?></button>
    </div>
</form>
<?php
$modal_content = ob_get_clean();
require __DIR__ . '/../../components/modal.php';
?>
<?php if ($edit): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modalOrganisasi'); });</script>
<?php endif; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
