<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_login();

$pdo = db();
$user_id = (int) $_SESSION['user_id'];

$page_title = 'Semua Kegiatan';
$current_page = 'kegiatan';

// Get all kegiatan with organization info
$kegiatan = $pdo->query("
    SELECT k.*, o.nama as org_nama, o.singkatan as org_singkatan, u.nama as creator_nama
    FROM kegiatan k
    JOIN organisasi o ON k.organisasi_id = o.id
    JOIN users u ON k.created_by = u.id
    WHERE k.deleted_at IS NULL
    ORDER BY k.created_at DESC
")->fetchAll();
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Semua Kegiatan</h1>
                <p class="text-sm text-on-surface-variant mt-1">Daftar semua kegiatan organisasi</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-low">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Nama Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Organisasi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <?php foreach ($kegiatan as $k): ?>
                        <tr class="hover:bg-surface-low transition-colors">
                            <td class="px-6 py-4">
                                <a href="<?= url('organisasi/' . $k['organisasi_id'] . '/kegiatan/' . $k['id']) ?>" class="font-semibold text-on-surface hover:text-primary">
                                    <?= e($k['nama']) ?>
                                </a>
                                <?php if ($k['deskripsi']): ?>
                                <p class="text-sm text-on-surface-variant mt-1 line-clamp-1"><?= e($k['deskripsi']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <a href="<?= url('organisasi/' . $k['organisasi_id']) ?>" class="text-sm text-on-surface hover:text-primary">
                                    <?= e($k['org_singkatan'] ?: $k['org_nama']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge bg-surface-low text-on-surface-variant text-xs capitalize"><?= e($k['tipe']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface">
                                <?= date('d M Y', strtotime($k['tanggal_mulai'])) ?>
                                <?php if ($k['tanggal_selesai']): ?>
                                <span class="text-on-surface-variant"> - <?= date('d M Y', strtotime($k['tanggal_selesai'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $status_colors = [
                                    'rencana' => 'bg-blue-100 text-blue-700',
                                    'berlangsung' => 'bg-green-100 text-green-700',
                                    'selesai' => 'bg-gray-100 text-gray-700',
                                    'dibatalkan' => 'bg-red-100 text-red-700'
                                ];
                                $color = $status_colors[$k['status']] ?? 'bg-gray-100 text-gray-700';
                                ?>
                                <span class="badge <?= $color ?> text-xs capitalize"><?= e($k['status']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface-variant">
                                <?= e($k['creator_nama']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($kegiatan)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">Belum ada kegiatan</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../components/footer.php'; ?>
