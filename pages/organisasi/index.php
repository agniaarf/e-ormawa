<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$page_title = 'Organisasi';
$current_page = 'organisasi';
$pdo = db();
$user_id = (int) $_SESSION['user_id'];

// ---------- POST handlers ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Token CSRF tidak valid.');
        redirect('/organisasi');
    }
    $intent = $_POST['intent'] ?? '';

    if ($intent === 'save_org') {
        // Super Admin only: create/update organisation
        require_can('organisasi.create');
        $id = $_POST['id'] ?? '';
        $nama = trim($_POST['nama'] ?? '');
        $singkatan = trim($_POST['singkatan'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $visi = trim($_POST['visi'] ?? '');
        $misi = trim($_POST['misi'] ?? '');
        $status = $_POST['status'] ?? 'aktif';
        $leader_id = !empty($_POST['leader_id']) ? (int) $_POST['leader_id'] : null;

        if ($nama === '') {
            set_flash('error', 'Nama organisasi wajib diisi.');
        } elseif ($id) {
            $pdo->prepare("UPDATE organisasi SET nama=?, singkatan=?, deskripsi=?, visi=?, misi=?, status=? WHERE id=?")
                ->execute([$nama, $singkatan, $deskripsi, $visi, $misi, $status, $id]);
            if ($leader_id) {
                $pdo->prepare("INSERT INTO user_organisasi (user_id, organisasi_id, role, status, created_at) VALUES (?, ?, 'leader', 'aktif', NOW()) ON DUPLICATE KEY UPDATE role='leader', status='aktif'")
                    ->execute([$leader_id, $id]);
            }
            log_activity($user_id, 'Update Organisasi', "ID: $id");
            set_flash('success', 'Organisasi berhasil diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO organisasi (nama, singkatan, deskripsi, visi, misi, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
                ->execute([$nama, $singkatan, $deskripsi, $visi, $misi, $status]);
            $new_id = (int) $pdo->lastInsertId();
            if ($leader_id) {
                $pdo->prepare("INSERT INTO user_organisasi (user_id, organisasi_id, role, status, created_at) VALUES (?, ?, 'leader', 'aktif', NOW())")
                    ->execute([$leader_id, $new_id]);
            }
            log_activity($user_id, 'Tambah Organisasi', $nama);
            set_flash('success', 'Organisasi berhasil ditambahkan.');
        }
        redirect('/organisasi');
    }

    if ($intent === 'join') {
        // Mahasiswa: request to join an organisation
        $org_id = (int) ($_POST['org_id'] ?? 0);
        $motivasi = trim($_POST['motivasi'] ?? '');
        $existing_role = get_org_role($user_id, $org_id);
        $pending = $pdo->prepare("SELECT id FROM permintaan_bergabung WHERE user_id=? AND organisasi_id=? AND status IN ('menunggu','administrasi','wawancara') LIMIT 1");
        $pending->execute([$user_id, $org_id]);
        if ($existing_role) {
            set_flash('error', 'Anda sudah menjadi anggota organisasi ini.');
        } elseif ($pending->fetch()) {
            set_flash('error', 'Anda sudah memiliki permintaan yang menunggu pada organisasi ini.');
        } else {
            $pdo->prepare("INSERT INTO permintaan_bergabung (user_id, organisasi_id, motivasi, status, created_at) VALUES (?, ?, ?, 'menunggu', NOW())")
                ->execute([$user_id, $org_id, $motivasi]);
            // notify leaders/staff
            $mgr = $pdo->prepare("SELECT user_id FROM user_organisasi WHERE organisasi_id=? AND role IN ('leader','staff') AND status='aktif'");
            $mgr->execute([$org_id]);
            foreach ($mgr->fetchAll() as $m) {
                send_notification((int) $m['user_id'], 'Permintaan Bergabung Baru', ($_SESSION['nama'] ?? 'Seseorang') . ' ingin bergabung.', 'info');
            }
            log_activity($user_id, 'Permintaan Bergabung', "Organisasi ID: $org_id");
            set_flash('success', 'Permintaan bergabung berhasil dikirim. Menunggu persetujuan.');
        }
        redirect('/organisasi');
    }
    redirect('/organisasi');
}

// ---------- Soft delete (Super Admin) ----------
if (isset($_GET['delete'])) {
    require_can('organisasi.delete');
    $id = (int) $_GET['delete'];
    $pdo->prepare("UPDATE organisasi SET deleted_at=NOW() WHERE id=?")->execute([$id]);
    log_activity($user_id, 'Hapus Organisasi (soft)', "ID: $id");
    set_flash('success', 'Organisasi dipindahkan ke arsip.');
    redirect('/organisasi');
}
if (isset($_GET['restore'])) {
    require_can('organisasi.delete');
    $id = (int) $_GET['restore'];
    $pdo->prepare("UPDATE organisasi SET deleted_at=NULL WHERE id=?")->execute([$id]);
    log_activity($user_id, 'Pulihkan Organisasi', "ID: $id");
    set_flash('success', 'Organisasi dipulihkan.');
    redirect('/organisasi');
}

// ---------- Data ----------
$search = trim($_GET['search'] ?? '');
$show_arsip = is_super_admin() && isset($_GET['arsip']);
$pageNum = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$users = $pdo->query("SELECT id, nama, nim FROM users WHERE status='aktif' AND deleted_at IS NULL AND role_id=3 ORDER BY nama")->fetchAll();

if (is_super_admin()) {
    $sql = "SELECT o.*, (SELECT u.id FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.organisasi_id=o.id AND uo.role='leader' AND uo.status='aktif' LIMIT 1) AS leader_id, (SELECT u.nama FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.organisasi_id=o.id AND uo.role='leader' AND uo.status='aktif' LIMIT 1) AS leader_nama
            FROM organisasi o WHERE o.deleted_at IS " . ($show_arsip ? 'NOT NULL' : 'NULL');
    $countSql = "SELECT COUNT(*) FROM organisasi o WHERE o.deleted_at IS " . ($show_arsip ? 'NOT NULL' : 'NULL');
    $params = [];
    if ($search !== '') { $sql .= " AND (o.nama LIKE ? OR o.singkatan LIKE ?)"; $countSql .= " AND (o.nama LIKE ? OR o.singkatan LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $sql .= " ORDER BY o.created_at DESC";
    $result = fetchPaginated($pdo, $sql, $params, $pageNum, $perPage, $countSql);
    $list = $result['list'];
    $p = $result['p'];
} else {
    // Mahasiswa browse
    $sql = "SELECT o.* FROM organisasi o WHERE o.status='aktif' AND o.deleted_at IS NULL";
    $params = [];
    if ($search !== '') { $sql .= " AND (o.nama LIKE ? OR o.singkatan LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $sql .= " ORDER BY o.nama";
    $result = fetchPaginated($pdo, $sql, $params, $pageNum, $perPage);
    $list = $result['list'];
    $p = $result['p'];

    // membership + pending status maps
    $mine = $pdo->prepare("SELECT organisasi_id, role FROM user_organisasi WHERE user_id=? AND status='aktif'");
    $mine->execute([$user_id]);
    $my_roles = [];
    foreach ($mine->fetchAll() as $r) { $my_roles[(int)$r['organisasi_id']] = $r['role']; }
    $pend = $pdo->prepare("SELECT organisasi_id, status FROM permintaan_bergabung WHERE user_id=? AND status IN ('menunggu','administrasi','wawancara')");
    $pend->execute([$user_id]);
    $my_pending = [];
    foreach ($pend->fetchAll() as $row) { $my_pending[(int)$row['organisasi_id']] = $row['status']; }
}

// AJAX partial for Super Admin table
if (is_super_admin() && ($_GET['ajax'] ?? '') === 'table') {
    include __DIR__ . '/../../components/tables/organisasi.php';
    exit;
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
    <?php if (is_super_admin()): ?>
        <div class="flex items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-on-surface-variant" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
                <form method="GET" action="" class="w-full">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-10 !pl-10 !text-sm w-full" placeholder="Cari organisasi..." autocomplete="off" data-live-search data-target="#org-table-body">
                </form>
            </div>
            <button onclick="openOrganisasiModal()" type="button" class="btn-primary !w-10 !h-10 !p-0 !rounded-full flex items-center justify-center" title="Tambah organisasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant">
                <h3 class="font-bold text-on-surface">Daftar Organisasi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>Singkatan</th><th>Leader</th><th>Status</th><th>Dibuat</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody id="org-table-body">
                        <?php include __DIR__ . '/../../components/tables/organisasi.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-on-surface">Jelajah Organisasi</h2>
            <div class="relative max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-on-surface-variant" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
                <form method="GET" action="" class="w-full">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-10 !pl-10 !text-sm w-full" placeholder="Cari organisasi..." autocomplete="off" data-live-search>
                </form>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($list as $row):
                $oid = (int) $row['id'];
                $role_here = $my_roles[$oid] ?? null;
                $pending_status = $my_pending[$oid] ?? null;
            ?>
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card card-hover p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm"><?= e(strtoupper(substr($row['nama'],0,2))) ?></div>
                    <div>
                        <h4 class="font-bold text-on-surface"><?= e($row['nama']) ?></h4>
                        <p class="text-xs text-on-surface-variant"><?= e($row['singkatan'] ?? '') ?></p>
                    </div>
                </div>
                <p class="text-sm text-on-surface-variant line-clamp-3 flex-1"><?= e($row['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
                <div class="mt-4 flex items-center gap-2">
                    <a href="<?= url('organisasi/' . $oid) ?>" class="flex-1 text-center px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-semibold hover:bg-surface-low">Lihat Detail</a>
                    <?php if ($role_here): ?>
                        <span class="badge bg-green-100 text-green-700 text-xs"><?= e(org_role_label($role_here)) ?></span>
                    <?php elseif ($pending_status): ?>
                        <span class="badge bg-yellow-100 text-yellow-700 text-xs"><?= e(ucfirst($pending_status)) ?></span>
                    <?php else: ?>
                        <button type="button" onclick='openJoin(<?= $oid ?>, <?= json_encode($row['nama']) ?>)' class="btn-primary !h-auto !py-2 !px-3 !text-sm">Gabung</button>
                    <?php endif; ?>
                </div>
                <?php if ($pending_status): ?>
                <div class="mt-3"><?= recruitment_stepper($pending_status) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (empty($list)): ?><div class="col-span-full text-center text-on-surface-variant py-12">Belum ada organisasi tersedia.</div><?php endif; ?>
        </div>
        <?php if (!empty($list)): ?><div class="mt-2"><?= renderPagination($p) ?></div><?php endif; ?>
    <?php endif; ?>
    </div>
</main>

<?php if (is_super_admin()): ?>
<div id="modalOrganisasi" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalOrganisasi')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-lg" id="modalOrganisasiTitle">Tambah Organisasi</h3>
                <button type="button" onclick="closeModal('modalOrganisasi')" class="p-1.5 rounded-lg hover:bg-surface-low text-on-surface-variant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <form method="POST" action="<?= url('organisasi') ?>" class="grid grid-cols-1 md:grid-cols-2 gap-5" id="formOrganisasi">
                    <?= csrf_input() ?>
                    <input type="hidden" name="intent" value="save_org">
                    <input type="hidden" name="id" id="orgId" value="">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Nama Organisasi</label>
                        <input type="text" name="nama" id="orgNama" required value="" class="form-input" placeholder="Nama lengkap organisasi">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Singkatan</label>
                        <input type="text" name="singkatan" id="orgSingkatan" value="" class="form-input" placeholder="Contoh: BEM, HIMTI">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
                        <select name="status" id="orgStatus" class="form-input">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Leader (Ketua)</label>
                        <select name="leader_id" id="orgLeader" class="form-input"></select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Deskripsi</label>
                        <textarea name="deskripsi" id="orgDeskripsi" rows="2" class="form-input py-2" placeholder="Deskripsi singkat"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Visi</label>
                        <textarea name="visi" id="orgVisi" rows="2" class="form-input py-2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Misi</label>
                        <textarea name="misi" id="orgMisi" rows="2" class="form-input py-2"></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button type="button" onclick="closeModal('modalOrganisasi')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
                        <button type="submit" class="btn-primary" id="orgSubmitBtn">Tambah Organisasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modalDetail" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalDetail')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-lg">Detail Organisasi</h3>
                <button type="button" onclick="closeModal('modalDetail')" class="p-1.5 rounded-lg hover:bg-surface-low text-on-surface-variant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Nama</label>
                        <p class="text-sm font-medium text-on-surface" id="detailNama"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Singkatan</label>
                        <p class="text-sm text-on-surface" id="detailSingkatan"></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Leader</label>
                        <p class="text-sm text-on-surface" id="detailLeader"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Status</label>
                        <span id="detailStatus" class="badge"></span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Dibuat</label>
                    <p class="text-sm text-on-surface" id="detailCreated"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Deskripsi</label>
                    <p class="text-sm text-on-surface whitespace-pre-wrap" id="detailDeskripsi"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Visi</label>
                    <p class="text-sm text-on-surface whitespace-pre-wrap" id="detailVisi"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Misi</label>
                    <p class="text-sm text-on-surface whitespace-pre-wrap" id="detailMisi"></p>
                </div>
                <div class="flex justify-end pt-4">
                    <button type="button" onclick="closeModal('modalDetail')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const users = <?= json_encode($users) ?>;
function buildOrgLeader(selected) {
    const el = document.getElementById('orgLeader');
    el.innerHTML = '<option value="">-- Pilih Leader --</option>';
    for (const u of users) {
        const opt = document.createElement('option');
        opt.value = u.id;
        opt.textContent = `${u.nama} (${u.nim})`;
        if (String(u.id) === String(selected)) opt.selected = true;
        el.appendChild(opt);
    }
}
function openOrganisasiModal(row) {
    const isEdit = !!row;
    document.getElementById('modalOrganisasiTitle').textContent = isEdit ? 'Edit Organisasi' : 'Tambah Organisasi';
    document.getElementById('orgSubmitBtn').textContent = isEdit ? 'Simpan Perubahan' : 'Tambah Organisasi';
    document.getElementById('orgId').value = isEdit ? row.id : '';
    document.getElementById('orgNama').value = isEdit ? row.nama : '';
    document.getElementById('orgSingkatan').value = isEdit ? row.singkatan : '';
    document.getElementById('orgStatus').value = isEdit ? row.status : 'aktif';
    document.getElementById('orgDeskripsi').value = isEdit ? row.deskripsi : '';
    document.getElementById('orgVisi').value = isEdit ? row.visi : '';
    document.getElementById('orgMisi').value = isEdit ? row.misi : '';
    buildOrgLeader(isEdit ? row.leader_id : '');
    openModal('modalOrganisasi');
}
function openDetailModal(row) {
    document.getElementById('detailNama').textContent = row.nama;
    document.getElementById('detailSingkatan').textContent = row.singkatan || '-';
    document.getElementById('detailLeader').textContent = row.leader_nama || '-';
    document.getElementById('detailStatus').textContent = row.status;
    document.getElementById('detailStatus').className = 'badge ' + (row.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600');
    document.getElementById('detailDeskripsi').textContent = row.deskripsi || 'Tidak ada deskripsi.';
    document.getElementById('detailVisi').textContent = row.visi || 'Tidak ada visi.';
    document.getElementById('detailMisi').textContent = row.misi || 'Tidak ada misi.';
    document.getElementById('detailCreated').textContent = new Date(row.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    openModal('modalDetail');
}
</script>

<?php else:
// Mahasiswa join modal
$modal_id = 'modalJoin';
$modal_title = 'Gabung Organisasi';
ob_start();
?>
<form method="POST" action="<?= url('organisasi') ?>" class="space-y-4">
    <?= csrf_input() ?>
    <input type="hidden" name="intent" value="join">
    <input type="hidden" name="org_id" id="join_org_id" value="">
    <p class="text-sm text-on-surface-variant">Anda akan mengirim permintaan bergabung ke <strong id="join_org_name" class="text-on-surface"></strong>.</p>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Motivasi Bergabung</label>
        <textarea name="motivasi" rows="4" required class="form-input py-2" placeholder="Ceritakan motivasi Anda..."></textarea>
    </div>
    <div class="flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalJoin')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary">Kirim Permintaan</button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<script>
function openJoin(id, name) {
    document.getElementById('join_org_id').value = id;
    document.getElementById('join_org_name').textContent = name;
    openModal('modalJoin');
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/../../components/footer.php'; ?>
