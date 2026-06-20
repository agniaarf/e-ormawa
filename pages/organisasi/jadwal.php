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

require_can('kegiatan.read', $org_id);

$page_title = 'Jadwal Kegiatan · ' . $org['nama'];
$current_page = 'org_jadwal';
$current_org_id = $org_id;

$year = (int) ($_GET['y'] ?? date('Y'));
$month = (int) ($_GET['m'] ?? date('n'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$monthStart = new DateTimeImmutable("$year-$month-01");
$daysInMonth = (int) $monthStart->format('t');
$firstDayOfWeek = (int) $monthStart->format('w');
$monthLabel = format_month_id($month) . ' ' . $year;

$prev = ['y' => ($month === 1 ? $year - 1 : $year), 'm' => ($month === 1 ? 12 : $month - 1)];
$next = ['y' => ($month === 12 ? $year + 1 : $year), 'm' => ($month === 12 ? 1 : $month + 1)];

$rangeStart = $monthStart->modify('first day of this month')->format('Y-m-d');
$rangeEnd = $monthStart->modify('last day of this month')->format('Y-m-d');

$stmt = $pdo->prepare("SELECT * FROM kegiatan WHERE organisasi_id=? AND deleted_at IS NULL AND tanggal_mulai BETWEEN ? AND ? ORDER BY tanggal_mulai ASC");
$stmt->execute([$org_id, $rangeStart, $rangeEnd]);
$events = $stmt->fetchAll();

$eventsByDate = [];
foreach ($events as $e) {
    $key = (new DateTimeImmutable($e['tanggal_mulai']))->format('Y-m-d');
    if ($key >= $rangeStart && $key <= $rangeEnd) {
        $eventsByDate[$key][] = $e;
    }
}

function statusColor(string $status): string {
    return match ($status) {
        'berlangsung' => 'bg-green-100 text-green-700 border-green-200',
        'selesai' => 'bg-surface-dim text-on-surface-variant border-outline-variant',
        'batal' => 'bg-red-100 text-red-700 border-red-200',
        default => 'bg-blue-100 text-blue-700 border-blue-200',
    };
}

function format_month_id(int $month): string {
    return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$month - 1];
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-5xl mx-auto space-y-6">
        <a href="<?= url('organisasi/' . $org_id) ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            <?= e($org['nama']) ?>
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-on-surface">Jadwal Kegiatan</h1>
            <div class="flex items-center gap-2">
                <a href="<?= url('organisasi/' . $org_id . '/jadwal?y=' . $prev['y'] . '&m=' . $prev['m']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-outline-variant hover:bg-surface-low text-on-surface">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </a>
                <span class="min-w-[140px] text-center font-semibold text-on-surface"><?= e($monthLabel) ?></span>
                <a href="<?= url('organisasi/' . $org_id . '/jadwal?y=' . $next['y'] . '&m=' . $next['m']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-outline-variant hover:bg-surface-low text-on-surface">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                <a href="<?= url('organisasi/' . $org_id . '/jadwal') ?>" class="ml-2 px-3 py-2 rounded-lg border border-outline-variant text-sm font-medium text-on-surface-variant hover:bg-surface-low">Hari Ini</a>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="grid grid-cols-7 bg-surface border-b border-outline-variant">
                <?php foreach (['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $d): ?>
                <div class="py-2 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider"><?= e($d) ?></div>
                <?php endforeach; ?>
            </div>
            <div class="grid grid-cols-7 auto-rows-fr">
                <?php for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
                    <div class="min-h-[100px] border-b border-r border-outline-variant bg-surface-low/50"></div>
                <?php endfor; ?>

                <?php for ($day = 1; $day <= $daysInMonth; $day++):
                    $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $isToday = $dateKey === date('Y-m-d');
                    $dayEvents = $eventsByDate[$dateKey] ?? [];
                ?>
                <div class="min-h-[100px] border-b border-r border-outline-variant p-2 relative hover:bg-surface-low/30 transition-colors <?= $isToday ? 'bg-primary/5' : '' ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold w-7 h-7 flex items-center justify-center rounded-full <?= $isToday ? 'bg-primary text-white' : 'text-on-surface' ?>"><?= $day ?></span>
                        <?php if (!empty($dayEvents)): ?>
                            <span class="text-[10px] font-semibold text-primary"><?= count($dayEvents) ?> kegiatan</span>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-1">
                        <?php foreach (array_slice($dayEvents, 0, 3) as $e): ?>
                        <button type="button"
                            onclick="openEventModal(<?= e(json_encode($e)) ?>)"
                            class="block w-full text-left text-[10px] px-2 py-1 rounded border truncate <?= statusColor($e['status']) ?>"
                            title="<?= e($e['nama']) ?>">
                            <?= e($e['nama']) ?>
                        </button>
                        <?php endforeach; ?>
                        <?php if (count($dayEvents) > 3): ?>
                        <span class="text-[10px] text-on-surface-variant px-1">+<?= count($dayEvents) - 3 ?> lainnya</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6">
            <h3 class="font-bold text-on-surface mb-4">Kegiatan Bulan Ini</h3>
            <?php if (empty($events)): ?>
                <p class="text-sm text-on-surface-variant">Tidak ada kegiatan pada bulan ini.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($events as $e): ?>
                <div class="flex items-start gap-3 p-3 rounded-xl border border-outline-variant hover:bg-surface-low transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                        <?= e((new DateTimeImmutable($e['tanggal_mulai']))->format('d')) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" onclick="openEventModal(<?= e(json_encode($e)) ?>)" class="font-semibold text-on-surface hover:text-primary hover:underline truncate text-left"><?= e($e['nama']) ?></button>
                            <span class="badge text-[10px] px-2 py-0.5 <?= statusColor($e['status']) ?>"><?= e(ucfirst($e['status'])) ?></span>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            <?= e((new DateTimeImmutable($e['tanggal_mulai']))->format('d M Y')) ?>
                            <?php if ($e['tanggal_selesai']): ?> - <?= e((new DateTimeImmutable($e['tanggal_selesai']))->format('d M Y')) ?><?php endif; ?>
                            <?php if ($e['lokasi']): ?> · <?= e($e['lokasi']) ?><?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div id="eventModal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEventModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-lg" id="eventModalTitle">Detail Kegiatan</h3>
                <button type="button" onclick="closeEventModal()" class="p-1.5 rounded-lg hover:bg-surface-low text-on-surface-variant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4" id="eventModalContent">
            </div>
        </div>
    </div>
</div>

<script>
function openEventModal(event) {
    const fmt = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
    document.getElementById('eventModalTitle').textContent = event.nama || 'Detail Kegiatan';
    document.getElementById('eventModalContent').innerHTML = `
        <div class="space-y-3 text-sm">
            <div class="flex items-center gap-2">
                <span class="badge px-2.5 py-1 text-xs ${statusClass(event.status)}">${event.status ? event.status.charAt(0).toUpperCase() + event.status.slice(1) : '-'}</span>
                <span class="text-xs text-on-surface-variant">${event.tipe ? event.tipe.toUpperCase() : '-'}</span>
            </div>
            <div>
                <p class="text-xs text-on-surface-variant uppercase tracking-wider">Tanggal Mulai</p>
                <p class="font-semibold text-on-surface">${fmt(event.tanggal_mulai)}</p>
            </div>
            <div>
                <p class="text-xs text-on-surface-variant uppercase tracking-wider">Tanggal Selesai</p>
                <p class="font-semibold text-on-surface">${fmt(event.tanggal_selesai)}</p>
            </div>
            <div>
                <p class="text-xs text-on-surface-variant uppercase tracking-wider">Lokasi</p>
                <p class="font-semibold text-on-surface">${event.lokasi || '-'}</p>
            </div>
            <div>
                <p class="text-xs text-on-surface-variant uppercase tracking-wider">Deskripsi</p>
                <p class="text-on-surface">${event.deskripsi || 'Tidak ada deskripsi.'}</p>
            </div>
        </div>
    `;
    document.getElementById('eventModal').classList.remove('hidden');
}
function closeEventModal() {
    document.getElementById('eventModal').classList.add('hidden');
}
function statusClass(status) {
    return {
        'berlangsung': 'bg-green-100 text-green-700 border-green-200',
        'selesai': 'bg-surface-dim text-on-surface-variant border-outline-variant',
        'batal': 'bg-red-100 text-red-700 border-red-200',
        'rencana': 'bg-blue-100 text-blue-700 border-blue-200'
    }[status] || 'bg-blue-100 text-blue-700 border-blue-200';
}
</script>

<?php require __DIR__ . '/../../components/footer.php'; ?>
