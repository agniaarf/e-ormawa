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
$pageNum = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

if (is_super_admin()) {
    $sql = "SELECT p.*, o.nama AS org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id=o.id WHERE 1=1";
    $params = [];
} else {
    $sql = "SELECT p.*, o.nama AS org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id=o.id WHERE (p.tipe='global' OR p.organisasi_id IN (SELECT organisasi_id FROM user_organisasi WHERE user_id=? AND status='aktif'))";
    $params = [$user_id];
}
if ($search !== '') { $sql .= " AND p.judul LIKE ?"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.created_at DESC";
$result = fetchPaginated($pdo, $sql, $params, $pageNum, $perPage);
$list = $result['list'];
$p = $result['p'];

if (($_GET['ajax'] ?? '') === 'table') {
    include __DIR__ . '/../../components/tables/pengumuman.php';
    exit;
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
            <?php if ($can_create): ?><button onclick="openPengumumanModal()" type="button" class="btn-primary !w-10 !h-10 !p-0 !rounded-full flex items-center justify-center" title="Tambah pengumuman">
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
$allow_global = is_super_admin();
$orgopts = $allow_global ? $pdo->query("SELECT id, nama FROM organisasi WHERE status='aktif' AND deleted_at IS NULL ORDER BY nama")->fetchAll() : [];
?>
<div id="modalPengumuman" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalPengumuman')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-lg" id="modalPengumumanTitle">Tambah Pengumuman</h3>
                <button type="button" onclick="closeModal('modalPengumuman')" class="p-1.5 rounded-lg hover:bg-surface-low text-on-surface-variant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <form method="POST" action="<?= url('pengumuman') ?>" class="grid grid-cols-1 md:grid-cols-2 gap-5" id="formPengumuman">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" id="pengumumanId" value="">
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-on-surface mb-1.5">Judul</label><input type="text" name="judul" id="pengumumanJudul" required value="" class="form-input"></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-on-surface mb-1.5">Isi</label><textarea name="isi" id="pengumumanIsi" rows="4" required class="form-input py-2"></textarea></div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Tipe</label>
                        <select name="tipe" id="pengumumanTipe" class="form-input" onchange="document.getElementById('pengumumanOrgWrap').classList.toggle('hidden', this.value!=='organisasi')">
                            <?php if ($allow_global): ?><option value="global">Global</option><?php endif; ?>
                            <option value="organisasi">Organisasi</option>
                        </select>
                    </div>
                    <div id="pengumumanOrgWrap" class="<?= $allow_global ? 'hidden' : '' ?>">
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Organisasi</label>
                        <select name="organisasi_id" id="pengumumanOrg" class="form-input"></select>
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button type="button" onclick="closeModal('modalPengumuman')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
                        <button type="submit" class="btn-primary" id="pengumumanSubmitBtn">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const allowGlobal = <?= json_encode($allow_global) ?>;
const orgOptions = <?= json_encode($allow_global ? $orgopts : $managed_orgs) ?>;
const isSuperAdmin = <?= json_encode($allow_global) ?>;
function buildOrgOptions(selected) {
    const el = document.getElementById('pengumumanOrg');
    el.innerHTML = '<option value="">Pilih organisasi</option>';
    if (isSuperAdmin) {
        for (const o of orgOptions) {
            const opt = document.createElement('option');
            opt.value = o.id;
            opt.textContent = o.nama;
            if (String(o.id) === String(selected)) opt.selected = true;
            el.appendChild(opt);
        }
    } else {
        for (const [oid, onama] of Object.entries(orgOptions)) {
            const opt = document.createElement('option');
            opt.value = oid;
            opt.textContent = onama;
            if (String(oid) === String(selected)) opt.selected = true;
            el.appendChild(opt);
        }
    }
}
function openPengumumanModal(row) {
    const isEdit = !!row;
    const defaultTipe = allowGlobal ? 'global' : 'organisasi';
    document.getElementById('modalPengumumanTitle').textContent = isEdit ? 'Edit Pengumuman' : 'Tambah Pengumuman';
    document.getElementById('pengumumanSubmitBtn').textContent = isEdit ? 'Simpan' : 'Tambah';
    document.getElementById('pengumumanId').value = isEdit ? row.id : '';
    document.getElementById('pengumumanJudul').value = isEdit ? row.judul : '';
    document.getElementById('pengumumanIsi').value = isEdit ? row.isi : '';
    const tipe = isEdit ? row.tipe : defaultTipe;
    document.getElementById('pengumumanTipe').value = tipe;
    document.getElementById('pengumumanOrgWrap').classList.toggle('hidden', tipe !== 'organisasi');
    buildOrgOptions(isEdit ? row.organisasi_id : '');
    openModal('modalPengumuman');
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/../../components/footer.php'; ?>
