<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Mahasiswa');

$page_title = 'Kegiatan';
$current_page = 'kegiatan';
$pdo = db();
$user_id = $_SESSION['user_id'];

// Daftar ke semua kegiatan organisasi yang user ikuti
$kegiatan = $pdo->prepare("SELECT k.*, o.nama as org_nama FROM kegiatan k JOIN organisasi o ON k.organisasi_id = o.id WHERE k.organisasi_id IN (SELECT organisasi_id FROM anggota WHERE user_id=? AND status='aktif') AND k.status IN ('rencana','berlangsung') ORDER BY k.tanggal_mulai DESC");
$kegiatan->execute([$user_id]); $kegiatan = $kegiatan->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); }
    else {
        $kegiatan_id = (int) ($_POST['kegiatan_id'] ?? 0);
        $pdo->prepare("INSERT INTO peserta_kegiatan (kegiatan_id, user_id, status) VALUES (?, ?, 'terdaftar') ON DUPLICATE KEY UPDATE status='terdaftar'")
            ->execute([$kegiatan_id, $user_id]);
        set_flash('success', 'Berhasil mendaftar kegiatan.');
    }
    redirect('/pages/mahasiswa/kegiatan.php');
}

$my_kegiatan_ids = [];
$m = $pdo->prepare("SELECT kegiatan_id FROM peserta_kegiatan WHERE user_id=?");
$m->execute([$user_id]);
foreach ($m->fetchAll() as $r) $my_kegiatan_ids[] = $r['kegiatan_id'];
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php foreach ($kegiatan as $k): ?>
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="badge bg-primary/10 text-primary text-xs"><?= e($k['org_nama']) ?></span>
                    <span class="badge <?= $k['status']==='berlangsung'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' ?>"><?= ucfirst($k['status']) ?></span>
                </div>
                <h4 class="font-bold text-on-surface mb-1"><?= e($k['nama']) ?></h4>
                <p class="text-sm text-on-surface-variant mb-3"><?= e($k['deskripsi'] ?: '-') ?></p>
                <div class="text-sm text-on-surface-variant space-y-1 mb-4">
                    <p class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg> <?= e(date('d M Y H:i', strtotime($k['tanggal_mulai']))) ?></p>
                    <p class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> <?= e($k['lokasi'] ?: '-') ?></p>
                </div>
                <?php if (in_array($k['id'], $my_kegiatan_ids)): ?>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold">Sudah Terdaftar</span>
                <?php else: ?>
                <form method="POST" action="" class="inline">
                    <?= csrf_input() ?>
                    <input type="hidden" name="kegiatan_id" value="<?= $k['id'] ?>">
                    <button type="submit" class="btn-primary !h-9 !px-3 !text-xs">Daftar Kegiatan</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (empty($kegiatan)): ?><div class="col-span-full text-center text-on-surface-variant py-12">Belum ada kegiatan tersedia.</div><?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
