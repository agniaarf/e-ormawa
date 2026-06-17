<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Mahasiswa');

$page_title = 'Kartu Anggota';
$current_page = 'ktm';
$pdo = db();
$user_id = $_SESSION['user_id'];

$org_id = (int) ($_GET['org'] ?? 0);
if (!$org_id) { redirect('/pages/mahasiswa/daftar_organisasi.php'); }

// Verify membership
$cek = $pdo->prepare("SELECT a.*, o.nama as org_nama, o.singkatan, u.nama, u.nim, u.jurusan, u.angkatan FROM anggota a JOIN organisasi o ON a.organisasi_id = o.id JOIN users u ON a.user_id = u.id WHERE a.user_id = ? AND a.organisasi_id = ? AND a.status = 'aktif' LIMIT 1");
$cek->execute([$user_id, $org_id]);
$ktm = $cek->fetch();

if (!$ktm) { set_flash('error', 'Anda belum resmi menjadi anggota organisasi ini.'); redirect('/pages/mahasiswa/daftar_organisasi.php'); }

$ktm['periode'] = date('Y');
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-lg mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-on-surface">Kartu Anggota Digital</h2>
            <button onclick="downloadKTM()" class="btn-primary flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Unduh PDF
            </button>
        </div>

        <!-- Card Container -->
        <div id="ktm-card" class="bg-gradient-to-br from-primary to-primary-light rounded-3xl p-6 text-white shadow-xl relative overflow-hidden" style="min-height: 320px;">
            <!-- Decorative circles -->
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-8 -left-8 w-28 h-28 rounded-full bg-white/10"></div>

            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl font-extrabold tracking-tight"><?= e($ktm['org_nama']) ?></h3>
                        <p class="text-sm text-white/80 mt-0.5"><?= e($ktm['singkatan']) ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                        <span class="font-extrabold text-sm">EO</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 my-6">
                    <div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center text-2xl font-extrabold border-2 border-white/30">
                        <?= e(strtoupper(substr($ktm['nama'], 0, 2))) ?>
                    </div>
                    <div>
                        <p class="text-xl font-bold"><?= e($ktm['nama']) ?></p>
                        <p class="text-sm text-white/80"><?= e($ktm['nim']) ?></p>
                        <p class="text-sm text-white/80"><?= e($ktm['jurusan']) ?> · Angkatan <?= e($ktm['angkatan']) ?></p>
                    </div>
                </div>

                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs text-white/70 uppercase tracking-wide">Jabatan</p>
                        <p class="text-sm font-semibold"><?= e($ktm['jabatan']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-white/70 uppercase tracking-wide">Periode</p>
                        <p class="text-sm font-semibold"><?= $ktm['periode'] ?></p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-white/20 flex items-center justify-between">
                    <p class="text-[10px] text-white/60">E-ORMAWA · e-ormawa.test</p>
                    <div class="h-6 w-24 bg-white/20 rounded flex items-center justify-center text-[10px] font-mono tracking-widest">ID: <?= $ktm['id'] ?>-<?= $org_id ?></div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function downloadKTM() {
    const card = document.getElementById('ktm-card');
    html2canvas(card, { scale: 2, useCORS: true }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({ orientation: 'landscape', unit: 'px', format: [canvas.width, canvas.height] });
        pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
        pdf.save('Kartu-Anggota-<?= e($ktm['singkatan']) ?>.pdf');
    });
}
</script>

<?php require __DIR__ . '/../../components/footer.php'; ?>
