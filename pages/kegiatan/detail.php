<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pdo = db();
$user_id = (int) $_SESSION['user_id'];
$org_id = (int) ($_GET['org_id'] ?? 0);
$keg_id = (int) ($_GET['id'] ?? 0);

$keg = $pdo->prepare("SELECT k.*, o.nama AS org_nama FROM kegiatan k JOIN organisasi o ON k.organisasi_id=o.id WHERE k.id=? AND k.organisasi_id=? AND k.deleted_at IS NULL LIMIT 1");
$keg->execute([$keg_id, $org_id]); $keg = $keg->fetch();
if (!$keg) { set_flash('error', 'Kegiatan tidak ditemukan.'); redirect('/organisasi/' . $org_id . '/kegiatan'); }

require_can('kegiatan.read', $org_id);

$page_title = $keg['nama'];
$current_page = 'org_kegiatan';
$current_org_id = $org_id;

$tipe_label = ucfirst($keg['tipe']);
$status_class = $keg['status']==='berlangsung'?'bg-green-100 text-green-700':($keg['status']==='selesai'?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-600');
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-3xl mx-auto space-y-6">
        <a href="<?= url('organisasi/' . $org_id . '/kegiatan') ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kegiatan <?= e($keg['org_nama']) ?>
        </a>
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-xl font-bold text-on-surface"><?= e($keg['nama']) ?></h2>
                <span class="badge <?= $status_class ?>"><?= ucfirst($keg['status']) ?></span>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="badge bg-primary/10 text-primary text-xs"><?= e($tipe_label) ?></span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-on-surface-variant">Tanggal Mulai</p>
                    <p class="font-semibold text-on-surface"><?= e(date('d M Y H:i', strtotime($keg['tanggal_mulai']))) ?></p>
                </div>
                <div>
                    <p class="text-on-surface-variant">Tanggal Selesai</p>
                    <p class="font-semibold text-on-surface"><?= $keg['tanggal_selesai'] ? e(date('d M Y H:i', strtotime($keg['tanggal_selesai']))) : '-' ?></p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-on-surface-variant">Lokasi</p>
                    <p class="font-semibold text-on-surface"><?= e($keg['lokasi'] ?: '-') ?></p>
                </div>
            </div>
            <div>
                <p class="text-sm text-on-surface-variant mb-1">Deskripsi</p>
                <p class="text-sm text-on-surface leading-relaxed"><?= nl2br(e($keg['deskripsi'] ?: 'Tidak ada deskripsi.')) ?></p>
            </div>
            <?php if (can('kegiatan.manage', $org_id)): ?>
            <div class="pt-4 border-t border-outline-variant">
                <a href="<?= url('organisasi/' . $org_id . '/kegiatan') ?>?edit=<?= $keg_id ?>" class="btn-primary !h-9 !px-4 !text-sm">Edit Kegiatan</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
