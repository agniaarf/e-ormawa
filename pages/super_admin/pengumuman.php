<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Super Admin');

$page_title = 'Pengumuman';
$current_page = 'pengumuman';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Token CSRF tidak valid.');
    } else {
        $id = $_POST['id'] ?? '';
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi'] ?? '');
        $tipe = $_POST['tipe'] ?? 'global';
        $organisasi_id = !empty($_POST['organisasi_id']) ? (int)$_POST['organisasi_id'] : null;

        if (empty($judul) || empty($isi)) {
            set_flash('error', 'Judul dan isi wajib diisi.');
        } else {
            $uid = $_SESSION['user_id'];
            if ($id) {
                $pdo->prepare("UPDATE pengumuman SET judul=?, isi=?, tipe=?, organisasi_id=? WHERE id=?")->execute([$judul, $isi, $tipe, $organisasi_id, $id]);
                log_activity($uid, 'Update Pengumuman', "ID: $id");
                set_flash('success', 'Pengumuman diperbarui.');
            } else {
                $pdo->prepare("INSERT INTO pengumuman (judul, isi, tipe, organisasi_id, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())")->execute([$judul, $isi, $tipe, $organisasi_id, $uid]);
                log_activity($uid, 'Tambah Pengumuman', $judul);
                // Notify relevant users
                if ($tipe === 'global') {
                    $users = $pdo->query("SELECT id FROM users WHERE status='aktif'")->fetchAll();
                    foreach ($users as $u) {
                        send_notification((int)$u['id'], 'Pengumuman: ' . $judul, $isi, 'info');
                    }
                } elseif ($organisasi_id) {
                    $anggota = $pdo->prepare("SELECT user_id FROM anggota WHERE organisasi_id = ? AND status='aktif'");
                    $anggota->execute([$organisasi_id]);
                    foreach ($anggota->fetchAll() as $a) {
                        send_notification((int)$a['user_id'], 'Pengumuman: ' . $judul, $isi, 'info');
                    }
                }
                set_flash('success', 'Pengumuman ditambahkan.');
            }
        }
    }
    redirect('/pages/super_admin/pengumuman.php');
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM pengumuman WHERE id=?")->execute([(int)$_GET['delete']]);
    log_activity($_SESSION['user_id'], 'Hapus Pengumuman', "ID: {$_GET['delete']}");
    set_flash('success', 'Pengumuman dihapus.');
    redirect('/pages/super_admin/pengumuman.php');
}

$list = $pdo->query("SELECT p.*, o.nama as org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id = o.id ORDER BY p.created_at DESC")->fetchAll();
$organisasi = $pdo->query("SELECT id, nama FROM organisasi WHERE status='aktif' ORDER BY nama")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM pengumuman WHERE id=? LIMIT 1");
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
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-on-surface">Daftar Pengumuman</h3>
                    <button onclick="openModal('modalPengumuman')" type="button" class="btn-primary !h-8 !px-3 !text-xs">+ Tambah</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Judul</th><th>Tipe</th><th>Organisasi</th><th>Tanggal</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($row['judul']) ?></td>
                            <td><span class="badge <?= $row['tipe']==='global'?'bg-blue-100 text-blue-700':'bg-purple-100 text-purple-700' ?>"><?= e($row['tipe']) ?></span></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['org_nama'] ?? '-') ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e(date('d M Y', strtotime($row['created_at']))) ?></td>
                            <td class="text-right">
                                <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?><tr><td colspan="5" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
$modal_id = 'modalPengumuman';
$modal_title = $edit ? 'Edit Pengumuman' : 'Tambah Pengumuman';
ob_start();
?>
<form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <?= csrf_input() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Judul</label>
        <input type="text" name="judul" required value="<?= e($edit['judul'] ?? '') ?>" class="form-input" placeholder="Judul pengumuman">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Isi</label>
        <textarea name="isi" rows="4" required class="form-input py-2" placeholder="Isi pengumuman"><?= e($edit['isi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Tipe</label>
        <select name="tipe" class="form-input" onchange="this.value==='organisasi' ? document.getElementById('org-select-modal').classList.remove('hidden') : document.getElementById('org-select-modal').classList.add('hidden')">
            <option value="global" <?= ($edit['tipe'] ?? '')==='global'?'selected':'' ?>>Global</option>
            <option value="organisasi" <?= ($edit['tipe'] ?? '')==='organisasi'?'selected':'' ?>>Organisasi</option>
        </select>
    </div>
    <div id="org-select-modal" class="<?= ($edit['tipe'] ?? '')==='organisasi' ? '' : 'hidden' ?>">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Organisasi</label>
        <select name="organisasi_id" class="form-input">
            <option value="">Pilih organisasi</option>
            <?php foreach ($organisasi as $o): ?>
            <option value="<?= $o['id'] ?>" <?= ($edit['organisasi_id'] ?? '')==$o['id']?'selected':'' ?>><?= e($o['nama']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalPengumuman')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    </div>
</form>
<?php
$modal_content = ob_get_clean();
require __DIR__ . '/../../components/modal.php';
?>
<?php if ($edit): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modalPengumuman'); });</script>
<?php endif; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
