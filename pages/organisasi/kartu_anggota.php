<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pdo = db();
$user_id = (int) $_SESSION['user_id'];
$org_id = (int) ($_GET['org_id'] ?? 0);

$org = $pdo->prepare("SELECT * FROM organisasi WHERE id=? AND deleted_at IS NULL LIMIT 1");
$org->execute([$org_id]); $org = $org->fetch();
if (!$org) { set_flash('error', 'Organisasi tidak ditemukan.'); redirect('/organisasi'); }

$membership = $pdo->prepare("SELECT uo.*, u.nama, u.nim, u.email, u.jurusan, u.foto_profile FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.user_id=? AND uo.organisasi_id=? AND uo.status='aktif' LIMIT 1");
$membership->execute([$user_id, $org_id]); $membership = $membership->fetch();
if (!$membership) { set_flash('error', 'Anda belum menjadi anggota organisasi ini.'); redirect('/organisasi/' . $org_id); }

$page_title = 'Kartu Anggota · ' . $org['nama'];
$current_page = 'org_kartu';
$current_org_id = $org_id;

$role_label = org_role_label($membership['role']);
$joined = $membership['created_at'] ? date('d M Y', strtotime($membership['created_at'])) : '-';
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-3xl mx-auto space-y-6">
        <a href="<?= url('organisasi/' . $org_id) ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            <?= e($org['nama']) ?>
        </a>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Kartu Anggota</h3>
                <button onclick="window.print()" class="btn-secondary !h-9 !px-4 !text-sm no-print">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m-6.06-3.71c.24.03.48.062.72.096m-.72-.096L11.34 18M3 12v6.75A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V12M3 12h18M3 12a9 9 0 0118 0"/></svg>
                    Cetak
                </button>
            </div>

            <div class="member-card">
                <div class="member-card-header">
                    <?php if ($org['logo']): ?>
                        <img src="<?= url($org['logo']) ?>" alt="" class="w-12 h-12 object-contain rounded-lg bg-white">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center text-primary font-bold text-lg"><?= e(mb_substr($org['singkatan'] ?: $org['nama'], 0, 2)) ?></div>
                    <?php endif; ?>
                    <div>
                        <p class="font-bold text-white text-lg leading-tight"><?= e($org['nama']) ?></p>
                        <p class="text-white/80 text-xs"><?= e($org['singkatan'] ?: '') ?></p>
                    </div>
                </div>
                <div class="member-card-body">
                    <div class="flex items-center gap-4">
                        <?php if ($membership['foto_profile']): ?>
                            <img src="<?= url($membership['foto_profile']) ?>" alt="" class="w-20 h-20 rounded-full object-cover border-2 border-outline-variant">
                        <?php else: ?>
                            <div class="w-20 h-20 rounded-full bg-surface-dim flex items-center justify-center text-on-surface-variant text-2xl font-bold border-2 border-outline-variant">
                                <?= e(mb_substr($membership['nama'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-on-surface-variant uppercase tracking-wider">Nama</p>
                            <p class="font-bold text-on-surface truncate"><?= e($membership['nama']) ?></p>
                            <p class="text-xs text-on-surface-variant uppercase tracking-wider mt-2">NIM</p>
                            <p class="font-semibold text-on-surface"><?= e($membership['nim'] ?: '-') ?></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-5">
                        <div>
                            <p class="text-xs text-on-surface-variant uppercase tracking-wider">Posisi</p>
                            <p class="font-semibold text-on-surface"><?= e($role_label) ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant uppercase tracking-wider">Status</p>
                            <p class="font-semibold text-green-700">Aktif</p>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant uppercase tracking-wider">Jurusan</p>
                            <p class="font-semibold text-on-surface"><?= e($membership['jurusan'] ?: '-') ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant uppercase tracking-wider">Bergabung</p>
                            <p class="font-semibold text-on-surface"><?= e($joined) ?></p>
                        </div>
                    </div>
                </div>
                <div class="member-card-footer">
                    <p class="text-white/80 text-xs">Kartu anggota digital ini sah sebagai identitas anggota <?= e($org['nama']) ?>.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
