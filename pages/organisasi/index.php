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

if (is_super_admin()) {
    $sql = "SELECT o.*, (SELECT u.nama FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.organisasi_id=o.id AND uo.role='leader' AND uo.status='aktif' LIMIT 1) AS leader_nama
            FROM organisasi o WHERE o.deleted_at IS " . ($show_arsip ? 'NOT NULL' : 'NULL');
    $params = [];
    if ($search !== '') { $sql .= " AND (o.nama LIKE ? OR o.singkatan LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $sql .= " ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $list = $stmt->fetchAll();

    $edit = null;
    if (isset($_GET['edit'])) {
        $edit = $pdo->prepare("SELECT * FROM organisasi WHERE id=? LIMIT 1");
        $edit->execute([(int) $_GET['edit']]); $edit = $edit->fetch();
        if ($edit) {
            $le = $pdo->prepare("SELECT user_id FROM user_organisasi WHERE organisasi_id=? AND role='leader' AND status='aktif' LIMIT 1");
            $le->execute([$edit['id']]); $edit['leader_id'] = $le->fetchColumn() ?: '';
        }
    }
} else {
    // Mahasiswa browse
    $sql = "SELECT o.* FROM organisasi o WHERE o.status='aktif' AND o.deleted_at IS NULL";
    $params = [];
    if ($search !== '') { $sql .= " AND (o.nama LIKE ? OR o.singkatan LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $sql .= " ORDER BY o.nama";
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $list = $stmt->fetchAll();

    // membership + pending status maps
    $mine = $pdo->prepare("SELECT organisasi_id, role FROM user_organisasi WHERE user_id=? AND status='aktif'");
    $mine->execute([$user_id]);
    $my_roles = [];
    foreach ($mine->fetchAll() as $r) { $my_roles[(int)$r['organisasi_id']] = $r['role']; }
    $pend = $pdo->prepare("SELECT organisasi_id, status FROM permintaan_bergabung WHERE user_id=? AND status IN ('menunggu','administrasi','wawancara')");
    $pend->execute([$user_id]);
    $my_pending = [];
    foreach ($pend->fetchAll() as $p) { $my_pending[(int)$p['organisasi_id']] = $p['status']; }
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
            <button onclick="openModal('modalOrganisasi')" type="button" class="btn-primary !w-10 !h-10 !p-0 !rounded-full flex items-center justify-center" title="Tambah organisasi">
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
    <?php endif; ?>
    </div>
</main>

<?php if (is_super_admin()):
$modal_id = 'modalOrganisasi';
$modal_title = ($edit ?? null) ? 'Edit Organisasi' : 'Tambah Organisasi';
ob_start();
?>
<form method="POST" action="<?= url('organisasi') ?>" class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <?= csrf_input() ?>
    <input type="hidden" name="intent" value="save_org">
    <?php if ($edit ?? null): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
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
            <option value="aktif" <?= ($edit['status'] ?? '')==='aktif'?'selected':'' ?>>Aktif</option>
            <option value="nonaktif" <?= ($edit['status'] ?? '')==='nonaktif'?'selected':'' ?>>Nonaktif</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Leader (Ketua)</label>
        <select name="leader_id" class="form-input">
            <option value="">-- Pilih Leader --</option>
            <?php foreach ($pdo->query("SELECT id, nama, nim FROM users WHERE status='aktif' AND deleted_at IS NULL AND role_id=3 ORDER BY nama")->fetchAll() as $u): ?>
            <option value="<?= $u['id'] ?>" <?= ($edit['leader_id'] ?? '')==$u['id']?'selected':'' ?>><?= e($u['nama']) ?> (<?= e($u['nim']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="form-input py-2" placeholder="Deskripsi singkat"><?= e($edit['deskripsi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Visi</label>
        <textarea name="visi" rows="2" class="form-input py-2"><?= e($edit['visi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Misi</label>
        <textarea name="misi" rows="2" class="form-input py-2"><?= e($edit['misi'] ?? '') ?></textarea>
    </div>
    <div class="md:col-span-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalOrganisasi')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary"><?= ($edit ?? null) ? 'Simpan Perubahan' : 'Tambah Organisasi' ?></button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<?php if ($edit ?? null): ?><script>document.addEventListener('DOMContentLoaded',()=>openModal('modalOrganisasi'));</script><?php endif; ?>

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
