<?php declare(strict_types=1);
$unread_count = unread_notif_count();
?>
<header class="sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-outline-variant">
    <div class="flex items-center justify-between px-6 py-3 lg:ml-[280px]">
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-surface-low text-on-surface">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
            <h1 class="text-lg font-bold text-on-surface"><?= e($page_title ?? 'Dashboard') ?></h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="<?= url('notifikasi') ?>" class="relative p-2 rounded-lg hover:bg-surface-low text-on-surface">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                <?php if ($unread_count > 0): ?>
                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-error rounded-full"></span>
                <?php endif; ?>
            </a>
            <div class="flex items-center gap-3 pl-4 border-l border-outline-variant">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-on-surface"><?= e($_SESSION['nama'] ?? 'User') ?></p>
                    <p class="text-xs text-on-surface-variant"><?= e($_SESSION['role'] ?? '') ?></p>
                </div>
                <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                    <?= e(strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 2))) ?>
                </div>
            </div>
        </div>
    </div>
</header>
