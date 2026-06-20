<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pdo = db();
$user_id = (int) $_SESSION['user_id'];
$org_id = (int) ($_GET['id'] ?? $_GET['org_id'] ?? 0);

$org = $pdo->prepare("SELECT * FROM organisasi WHERE id=? AND deleted_at IS NULL LIMIT 1");
$org->execute([$org_id]);
$org = $org->fetch();
if (!$org) { set_flash('error', 'Organisasi tidak ditemukan.'); redirect('/organisasi'); }

$my_role = get_org_role($user_id, $org_id);
$page_title = $org['nama'];
$current_page = 'org_detail';
$current_org_id = $org_id;

// Leader update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); redirect('/organisasi/' . $org_id); }
    $intent = $_POST['intent'] ?? '';
    if ($intent === 'update_org') {
        require_can('organisasi.update', $org_id);
        $nama = trim($_POST['nama'] ?? '');
        $singkatan = trim($_POST['singkatan'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $visi = trim($_POST['visi'] ?? '');
        $misi = trim($_POST['misi'] ?? '');
        if ($nama === '') { set_flash('error', 'Nama wajib diisi.'); }
        else {
            $pdo->prepare("UPDATE organisasi SET nama=?, singkatan=?, deskripsi=?, visi=?, misi=? WHERE id=?")
                ->execute([$nama, $singkatan, $deskripsi, $visi, $misi, $org_id]);
            log_activity($user_id, 'Update Organisasi', "ID: $org_id");
            set_flash('success', 'Profil organisasi diperbarui.');
        }
        redirect('/organisasi/' . $org_id);
    }
    if ($intent === 'join') {
        if (!$my_role) {
            $pending = $pdo->prepare("SELECT id FROM permintaan_bergabung WHERE user_id=? AND organisasi_id=? AND status='menunggu' LIMIT 1");
            $pending->execute([$user_id, $org_id]);
            if (!$pending->fetch()) {
                $pdo->prepare("INSERT INTO permintaan_bergabung (user_id, organisasi_id, motivasi, status, created_at) VALUES (?, ?, ?, 'menunggu', NOW())")
                    ->execute([$user_id, $org_id, trim($_POST['motivasi'] ?? '')]);
                log_activity($user_id, 'Permintaan Bergabung', "Organisasi ID: $org_id");
                set_flash('success', 'Permintaan bergabung dikirim.');
            }
        }
        redirect('/organisasi/' . $org_id);
    }
}

$leader = $pdo->prepare("SELECT u.nama FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.organisasi_id=? AND uo.role='leader' AND uo.status='aktif' LIMIT 1");
$leader->execute([$org_id]); $leader_nama = $leader->fetchColumn() ?: '-';
$member_count = (int) ($pdo->query("SELECT COUNT(*) FROM user_organisasi WHERE organisasi_id={$org_id} AND status='aktif'")->fetchColumn());
$kegiatan_count = (int) ($pdo->query("SELECT COUNT(*) FROM kegiatan WHERE organisasi_id={$org_id} AND deleted_at IS NULL")->fetchColumn());

$pending_status = null;
if (!$my_role) {
    $p = $pdo->prepare("SELECT status FROM permintaan_bergabung WHERE user_id=? AND organisasi_id=? AND status IN ('menunggu','administrasi','wawancara') LIMIT 1");
    $p->execute([$user_id, $org_id]); $pending_status = $p->fetchColumn() ?: null;
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-4xl mx-auto space-y-6">
        <a href="<?= url('organisasi') ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali
        </a>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-extrabold text-xl"><?= e(strtoupper(substr($org['nama'],0,2))) ?></div>
                    <div>
                        <h2 class="text-xl font-bold text-on-surface"><?= e($org['nama']) ?></h2>
                        <p class="text-sm text-on-surface-variant"><?= e($org['singkatan'] ?? '') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($my_role): ?>
                        <span class="badge bg-primary/10 text-primary text-xs"><?= e(org_role_label($my_role)) ?></span>
                    <?php endif; ?>
                    <?php if (can('organisasi.update', $org_id)): ?>
                        <button onclick="openModal('modalEditOrg')" class="btn-primary !h-8 !px-3 !text-xs">Edit Profil</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-5 space-y-3 text-sm text-on-surface-variant">
                <p><strong class="text-on-surface">Leader:</strong> <?= e($leader_nama) ?></p>
                <p><strong class="text-on-surface">Deskripsi:</strong> <?= e($org['deskripsi'] ?: '-') ?></p>
                <p><strong class="text-on-surface">Visi:</strong> <?= e($org['visi'] ?: '-') ?></p>
                <p><strong class="text-on-surface">Misi:</strong> <?= e($org['misi'] ?: '-') ?></p>
            </div>
            <?php if (!$my_role): ?>
            <div class="mt-5 pt-5 border-t border-outline-variant">
                <?php if ($pending_status): ?>
                    <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
                        <p class="text-sm font-semibold text-yellow-800">Permintaan Anda sedang dalam proses rekrutmen</p>
                        <p class="text-xs text-yellow-700 mt-0.5">Status saat ini: <strong><?= e(ucfirst($pending_status)) ?></strong></p>
                        <?= recruitment_stepper($pending_status) ?>
                    </div>
                <?php else: ?>
                    <button onclick="openModal('modalJoin')" class="btn-primary">Gabung Organisasi</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($my_role): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="<?= url('organisasi/' . $org_id . '/member') ?>" class="bg-white rounded-2xl border border-outline-variant shadow-card p-5 flex items-center justify-between hover:bg-surface-low transition-colors">
                <div>
                    <p class="text-sm text-on-surface-variant"><?= can('member.manage', $org_id) ? 'Manajemen Member' : 'Lihat Member' ?></p>
                    <p class="text-2xl font-extrabold text-on-surface"><?= $member_count ?></p>
                </div>
                <svg class="w-8 h-8 text-primary/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
            </a>
            <a href="<?= url('organisasi/' . $org_id . '/kegiatan') ?>" class="bg-white rounded-2xl border border-outline-variant shadow-card p-5 flex items-center justify-between hover:bg-surface-low transition-colors">
                <div>
                    <p class="text-sm text-on-surface-variant"><?= can('kegiatan.manage', $org_id) ? 'Manajemen Kegiatan' : 'Lihat Kegiatan' ?></p>
                    <p class="text-2xl font-extrabold text-on-surface"><?= $kegiatan_count ?></p>
                </div>
                <svg class="w-8 h-8 text-primary/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75"/></svg>
            </a>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php if (can('organisasi.update', $org_id)):
$modal_id = 'modalEditOrg'; $modal_title = 'Edit Profil Organisasi'; ob_start();
?>
<form method="POST" action="<?= url('organisasi/' . $org_id) ?>" class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <?= csrf_input() ?>
    <input type="hidden" name="intent" value="update_org">
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Nama Organisasi</label>
        <input type="text" name="nama" required value="<?= e($org['nama']) ?>" class="form-input">
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Singkatan</label>
        <input type="text" name="singkatan" value="<?= e($org['singkatan'] ?? '') ?>" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="form-input py-2"><?= e($org['deskripsi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Visi</label>
        <textarea name="visi" rows="2" class="form-input py-2"><?= e($org['visi'] ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Misi</label>
        <textarea name="misi" rows="2" class="form-input py-2"><?= e($org['misi'] ?? '') ?></textarea>
    </div>
    <div class="md:col-span-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalEditOrg')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<?php endif; ?>

<?php if (!$my_role && !$pending_status):
$modal_id = 'modalJoin'; $modal_title = 'Gabung Organisasi'; ob_start();
?>
<form method="POST" action="<?= url('organisasi/' . $org_id) ?>" class="space-y-4">
    <?= csrf_input() ?>
    <input type="hidden" name="intent" value="join">
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
<?php endif; ?>

<?php require __DIR__ . '/../../components/footer.php'; ?>
