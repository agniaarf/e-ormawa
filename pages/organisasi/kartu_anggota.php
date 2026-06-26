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
<style>
@media print {
    @page { margin: 20mm; }
    #sidebar, #sidebar-overlay, header, .no-print { display: none; }
    main { margin: 0; padding: 20px; margin-left: 0; }
    .max-w-3xl { max-width: 100%; }
    .member-card { border: 2px solid #244539; max-width: 450px; margin: 0 auto; }
    .member-card-header, .member-card-footer { background: #244539; color: white; }
}
</style>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-3xl mx-auto space-y-6">
        <a href="<?= url('organisasi/' . $org_id) ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline no-print">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            <?= e($org['nama']) ?>
        </a>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6 space-y-6 no-print-container">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Kartu Anggota</h3>
                <div class="flex items-center gap-2 no-print">
                    <button onclick="downloadAsImage()" class="btn-secondary !h-9 !px-4 !text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        Foto
                    </button>
                    <button onclick="downloadAsPDF()" class="btn-secondary !h-9 !px-4 !text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        PDF
                    </button>
                </div>
            </div>

            <div id="memberCard" class="member-card">
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

<script>
function downloadAsImage() {
    const card = document.getElementById('memberCard');
    html2canvas(card, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'kartu-anggota-<?= e($org['singkatan'] ?: 'org') ?>-<?= e($membership['nim']) ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}

function downloadAsPDF() {
    const card = document.getElementById('memberCard');
    html2canvas(card, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true
    }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgWidth = 85;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        const x = (210 - imgWidth) / 2;
        const y = 20;
        pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
        pdf.save('kartu-anggota-<?= e($org['singkatan'] ?: 'org') ?>-<?= e($membership['nim']) ?>.pdf');
    });
}
</script>

<?php require __DIR__ . '/../../components/footer.php'; ?>
