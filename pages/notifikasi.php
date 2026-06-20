<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_login();

$page_title = 'Notifikasi';
$current_page = 'notifikasi';
$pdo = db();
$user_id = (int) $_SESSION['user_id'];

if (isset($_GET['read'])) {
    if ($_GET['read'] === 'all') {
        $pdo->prepare("UPDATE notifikasi SET is_read=1 WHERE user_id=?")->execute([$user_id]);
    } else {
        $pdo->prepare("UPDATE notifikasi SET is_read=1 WHERE id=? AND user_id=?")->execute([(int)$_GET['read'], $user_id]);
    }
    redirect('/notifikasi');
}

$list = $pdo->prepare("SELECT * FROM notifikasi WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$list->execute([$user_id]); $list = $list->fetchAll();
?>
<?php require __DIR__ . '/../components/head.php'; ?>
<?php require __DIR__ . '/../components/sidebar.php'; ?>
<?php require __DIR__ . '/../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface">Notifikasi</h3>
                <?php if (!empty($list)): ?><a href="<?= url('notifikasi') ?>?read=all" class="text-sm font-medium text-primary hover:underline">Tandai Semua Dibaca</a><?php endif; ?>
            </div>
            <div class="divide-y divide-outline-variant">
                <?php foreach ($list as $n): ?>
                <div class="px-6 py-4 flex items-start gap-3 hover:bg-surface-low transition-colors <?= $n['is_read']?'':'bg-primary/5' ?>">
                    <div class="mt-0.5 w-2 h-2 rounded-full <?= $n['is_read']?'bg-outline-variant':'bg-primary' ?> flex-shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-on-surface"><?= e($n['judul']) ?></p>
                        <p class="text-sm text-on-surface-variant mt-0.5"><?= e($n['pesan']) ?></p>
                        <p class="text-xs text-on-surface-variant mt-1"><?= e(date('d M Y H:i', strtotime($n['created_at']))) ?></p>
                    </div>
                    <?php if (!$n['is_read']): ?><a href="?read=<?= $n['id'] ?>" class="text-xs font-medium text-primary hover:underline">Tandai Dibaca</a><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($list)): ?><div class="px-6 py-8 text-center text-on-surface-variant text-sm">Tidak ada notifikasi.</div><?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../components/footer.php'; ?>
