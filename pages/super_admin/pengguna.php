<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Super Admin');

$page_title = 'Kelola Pengguna';
$current_page = 'pengguna';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Token CSRF tidak valid.');
    } else {
        $id = $_POST['id'] ?? '';
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nim = trim($_POST['nim'] ?? '');
        $role_id = (int) ($_POST['role_id'] ?? 3);
        $no_hp = trim($_POST['no_hp'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $angkatan = trim($_POST['angkatan'] ?? '');
        $status = $_POST['status'] ?? 'aktif';
        $password = $_POST['password'] ?? '';

        if (empty($nama) || empty($email) || empty($nim)) {
            set_flash('error', 'Nama, email, dan NIM wajib diisi.');
        } else {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE users SET nama=?, email=?, nim=?, role_id=?, no_hp=?, jurusan=?, angkatan=?, status=? WHERE id=?");
                $stmt->execute([$nama, $email, $nim, $role_id, $no_hp, $jurusan, $angkatan, $status, $id]);
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $id]);
                }
                log_activity($_SESSION['user_id'], 'Update Pengguna', "ID: $id");
                set_flash('success', 'Pengguna berhasil diperbarui.');
            } else {
                if (empty($password)) { set_flash('error', 'Password wajib diisi untuk pengguna baru.'); redirect('/pages/super_admin/pengguna.php'); }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, nim, password, role_id, no_hp, jurusan, angkatan, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$nama, $email, $nim, $hash, $role_id, $no_hp, $jurusan, $angkatan, $status]);
                log_activity($_SESSION['user_id'], 'Tambah Pengguna', $nama);
                set_flash('success', 'Pengguna berhasil ditambahkan.');
            }
        }
    }
    redirect('/pages/super_admin/pengguna.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id=? AND role_id != 1")->execute([$id]);
    log_activity($_SESSION['user_id'], 'Hapus Pengguna', "ID: $id");
    set_flash('success', 'Pengguna berhasil dihapus.');
    redirect('/pages/super_admin/pengguna.php');
}

$search = $_GET['search'] ?? '';
$sql = "SELECT u.*, r.nama as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (u.nama LIKE ? OR u.email LIKE ? OR u.nim LIKE ?)"; $params = ["%$search%","%$search%","%$search%"]; }
$sql .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $edit->execute([(int)$_GET['edit']]);
    $edit = $edit->fetch();
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
                    <h3 class="font-bold text-on-surface">Daftar Pengguna</h3>
                    <button onclick="openModal('modalPengguna')" type="button" class="btn-primary !h-8 !px-3 !text-xs">+ Tambah</button>
                </div>
                <form method="GET" action="" class="flex gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-9 !text-sm" placeholder="Cari nama/email/nim...">
                    <button type="submit" class="btn-primary !h-9 !px-3 !text-sm">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr><th>Nama</th><th>Email</th><th>NIM</th><th>Role</th><th>Status</th><th class="text-right">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['email']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['nim']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['role_name']) ?></td>
                            <td>
                                <span class="badge <?= $row['status']==='aktif' ? 'bg-green-100 text-green-700' : ($row['status']==='menunggu'?'bg-yellow-100 text-yellow-700':'bg-gray-100 text-gray-600') ?>"><?= e($row['status']) ?></span>
                            </td>
                            <td class="text-right">
                                <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
                                <?php if ($row['role_id'] != 1): ?>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?>
                        <tr><td colspan="6" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
$modal_id = 'modalPengguna';
$modal_title = $edit ? 'Edit Pengguna' : 'Tambah Pengguna';
ob_start();
?>
<form method="POST" action="" class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <?= csrf_input() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Nama</label>
        <input type="text" name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="form-input" placeholder="Nama lengkap">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Email</label>
        <input type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>" class="form-input" placeholder="Email">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">NIM</label>
        <input type="text" name="nim" required value="<?= e($edit['nim'] ?? '') ?>" class="form-input" placeholder="NIM">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Role</label>
        <select name="role_id" class="form-input">
            <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>" <?= ($edit['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['nama']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">No. HP</label>
        <input type="text" name="no_hp" value="<?= e($edit['no_hp'] ?? '') ?>" class="form-input" placeholder="08xxxxxxxxxx">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Jurusan</label>
        <input type="text" name="jurusan" value="<?= e($edit['jurusan'] ?? '') ?>" class="form-input" placeholder="Jurusan">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Angkatan</label>
        <input type="text" name="angkatan" value="<?= e($edit['angkatan'] ?? '') ?>" class="form-input" placeholder="Contoh: 2024">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
        <select name="status" class="form-input">
            <option value="aktif" <?= ($edit['status'] ?? '')==='aktif'?'selected':'' ?>>Aktif</option>
            <option value="nonaktif" <?= ($edit['status'] ?? '')==='nonaktif'?'selected':'' ?>>Nonaktif</option>
            <option value="menunggu" <?= ($edit['status'] ?? '')==='menunggu'?'selected':'' ?>>Menunggu</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Password <?= $edit ? '(kosongkan jika tidak diubah)' : '' ?></label>
        <input type="password" name="password" <?= $edit ? '' : 'required' ?> class="form-input" placeholder="Password">
    </div>
    <div class="md:col-span-3 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalPengguna')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Tambah Pengguna' ?></button>
    </div>
</form>
<?php
$modal_content = ob_get_clean();
require __DIR__ . '/../../components/modal.php';
?>
<?php if ($edit): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modalPengguna'); });</script>
<?php endif; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
