<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Super Admin');

$page_title = 'Laporan';
$current_page = 'laporan';
$pdo = db();

$total_organisasi = $pdo->query("SELECT COUNT(*) FROM organisasi")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_mahasiswa = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id=3")->fetchColumn();
$total_kegiatan = $pdo->query("SELECT COUNT(*) FROM kegiatan")->fetchColumn();
$total_pendaftar = $pdo->query("SELECT COUNT(*) FROM pendaftaran_organisasi")->fetchColumn();

$org_stats = $pdo->query("SELECT o.nama, COUNT(a.id) as jumlah FROM organisasi o LEFT JOIN anggota a ON o.id=a.organisasi_id AND a.status='aktif' GROUP BY o.id ORDER BY jumlah DESC LIMIT 10")->fetchAll();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-5 shadow-card">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_organisasi ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Organisasi</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-5 shadow-card">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_users ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Pengguna</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-5 shadow-card">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_mahasiswa ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Mahasiswa</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-5 shadow-card">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_kegiatan ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Kegiatan</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-5 shadow-card">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_pendaftar ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Pendaftar</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card">
                <h3 class="font-bold text-on-surface mb-4">Anggota per Organisasi</h3>
                <canvas id="chartOrg" height="200"></canvas>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card">
                <h3 class="font-bold text-on-surface mb-4">Statistik Cepat</h3>
                <div class="space-y-3">
                    <?php foreach ($org_stats as $s): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-on-surface"><?= e($s['nama']) ?></span>
                        <span class="text-sm font-bold text-primary"><?= $s['jumlah'] ?> anggota</span>
                    </div>
                    <div class="w-full bg-surface-low rounded-full h-2">
                        <div class="bg-primary h-2 rounded-full" style="width: <?= min(100, ($s['jumlah']/max(1,$total_users))*100) ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($org_stats)): ?><p class="text-sm text-on-surface-variant">Belum ada data.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const ctx = document.getElementById('chartOrg').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($x)=>$x['nama'], $org_stats)) ?>,
        datasets: [{
            label: 'Jumlah Anggota',
            data: <?= json_encode(array_map(fn($x)=>$x['jumlah'], $org_stats)) ?>,
            backgroundColor: '#3B5D50',
            borderRadius: 6
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision:0 } } } }
});
</script>

<?php require __DIR__ . '/../../components/footer.php'; ?>
