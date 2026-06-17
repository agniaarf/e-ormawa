<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Mahasiswa');

$page_title = 'Profil';
$current_page = 'profil';
$pdo = db();
$user_id = $_SESSION['user_id'];

$user = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$user->execute([$user_id]); $user = $user->fetch();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { $error = 'Token CSRF tidak valid.'; }
    else {
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $angkatan = trim($_POST['angkatan'] ?? '');
        $password = $_POST['password'] ?? '';

        $pdo->prepare("UPDATE users SET nama=?, no_hp=?, jurusan=?, angkatan=? WHERE id=?")
            ->execute([$nama, $no_hp, $jurusan, $angkatan, $user_id]);
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $user_id]);
        }
        $_SESSION['nama'] = $nama;
        set_flash('success', 'Profil berhasil diperbarui.');
        redirect('/pages/mahasiswa/profil.php');
    }
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant">
                <h3 class="font-bold text-on-surface">Edit Profil</h3>
            </div>
            <?php if ($error): ?><div class="mx-6 mt-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div><?php endif; ?>
            <form method="POST" action="" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <?= csrf_input() ?>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Nama</label>
                    <input type="text" name="nama" required value="<?= e($user['nama']) ?>" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Email</label>
                    <input type="email" disabled value="<?= e($user['email']) ?>" class="form-input bg-surface-low text-on-surface-variant">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">NIM</label>
                    <input type="text" disabled value="<?= e($user['nim']) ?>" class="form-input bg-surface-low text-on-surface-variant">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">No. HP</label>
                    <input type="text" name="no_hp" value="<?= e($user['no_hp'] ?? '') ?>" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Jurusan</label>
                    <input type="text" name="jurusan" value="<?= e($user['jurusan'] ?? '') ?>" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Angkatan</label>
                    <input type="text" name="angkatan" value="<?= e($user['angkatan'] ?? '') ?>" class="form-input" placeholder="Contoh: 2024">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Password Baru <span class="text-on-surface-variant font-normal">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Password baru">
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
