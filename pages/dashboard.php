<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_login();

$page_title = 'Dashboard';
$current_page = 'dashboard';
$pdo = db();
$user_id = (int) $_SESSION['user_id'];

if (is_super_admin()) {
    $total_organisasi = $pdo->query("SELECT COUNT(*) FROM organisasi WHERE status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $total_mahasiswa = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id=3 AND status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $total_kegiatan = $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE status='berlangsung' AND deleted_at IS NULL")->fetchColumn();
    $total_pendaftaran = $pdo->query("SELECT COUNT(*) FROM pendaftaran_organisasi WHERE status='menunggu'")->fetchColumn();
    $total_pengumuman = $pdo->query("SELECT COUNT(*) FROM pengumuman")->fetchColumn();
    $total_kegiatan_selesai = $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE status='selesai' AND deleted_at IS NULL")->fetchColumn();
    $recent_organisasi = $pdo->query("SELECT * FROM organisasi WHERE status='aktif' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $kegiatan_by_type = $pdo->query("SELECT tipe, COUNT(*) as total FROM kegiatan WHERE deleted_at IS NULL GROUP BY tipe")->fetchAll();
    $recent_pendaftaran = $pdo->query("SELECT po.*, u.nama as user_nama, o.nama as org_nama FROM pendaftaran_organisasi po JOIN users u ON po.user_id = u.id JOIN organisasi o ON po.organisasi_id = o.id WHERE po.status='menunggu' ORDER BY po.created_at DESC LIMIT 5")->fetchAll();
    $kegiatan_by_status = $pdo->query("SELECT status, COUNT(*) as total FROM kegiatan WHERE deleted_at IS NULL GROUP BY status")->fetchAll();
    $top_organisasi = $pdo->query("SELECT o.*, COUNT(k.id) as kegiatan_count FROM organisasi o LEFT JOIN kegiatan k ON o.id = k.organisasi_id AND k.deleted_at IS NULL WHERE o.status='aktif' AND o.deleted_at IS NULL GROUP BY o.id ORDER BY kegiatan_count DESC LIMIT 5")->fetchAll();
    $recent_kegiatan = $pdo->query("SELECT k.*, o.nama as org_nama FROM kegiatan k JOIN organisasi o ON k.organisasi_id = o.id WHERE k.deleted_at IS NULL ORDER BY k.created_at DESC LIMIT 5")->fetchAll();
    $pendaftaran_by_status = $pdo->query("SELECT status, COUNT(*) as total FROM pendaftaran_organisasi GROUP BY status")->fetchAll();
} else {
    $orgs = my_organisasi($user_id);
    $total_organisasi = $pdo->query("SELECT COUNT(*) FROM organisasi WHERE status='aktif' AND deleted_at IS NULL")->fetchColumn();
    $my_org_count = count($orgs);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM permintaan_bergabung WHERE user_id=? AND status IN ('menunggu','administrasi','wawancara')");
    $stmt->execute([$user_id]); $pending_req = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT p.*, o.nama AS org_nama FROM pengumuman p LEFT JOIN organisasi o ON p.organisasi_id=o.id WHERE p.tipe='global' OR p.organisasi_id IN (SELECT organisasi_id FROM user_organisasi WHERE user_id=? AND status='aktif') ORDER BY p.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]); $pengumuman = $stmt->fetchAll();
}
?>
<?php require __DIR__ . '/../components/head.php'; ?>
<?php require __DIR__ . '/../components/sidebar.php'; ?>
<?php require __DIR__ . '/../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
    <?php if (is_super_admin()): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_organisasi ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Organisasi</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_users ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Pengguna</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_mahasiswa ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Mahasiswa</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_kegiatan ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Kegiatan Aktif</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-primary"><?= $total_pendaftaran ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Pendaftaran Menunggu</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_pengumuman ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Total Pengumuman</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_kegiatan_selesai ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Kegiatan Selesai</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_users - $total_mahasiswa ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Admin Organisasi</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant">
                    <h3 class="font-bold text-on-surface">Status Kegiatan</h3>
                </div>
                <div class="p-6">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant">
                    <h3 class="font-bold text-on-surface">Status Pendaftaran</h3>
                </div>
                <div class="p-6">
                    <canvas id="pendaftaranChart" height="250"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant">
                    <h3 class="font-bold text-on-surface">Kegiatan Berdasarkan Tipe</h3>
                </div>
                <div class="p-6">
                    <canvas id="kegiatanChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant">
                <h3 class="font-bold text-on-surface">Top 5 Organisasi Teraktif</h3>
            </div>
            <div class="p-6">
                <canvas id="topOrgChart" height="200"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-bold text-on-surface">Organisasi Terbaru</h3>
                    <a href="<?= url('organisasi') ?>" class="text-sm font-medium text-primary hover:underline">Lihat Semua</a>
                </div>
                <div class="divide-y divide-outline-variant">
                    <?php foreach ($recent_organisasi as $org): ?>
                    <a href="<?= url('organisasi/' . $org['id']) ?>" class="flex items-center justify-between px-6 py-4 hover:bg-surface-low transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm"><?= e(strtoupper(substr($org['nama'],0,2))) ?></div>
                            <div>
                                <p class="font-semibold text-sm text-on-surface"><?= e($org['nama']) ?></p>
                                <p class="text-xs text-on-surface-variant"><?= e($org['singkatan'] ?: '-') ?> &middot; <?= e(date('d M Y', strtotime($org['created_at']))) ?></p>
                            </div>
                        </div>
                        <span class="badge bg-green-100 text-green-700 text-xs">Aktif</span>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($recent_organisasi)): ?>
                    <div class="px-6 py-8 text-center text-on-surface-variant text-sm">Belum ada organisasi</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-bold text-on-surface">Pendaftaran Menunggu</h3>
                    <span class="badge bg-amber-100 text-amber-700 text-xs"><?= $total_pendaftaran ?> Menunggu</span>
                </div>
                <div class="divide-y divide-outline-variant">
                    <?php foreach ($recent_pendaftaran as $p): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-sm text-on-surface"><?= e($p['user_nama']) ?></p>
                                <p class="text-xs text-on-surface-variant mt-0.5">Mendaftar ke <?= e($p['org_nama']) ?></p>
                                <p class="text-xs text-on-surface-variant mt-0.5"><?= e(date('d M Y H:i', strtotime($p['created_at']))) ?></p>
                            </div>
                            <span class="badge bg-amber-100 text-amber-700 text-xs">Menunggu</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($recent_pendaftaran)): ?>
                    <div class="px-6 py-8 text-center text-on-surface-variant text-sm">Tidak ada pendaftaran menunggu</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-bold text-on-surface">Kegiatan Terbaru</h3>
                    <a href="<?= url('kegiatan') ?>" class="text-sm font-medium text-primary hover:underline">Lihat Semua</a>
                </div>
                <div class="divide-y divide-outline-variant">
                    <?php foreach ($recent_kegiatan as $k): ?>
                    <a href="<?= url('kegiatan/' . $k['id']) ?>" class="block px-6 py-4 hover:bg-surface-low transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center font-extrabold text-sm"><?= e(strtoupper(substr($k['nama'],0,2))) ?></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-on-surface truncate"><?= e($k['nama']) ?></p>
                                <p class="text-xs text-on-surface-variant mt-0.5"><?= e($k['org_nama']) ?></p>
                                <p class="text-xs text-on-surface-variant mt-0.5"><?= e(date('d M Y', strtotime($k['tanggal_mulai']))) ?></p>
                            </div>
                            <span class="badge bg-primary/10 text-primary text-xs capitalize"><?= e($k['status']) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($recent_kegiatan)): ?>
                    <div class="px-6 py-8 text-center text-on-surface-variant text-sm">Belum ada kegiatan</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $total_organisasi ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Organisasi Tersedia</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $my_org_count ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Organisasi Saya</p>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-card card-hover">
                <p class="text-2xl font-extrabold text-on-surface"><?= $pending_req ?></p>
                <p class="text-sm text-on-surface-variant mt-1">Permintaan Menunggu</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Organisasi Saya</h3>
                <a href="<?= url('organisasi') ?>" class="text-sm font-medium text-primary hover:underline">Jelajah Organisasi</a>
            </div>
            <div class="divide-y divide-outline-variant">
                <?php foreach ($orgs as $o): ?>
                <a href="<?= url('organisasi/' . $o['id']) ?>" class="flex items-center justify-between px-6 py-4 hover:bg-surface-low transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm"><?= e(strtoupper(substr($o['nama'],0,2))) ?></div>
                        <div>
                            <p class="font-semibold text-sm text-on-surface"><?= e($o['nama']) ?></p>
                            <p class="text-xs text-on-surface-variant"><?= e($o['singkatan'] ?: '-') ?></p>
                        </div>
                    </div>
                    <span class="badge bg-primary/10 text-primary text-xs"><?= e(org_role_label($o['my_role'])) ?></span>
                </a>
                <?php endforeach; ?>
                <?php if (empty($orgs)): ?>
                <div class="px-6 py-8 text-center text-on-surface-variant text-sm">Anda belum bergabung dengan organisasi mana pun. <a href="<?= url('organisasi') ?>" class="text-primary font-semibold hover:underline">Jelajahi sekarang</a>.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant"><h3 class="font-bold text-on-surface">Pengumuman Terbaru</h3></div>
            <div class="divide-y divide-outline-variant">
                <?php foreach ($pengumuman as $p): ?>
                <div class="px-6 py-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 w-2 h-2 rounded-full bg-primary flex-shrink-0"></div>
                        <div>
                            <p class="font-semibold text-sm text-on-surface"><?= e($p['judul']) ?></p>
                            <p class="text-xs text-on-surface-variant mt-0.5"><?= e($p['org_nama'] ?? 'Umum') ?> &middot; <?= e(date('d M Y', strtotime($p['created_at']))) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($pengumuman)): ?><div class="px-6 py-8 text-center text-on-surface-variant text-sm">Belum ada pengumuman</div><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    </div>
</main>

<?php if (is_super_admin()): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kegiatan Status Chart (Doughnut)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = <?= json_encode(array_map('ucfirst', array_column($kegiatan_by_status, 'status'))) ?>;
    const statusData = <?= json_encode(array_column($kegiatan_by_status, 'total')) ?>;

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: ['#244539', '#3B5D50', '#D4B483', '#ba1a1a', '#717974'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        usePointStyle: true,
                        font: { size: 10 }
                    }
                }
            }
        }
    });

    // Pendaftaran Status Chart (Pie)
    const pendaftaranCtx = document.getElementById('pendaftaranChart').getContext('2d');
    const pendaftaranLabels = <?= json_encode(array_map('ucfirst', array_column($pendaftaran_by_status, 'status'))) ?>;
    const pendaftaranData = <?= json_encode(array_column($pendaftaran_by_status, 'total')) ?>;

    new Chart(pendaftaranCtx, {
        type: 'pie',
        data: {
            labels: pendaftaranLabels,
            datasets: [{
                data: pendaftaranData,
                backgroundColor: ['#D4B483', '#244539', '#3B5D50', '#ba1a1a'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        usePointStyle: true,
                        font: { size: 10 }
                    }
                }
            }
        }
    });

    // Kegiatan by Type Chart (Bar)
    const kegiatanCtx = document.getElementById('kegiatanChart').getContext('2d');
    const kegiatanLabels = <?= json_encode(array_map('ucfirst', array_column($kegiatan_by_type, 'tipe'))) ?>;
    const kegiatanData = <?= json_encode(array_column($kegiatan_by_type, 'total')) ?>;

    new Chart(kegiatanCtx, {
        type: 'bar',
        data: {
            labels: kegiatanLabels,
            datasets: [{
                label: 'Jumlah Kegiatan',
                data: kegiatanData,
                backgroundColor: '#244539',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Top Organizations Chart (Horizontal Bar)
    const topOrgCtx = document.getElementById('topOrgChart').getContext('2d');
    const topOrgLabels = <?= json_encode(array_column($top_organisasi, 'nama')) ?>;
    const topOrgData = <?= json_encode(array_column($top_organisasi, 'kegiatan_count')) ?>;

    new Chart(topOrgCtx, {
        type: 'bar',
        data: {
            labels: topOrgLabels,
            datasets: [{
                label: 'Jumlah Kegiatan',
                data: topOrgData,
                backgroundColor: ['#244539', '#3B5D50', '#D4B483', '#4E7A68', '#3b6756'],
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../components/footer.php'; ?>
