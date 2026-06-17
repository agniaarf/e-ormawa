<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();
require_role('Mahasiswa');

$page_title = 'Daftar Organisasi';
$current_page = 'daftar_org';
$pdo = db();
$user_id = $_SESSION['user_id'];

$org_id = (int) ($_GET['id'] ?? 0);
$org = null;
$my_status = null;

$ktm_data = null;
if ($org_id) {
    $org = $pdo->prepare("SELECT * FROM organisasi WHERE id=? AND status='aktif' LIMIT 1");
    $org->execute([$org_id]); $org = $org->fetch();
    if ($org) {
        $ms = $pdo->prepare("SELECT status FROM pendaftaran_organisasi WHERE user_id=? AND organisasi_id=? ORDER BY id DESC LIMIT 1");
        $ms->execute([$user_id, $org_id]); $my_status = $ms->fetchColumn();
        if ($my_status === 'diterima') {
            $cek = $pdo->prepare("SELECT a.*, o.nama as org_nama, o.singkatan, u.nama, u.nim, u.jurusan, u.angkatan FROM anggota a JOIN organisasi o ON a.organisasi_id = o.id JOIN users u ON a.user_id = u.id WHERE a.user_id = ? AND a.organisasi_id = ? AND a.status = 'aktif' LIMIT 1");
            $cek->execute([$user_id, $org_id]);
            $ktm_data = $cek->fetch();
            if ($ktm_data) $ktm_data['periode'] = date('Y');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $org && !$my_status) {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); }
    else {
        $motivasi = trim($_POST['motivasi'] ?? '');
        $pdo->prepare("INSERT INTO pendaftaran_organisasi (user_id, organisasi_id, motivasi, status, created_at) VALUES (?, ?, ?, 'menunggu', NOW())")
            ->execute([$user_id, $org_id, $motivasi]);
        $admins = $pdo->prepare("SELECT user_id FROM anggota WHERE organisasi_id = ?");
        $admins->execute([$org_id]);
        foreach ($admins->fetchAll() as $a) {
            send_notification((int)$a['user_id'], 'Pendaftaran Baru', ($_SESSION['nama'] ?? 'Seseorang') . ' mendaftar ke ' . $org['nama'], 'info');
        }
        set_flash('success', 'Pendaftaran berhasil dikirim. Menunggu verifikasi.');
    }
    redirect('/pages/mahasiswa/daftar_organisasi.php?id=' . $org_id);
}

// List all registrations + active memberships for this user (deduplicated)
$registrations = $pdo->prepare(
    "SELECT o.id, o.nama, o.singkatan, o.deskripsi, po.status, po.created_at
     FROM pendaftaran_organisasi po
     JOIN organisasi o ON po.organisasi_id = o.id
     WHERE po.user_id = ?
     UNION
     SELECT o.id, o.nama, o.singkatan, o.deskripsi, 'diterima' as status, a.created_at
     FROM anggota a
     JOIN organisasi o ON a.organisasi_id = o.id
     WHERE a.user_id = ? AND a.status = 'aktif'
       AND NOT EXISTS (
           SELECT 1 FROM pendaftaran_organisasi po2
           WHERE po2.user_id = ? AND po2.organisasi_id = a.organisasi_id
       )
     ORDER BY created_at DESC"
);
$registrations->execute([$user_id, $user_id, $user_id]);
$my_orgs = $registrations->fetchAll();

function statusBadgeClass(string $status): string {
    return match($status) {
        'menunggu' => 'bg-yellow-100 text-yellow-700',
        'wawancara' => 'bg-purple-100 text-purple-700',
        'diterima' => 'bg-green-100 text-green-700',
        'ditolak' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-600',
    };
}
function statusText(string $status): string {
    return match($status) {
        'menunggu' => 'Pendaftaran sedang direview oleh admin organisasi.',
        'wawancara' => 'Silakan cek jadwal wawancara Anda.',
        'diterima' => 'Selamat! Anda telah diterima sebagai anggota.',
        'ditolak' => 'Maaf, pendaftaran Anda tidak dapat diterima.',
        default => '',
    };
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-4xl mx-auto space-y-6">

        <?php if ($org_id && $org): ?>
        <!-- Detail View -->
        <a href="<?= BASE_URL ?>/pages/mahasiswa/daftar_organisasi.php" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar
        </a>
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-extrabold text-lg">
                    <?= e(strtoupper(substr($org['nama'], 0, 2))) ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-on-surface"><?= e($org['nama']) ?></h2>
                    <p class="text-sm text-on-surface-variant"><?= e($org['singkatan'] ?? '') ?></p>
                </div>
            </div>
            <div class="space-y-3 text-sm text-on-surface-variant">
                <p><strong class="text-on-surface">Deskripsi:</strong> <?= e($org['deskripsi'] ?: '-') ?></p>
                <p><strong class="text-on-surface">Visi:</strong> <?= e($org['visi'] ?: '-') ?></p>
                <p><strong class="text-on-surface">Misi:</strong> <?= e($org['misi'] ?: '-') ?></p>
            </div>
        </div>

        <?php if ($my_status): ?>
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6 text-center space-y-3">
            <p class="text-on-surface font-semibold">Status Pendaftaran Anda</p>
            <span class="badge <?= statusBadgeClass($my_status) ?>"><?= ucfirst($my_status) ?></span>
            <p class="text-xs text-on-surface-variant"><?= statusText($my_status) ?></p>
        </div>

        <?php if ($my_status === 'diterima' && $ktm_data): ?>
        <!-- Inline KTM Card -->
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Kartu Anggota Digital</h3>
                <button onclick="downloadKTM()" class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary-light">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Unduh PDF
                </button>
            </div>
            <div id="ktm-card" class="bg-gradient-to-br from-primary to-primary-light rounded-3xl p-6 text-white shadow-xl relative overflow-hidden" style="min-height: 280px;">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-8 -left-8 w-28 h-28 rounded-full bg-white/10"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-xl font-extrabold tracking-tight"><?= e($ktm_data['org_nama']) ?></h3>
                            <p class="text-xs text-white/80 mt-0.5"><?= e($ktm_data['singkatan']) ?></p>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-xs font-extrabold">EO</div>
                    </div>
                    <div class="flex items-center gap-4 my-5">
                        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-xl font-extrabold border-2 border-white/30">
                            <?= e(strtoupper(substr($ktm_data['nama'], 0, 2))) ?>
                        </div>
                        <div>
                            <p class="text-lg font-bold"><?= e($ktm_data['nama']) ?></p>
                            <p class="text-xs text-white/80"><?= e($ktm_data['nim']) ?></p>
                            <p class="text-xs text-white/80"><?= e($ktm_data['jurusan']) ?> · Angkatan <?= e($ktm_data['angkatan']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-end justify-between">
                        <div><p class="text-[10px] text-white/70 uppercase tracking-wide">Jabatan</p><p class="text-xs font-semibold"><?= e($ktm_data['jabatan']) ?></p></div>
                        <div class="text-right"><p class="text-[10px] text-white/70 uppercase tracking-wide">Periode</p><p class="text-xs font-semibold"><?= $ktm_data['periode'] ?></p></div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-white/20 flex items-center justify-between">
                        <p class="text-[10px] text-white/60">E-ORMAWA · e-ormawa.test</p>
                        <div class="h-5 w-20 bg-white/20 rounded flex items-center justify-center text-[9px] font-mono tracking-widest">ID: <?= $ktm_data['id'] ?>-<?= $org_id ?></div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function downloadKTM() {
            const card = document.getElementById('ktm-card');
            html2canvas(card, { scale: 2, useCORS: true }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({ orientation: 'landscape', unit: 'px', format: [canvas.width, canvas.height] });
                pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
                pdf.save('Kartu-Anggota-<?= e($ktm_data['singkatan']) ?>.pdf');
            });
        }
        </script>
        <?php endif; ?>
        <?php else: ?>
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant"><h3 class="font-bold text-on-surface">Formulir Pendaftaran</h3></div>
            <form method="POST" action="" class="p-6 space-y-4">
                <?= csrf_input() ?>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1.5">Motivasi Bergabung</label>
                    <textarea name="motivasi" rows="4" required class="form-input py-2" placeholder="Ceritakan motivasi Anda bergabung..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Kirim Pendaftaran</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- List View -->
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-bold text-on-surface">Organisasi Saya</h2>
            <a href="<?= BASE_URL ?>/pages/mahasiswa/jelajah.php" class="text-sm text-primary font-semibold hover:underline">+ Daftar Organisasi Baru</a>
        </div>

        <?php if (empty($my_orgs)): ?>
        <div class="bg-white rounded-2xl border border-outline-variant p-8 text-center shadow-card">
            <p class="text-on-surface font-medium">Anda belum mendaftar ke organisasi mana pun.</p>
            <p class="text-sm text-on-surface-variant mt-1">Jelajahi dan daftar ke organisasi yang menarik minat Anda.</p>
            <a href="<?= BASE_URL ?>/pages/mahasiswa/jelajah.php" class="btn-primary mt-4 inline-block">Jelajah Organisasi</a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 gap-4">
            <?php foreach ($my_orgs as $row): ?>
            <div class="bg-white rounded-2xl border border-outline-variant shadow-card p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-extrabold text-base shrink-0">
                        <?= e(strtoupper(substr($row['nama'], 0, 2))) ?>
                    </div>
                    <div>
                        <h3 class="font-semibold text-on-surface"><?= e($row['nama']) ?></h3>
                        <p class="text-xs text-on-surface-variant"><?= e($row['singkatan'] ?: '-') ?> · <?= e(date('d M Y', strtotime($row['created_at']))) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge <?= statusBadgeClass($row['status']) ?> text-xs"><?= ucfirst($row['status']) ?></span>
                    <?php if ($row['status'] === 'diterima'): ?>
                    <a href="?id=<?= $row['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary-light">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                        Kartu Anggota
                    </a>
                    <?php else: ?>
                    <a href="?id=<?= $row['id'] ?>" class="text-xs text-primary font-semibold hover:underline">Lihat Detail</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
