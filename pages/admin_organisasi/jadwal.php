<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Admin Organisasi');

$page_title = 'Jadwal';
$current_page = 'jadwal';
$pdo = db();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT o.* FROM organisasi o JOIN anggota a ON o.id = a.organisasi_id WHERE a.user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$my_org = $stmt->fetch();
$org_id = $my_org['id'] ?? 0;
if (!$org_id) { set_flash('error', 'Anda belum terhubung dengan organisasi.'); redirect('/pages/admin_organisasi/dashboard.php'); }

$year = (int) ($_GET['y'] ?? date('Y'));
$month = (int) ($_GET['m'] ?? date('n'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$startOfMonth = new DateTime("$year-$month-01");
$daysInMonth = (int) $startOfMonth->format('t');
$firstWeekday = (int) $startOfMonth->format('w');
$monthLabel = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$month-1];

// Kegiatan organisasi
$kg = $pdo->prepare("SELECT * FROM kegiatan WHERE organisasi_id = ?");
$kg->execute([$org_id]); $kg = $kg->fetchAll();
// Wawancara organisasi
$ww = $pdo->prepare("SELECT w.*, u.nama as peserta FROM wawancara w JOIN pendaftaran_organisasi po ON w.pendaftaran_id = po.id JOIN users u ON po.user_id = u.id WHERE po.organisasi_id = ?");
$ww->execute([$org_id]); $ww = $ww->fetchAll();

$events = [];
foreach ($kg as $k) {
    $d = (int) date('j', strtotime($k['tanggal_mulai']));
    $events[$d][] = ['type'=>'kegiatan','title'=>$k['nama'],'detail'=>$k['lokasi'],'time'=>date('H:i', strtotime($k['tanggal_mulai'])),'color'=>'bg-primary text-white'];
}
foreach ($ww as $w) {
    $d = (int) date('j', strtotime($w['jadwal']));
    $events[$d][] = ['type'=>'wawancara','title'=>'Wawancara','detail'=>$w['peserta'],'time'=>date('H:i', strtotime($w['jadwal'])),'color'=>'bg-accent text-on-surface'];
}

function isToday(int $y, int $m, int $d): bool {
    $today = new DateTime();
    return (int)$today->format('Y') === $y && (int)$today->format('n') === $m && (int)$today->format('j') === $d;
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Kalender <?= e($my_org['nama']) ?></h3>
                <div class="flex items-center gap-2">
                    <a href="?y=<?= $year ?>&m=<?= $month-1 ?>" class="p-2 rounded-lg hover:bg-surface-low text-on-surface"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></a>
                    <span class="text-sm font-semibold text-on-surface w-32 text-center"><?= $monthLabel ?> <?= $year ?></span>
                    <a href="?y=<?= $year ?>&m=<?= $month+1 ?>" class="p-2 rounded-lg hover:bg-surface-low text-on-surface"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg></a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-7 gap-2 mb-2 text-center">
                    <?php foreach (['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $d): ?>
                    <div class="text-xs font-semibold text-on-surface-variant uppercase"><?= $d ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="grid grid-cols-7 gap-2">
                    <?php for ($i=0; $i<$firstWeekday; $i++): ?><div></div><?php endfor; ?>
                    <?php for ($d=1; $d<=$daysInMonth; $d++): ?>
                    <div class="relative min-h-[80px] rounded-xl border border-outline-variant p-2 <?= isToday($year,$month,$d) ? 'bg-primary/5 border-primary' : 'bg-white' ?>">
                        <span class="text-sm font-semibold <?= isToday($year,$month,$d) ? 'text-primary' : 'text-on-surface' ?>"><?= $d ?></span>
                        <?php if (!empty($events[$d])): ?>
                        <div class="mt-1 space-y-1">
                            <?php foreach ($events[$d] as $e): ?>
                            <div class="text-[10px] leading-tight px-1.5 py-0.5 rounded <?= $e['color'] ?> truncate" title="<?= e($e['title']) ?>">
                                <?= e($e['time']) ?> <?= e($e['title']) ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 text-sm">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-primary"></span> Kegiatan</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-accent"></span> Wawancara</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant"><h3 class="font-bold text-on-surface">Kegiatan Bulan Ini</h3></div>
                <div class="divide-y divide-outline-variant max-h-80 overflow-y-auto">
                    <?php $found=0; foreach ($kg as $k): if ((int)date('n',strtotime($k['tanggal_mulai']))===$month && (int)date('Y',strtotime($k['tanggal_mulai']))===$year): $found++; ?>
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-surface-low">
                        <div>
                            <p class="text-sm font-semibold text-on-surface"><?= e($k['nama']) ?></p>
                            <p class="text-xs text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($k['tanggal_mulai']))) ?></p>
                        </div>
                        <span class="badge bg-primary/10 text-primary text-[10px]"><?= ucfirst($k['status']) ?></span>
                    </div>
                    <?php endif; endforeach; ?>
                    <?php if (!$found): ?><div class="px-6 py-6 text-center text-on-surface-variant text-sm">Tidak ada kegiatan</div><?php endif; ?>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant"><h3 class="font-bold text-on-surface">Wawancara Bulan Ini</h3></div>
                <div class="divide-y divide-outline-variant max-h-80 overflow-y-auto">
                    <?php $found=0; foreach ($ww as $w): if ((int)date('n',strtotime($w['jadwal']))===$month && (int)date('Y',strtotime($w['jadwal']))===$year): $found++; ?>
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-surface-low">
                        <div>
                            <p class="text-sm font-semibold text-on-surface">Wawancara <span class="text-on-surface-variant font-normal">— <?= e($w['peserta']) ?></span></p>
                            <p class="text-xs text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($w['jadwal']))) ?></p>
                        </div>
                        <span class="badge <?= $w['hasil']==='lulus'?'bg-green-100 text-green-700':($w['hasil']==='tidak_lulus'?'bg-red-100 text-red-700':'bg-yellow-100 text-yellow-700') ?> text-[10px]"><?= ucfirst($w['hasil']) ?></span>
                    </div>
                    <?php endif; endforeach; ?>
                    <?php if (!$found): ?><div class="px-6 py-6 text-center text-on-surface-variant text-sm">Tidak ada wawancara</div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
