<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$page_title = 'Pengumuman';
$current_page = 'pengumuman';
$pdo = db();
$user_id = (int) $_SESSION['user_id'];

// Organisations the current user may post announcements for
$managed_orgs = [];
if (!is_super_admin()) {
    foreach (my_organisasi($user_id) as $o) {
        if (in_array($o['my_role'], ['leader','staff'], true)) {
            $managed_orgs[(int)$o['id']] = $o['nama'];
        }
    }
}
$can_create = is_super_admin() || !empty($managed_orgs);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); redirect('/pengumuman'); }
    $id = $_POST['id'] ?? '';
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tipe = $_POST['tipe'] ?? 'global';
    $organisasi_id = !empty($_POST['organisasi_id']) ? (int) $_POST['organisasi_id'] : null;

    // Permission: only Super Admin may post global; org posts require manage rights
    if ($tipe === 'global') {
        require_can('pengumuman.manage'); // SA only (false for others)
        if (!is_super_admin()) { set_flash('error', 'Akses ditolak.'); redirect('/pengumuman'); }
        $organisasi_id = null;
    } else {
        if (!is_super_admin() && !can('pengumuman.manage', $organisasi_id)) {
            set_flash('error', 'Anda tidak dapat membuat pengumuman untuk organisasi ini.');
            redirect('/pengumuman');
        }
    }

    if ($judul === '' || $isi === '') {
        set_flash('error', 'Judul dan isi wajib diisi.');
    } elseif ($id) {
        $pdo->prepare("UPDATE pengumuman SET judul=?, isi=?, tipe=?, organisasi_id=? WHERE id=?")->execute([$judul, $isi, $tipe, $organisasi_id, $id]);
        log_activity($user_id, 'Update Pengumuman', "ID: $id");
        set_flash('success', 'Pengumuman diperbarui.');
    } else {
        $pdo->prepare("INSERT INTO pengumuman (judul, isi, tipe, organisasi_id, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())")->execute([$judul, $isi, $tipe, $organisasi_id, $user_id]);
        log_activity($user_id, 'Tambah Pengumuman', $judul);
        if ($tipe === 'global') {
            foreach ($pdo->query("SELECT id FROM users WHERE status='aktif' AND deleted_at IS NULL")->fetchAll() as $u) {
                send_notification((int)$u['id'], 'Pengumuman: ' . $judul, $isi, 'info');
            }
        } elseif ($organisasi_id) {
            $m = $pdo->prepare("SELECT user_id FROM user_organisasi WHERE organisasi_id=? AND status='aktif'");
            $m->execute([$organisasi_id]);
            foreach ($m->fetchAll() as $a) { send_notification((int)$a['user_id'], 'Pengumuman: ' . $judul, $isi, 'info'); }
        }
        set_flash('success', 'Pengumuman ditambahkan.');
    }
    redirect('/pengumuman');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // verify rights
    $p = $pdo->prepare("SELECT * FROM pengumuman WHERE id=? LIMIT 1"); $p->execute([$id]); $p = $p->fetch();
    if ($p && (is_super_admin() || ($p['tipe']==='organisasi' && can('pengumuman.manage', (int)$p['organisasi_id'])))) {
        $pdo->prepare("DELETE FROM pengumuman WHERE id=?")->execute([$id]);
        log_activity($user_id, 'Hapus Pengumuman', "ID: $id");
        set_flash('success', 'Pengumuman dihapus.');
    } else {
        set_flash('error', 'Akses ditolak.');
    }
    redirect('/pengumuman');
}

// Listing
$search = trim($_GET['search'] ?? '');
if (is_super_admin()) {
    $sql = "SELECT p.*, o.nama AS org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id=o.id WHERE 1=1";
    $params = [];
} else {
    $sql = "SELECT p.*, o.nama AS org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id=o.id WHERE (p.tipe='global' OR p.organisasi_id IN (SELECT organisasi_id FROM user_organisasi WHERE user_id=? AND status='aktif'))";
    $params = [$user_id];
}
if ($search !== '') { $sql .= " AND p.judul LIKE ?"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $list = $stmt->fetchAll();

if (($_GET['ajax'] ?? '') === 'table') {
    include __DIR__ . '/../../components/tables/pengumuman.php';
    exit;
}

$edit = null;
if ($can_create && isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM pengumuman WHERE id=? LIMIT 1");
    $edit->execute([(int)$_GET['edit']]); $edit = $edit->fetch();
    // verify edit rights
    if ($edit && !is_super_admin() && !($edit['tipe']==='organisasi' && can('pengumuman.manage', (int)$edit['organisasi_id']))) { $edit = null; }
}

function canModifyPengumuman(array $p): bool {
    if (is_super_admin()) return true;
    return $p['tipe']==='organisasi' && can('pengumuman.manage', (int)$p['organisasi_id']);
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
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-10 !pl-10 !text-sm w-full" placeholder="Cari pengumuman..." autocomplete="off" data-live-search data-target="#pengumuman-table-body">
                </form>
            </div>
            <?php if ($can_create): ?><button onclick="openModal('modalPengumuman')" type="button" class="btn-primary !w-10 !h-10 !p-0 !rounded-full flex items-center justify-center" title="Tambah pengumuman">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button><?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant">
                <h3 class="font-bold text-on-surface">Daftar Pengumuman</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Judul</th><th>Tipe</th><th>Organisasi</th><th>Tanggal</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody id="pengumuman-table-body">
                        <?php include __DIR__ . '/../../components/tables/pengumuman.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($can_create):
$modal_id = 'modalPengumuman'; $modal_title = $edit ? 'Edit Pengumuman' : 'Tambah Pengumuman'; ob_start();
$allow_global = is_super_admin();
?>
<form method="POST" action="<?= url('pengumuman') ?>" class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <?= csrf_input() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="md:col-span-2"><label class="block text-sm font-semibold text-on-surface mb-1.5">Judul</label><input type="text" name="judul" required value="<?= e($edit['judul'] ?? '') ?>" class="form-input"></div>
    <div class="md:col-span-2"><label class="block text-sm font-semibold text-on-surface mb-1.5">Isi</label><textarea name="isi" rows="4" required class="form-input py-2"><?= e($edit['isi'] ?? '') ?></textarea></div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Tipe</label>
        <select name="tipe" id="peng-tipe" class="form-input" onchange="document.getElementById('peng-org').classList.toggle('hidden', this.value!=='organisasi')">
            <?php if ($allow_global): ?><option value="global" <?= ($edit['tipe'] ?? 'global')==='global'?'selected':'' ?>>Global</option><?php endif; ?>
            <option value="organisasi" <?= ($edit['tipe'] ?? ($allow_global?'global':'organisasi'))==='organisasi'?'selected':'' ?>>Organisasi</option>
        </select>
    </div>
    <div id="peng-org" class="<?= ($edit['tipe'] ?? ($allow_global?'global':'organisasi'))==='organisasi' ? '' : 'hidden' ?>">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Organisasi</label>
        <select name="organisasi_id" class="form-input">
            <option value="">Pilih organisasi</option>
            <?php
            if (is_super_admin()) {
                $orgopts = $pdo->query("SELECT id, nama FROM organisasi WHERE status='aktif' AND deleted_at IS NULL ORDER BY nama")->fetchAll();
                foreach ($orgopts as $o): ?>
                    <option value="<?= $o['id'] ?>" <?= ($edit['organisasi_id'] ?? '')==$o['id']?'selected':'' ?>><?= e($o['nama']) ?></option>
                <?php endforeach;
            } else {
                foreach ($managed_orgs as $oid => $onama): ?>
                    <option value="<?= $oid ?>" <?= ($edit['organisasi_id'] ?? '')==$oid?'selected':'' ?>><?= e($onama) ?></option>
                <?php endforeach;
            }
            ?>
        </select>
    </div>
    <div class="md:col-span-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalPengumuman')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<?php if ($edit): ?><script>document.addEventListener('DOMContentLoaded',()=>openModal('modalPengumuman'));</script><?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../../components/footer.php'; ?>
