<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_can('pengguna.manage');

$page_title = 'Kelola Pengguna';
$current_page = 'pengguna';
$pdo = db();
$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); redirect('/pengguna'); }
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

    if ($nama === '' || $email === '' || $nim === '') {
        set_flash('error', 'Nama, email, dan NIM wajib diisi.');
    } elseif ($id) {
        $pdo->prepare("UPDATE users SET nama=?, email=?, nim=?, role_id=?, no_hp=?, jurusan=?, angkatan=?, status=? WHERE id=?")
            ->execute([$nama, $email, $nim, $role_id, $no_hp, $jurusan, $angkatan, $status, $id]);
        if ($password !== '') {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        }
        log_activity($user_id, 'Update Pengguna', "ID: $id");
        set_flash('success', 'Pengguna berhasil diperbarui.');
    } else {
        if ($password === '') { set_flash('error', 'Password wajib diisi untuk pengguna baru.'); redirect('/pengguna'); }
        $pdo->prepare("INSERT INTO users (nama, email, nim, password, role_id, no_hp, jurusan, angkatan, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$nama, $email, $nim, password_hash($password, PASSWORD_DEFAULT), $role_id, $no_hp, $jurusan, $angkatan, $status]);
        log_activity($user_id, 'Tambah Pengguna', $nama);
        set_flash('success', 'Pengguna berhasil ditambahkan.');
    }
    redirect('/pengguna');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("UPDATE users SET deleted_at=NOW(), status='nonaktif' WHERE id=? AND role_id<>1")->execute([$id]);
    log_activity($user_id, 'Hapus Pengguna (soft)', "ID: $id");
    set_flash('success', 'Pengguna diarsipkan.');
    redirect('/pengguna');
}
if (isset($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $pdo->prepare("UPDATE users SET deleted_at=NULL, status='aktif' WHERE id=?")->execute([$id]);
    log_activity($user_id, 'Pulihkan Pengguna', "ID: $id");
    set_flash('success', 'Pengguna dipulihkan.');
    redirect('/pengguna');
}

$show_arsip = isset($_GET['arsip']);
$search = trim($_GET['search'] ?? '');
$sql = "SELECT u.*, r.nama AS role_name FROM users u JOIN roles r ON u.role_id=r.id WHERE u.deleted_at IS " . ($show_arsip ? 'NOT NULL' : 'NULL');
$params = [];
if ($search !== '') { $sql .= " AND (u.nama LIKE ? OR u.email LIKE ? OR u.nim LIKE ?)"; $params = ["%$search%","%$search%","%$search%"]; }
$sql .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $list = $stmt->fetchAll();

$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();

if (($_GET['ajax'] ?? '') === 'table') {
    include __DIR__ . '/../../components/tables/pengguna.php';
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $edit->execute([(int)$_GET['edit']]); $edit = $edit->fetch();
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-on-surface-variant" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
                <form method="GET" action="" class="w-full">
                    <?php if ($show_arsip): ?><input type="hidden" name="arsip" value="1"><?php endif; ?>
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-10 !pl-10 !text-sm w-full" placeholder="Cari nama/email/nim..." autocomplete="off" data-live-search data-target="#pengguna-table-body">
                </form>
            </div>
            <?php if (!$show_arsip): ?><button onclick="openModal('modalPengguna')" type="button" class="btn-primary !w-10 !h-10 !p-0 !rounded-full flex items-center justify-center" title="Tambah pengguna">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button><?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center gap-3">
                <h3 class="font-bold text-on-surface"><?= $show_arsip ? 'Arsip Pengguna' : 'Daftar Pengguna' ?></h3>
                <a href="?<?= $show_arsip ? '' : 'arsip=1' ?>" class="text-xs font-semibold text-primary hover:underline"><?= $show_arsip ? '← Kembali' : 'Lihat Arsip' ?></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>Email</th><th>NIM</th><th>Role</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody id="pengguna-table-body">
                        <?php include __DIR__ . '/../../components/tables/pengguna.php'; ?>
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
<form method="POST" action="<?= url('pengguna') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <?= csrf_input() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div><label class="block text-sm font-semibold text-on-surface mb-1.5">Nama</label><input type="text" name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="form-input"></div>
    <div><label class="block text-sm font-semibold text-on-surface mb-1.5">Email</label><input type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>" class="form-input"></div>
    <div><label class="block text-sm font-semibold text-on-surface mb-1.5">NIM</label><input type="text" name="nim" required value="<?= e($edit['nim'] ?? '') ?>" class="form-input"></div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Role</label>
        <select name="role_id" class="form-input">
            <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>" <?= ($edit['role_id'] ?? 3)==$r['id']?'selected':'' ?>><?= e($r['nama']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div><label class="block text-sm font-semibold text-on-surface mb-1.5">No. HP</label><input type="text" name="no_hp" value="<?= e($edit['no_hp'] ?? '') ?>" class="form-input"></div>
    <div><label class="block text-sm font-semibold text-on-surface mb-1.5">Jurusan</label><input type="text" name="jurusan" value="<?= e($edit['jurusan'] ?? '') ?>" class="form-input"></div>
    <div><label class="block text-sm font-semibold text-on-surface mb-1.5">Angkatan</label><input type="text" name="angkatan" value="<?= e($edit['angkatan'] ?? '') ?>" class="form-input"></div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
        <select name="status" class="form-input">
            <option value="aktif" <?= ($edit['status'] ?? '')==='aktif'?'selected':'' ?>>Aktif</option>
            <option value="nonaktif" <?= ($edit['status'] ?? '')==='nonaktif'?'selected':'' ?>>Nonaktif</option>
            <option value="menunggu" <?= ($edit['status'] ?? '')==='menunggu'?'selected':'' ?>>Menunggu</option>
        </select>
    </div>
    <div><label class="block text-sm font-semibold text-on-surface mb-1.5">Password <?= $edit ? '(kosongkan jika tetap)' : '' ?></label><input type="password" name="password" <?= $edit ? '' : 'required' ?> class="form-input"></div>
    <div class="md:col-span-3 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalPengguna')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Tambah Pengguna' ?></button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<?php if ($edit): ?><script>document.addEventListener('DOMContentLoaded',()=>openModal('modalPengguna'));</script><?php endif; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
