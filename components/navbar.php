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
                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center animate-pop">
                    <?= e((string) min($unread_count, 99)) ?>
                </span>
                <?php endif; ?>
            </a>
            <div class="relative" id="profile-dropdown">
                <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-3 pl-4 pr-2 py-1.5 rounded-lg border-l border-outline-variant cursor-pointer hover:bg-surface-low transition-colors">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-on-surface"><?= e($_SESSION['nama'] ?? 'User') ?></p>
                        <p class="text-xs text-on-surface-variant"><?= e($_SESSION['role'] ?? '') ?></p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                        <?= e(strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 2))) ?>
                    </div>
                    <svg id="profile-chevron" class="w-4 h-4 text-on-surface-variant transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div id="profile-menu" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-card border border-outline-variant overflow-hidden z-50">
                    <a href="<?= url('profil') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-low transition-colors">
                        <svg class="w-4 h-4 text-outline" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Profil
                    </a>
                    <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-error hover:bg-surface-low transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9.75"/></svg>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
