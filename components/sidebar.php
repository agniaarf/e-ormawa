<?php
declare(strict_types=1);
$role = $_SESSION['role'] ?? '';
$current = $current_page ?? '';
$current_org_id = (int) ($current_org_id ?? ($_GET['org_id'] ?? 0));

function makeMenu(array $items): string {
    $html = '';
    foreach ($items as $item) {
        $isActive = (!empty($item['active'])) ? 'sidebar-active' : 'hover:bg-white/10';
        $html .= '<a href="' . $item['url'] . '" class="flex items-center gap-3 px-4 py-3 mx-3 rounded-lg text-white/80 transition-colors ' . $isActive . '">';
        $html .= $item['icon'];
        $html .= '<span class="text-sm font-medium">' . e($item['label']) . '</span></a>';
    }
    return $html;
}

// Icon shortcuts
$icn = [
    'dashboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>',
    'org' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>',
    'users' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
    'announce' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 01-4.5-4.5 4.5 4.5 0 014.5-4.5h.75c.704 0 1.402.03 2.09.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-.585-2.783m9.468-11.19a4.5 4.5 0 00-4.5-4.5h-.75a4.5 4.5 0 00-4.5 4.5c0 2.495 2.01 4.5 4.5 4.5h.75c.704 0 1.402-.03 2.09-.09m0 9.18a20.845 20.845 0 00.585-2.783c.267-.578.976-.779 1.527-.461l.657.38c.523.302.71.961.463 1.511-.401.891-.732 1.821-.985 2.783M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'report' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
    'kegiatan' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>',
    'member' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
    'inbox' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/></svg>',
    'profile' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
    'bell' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>',
];

$menus = [];
$org_groups = [];

if ($role === 'Super Admin') {
    $menus = [
        ['page'=>'dashboard','label'=>'Dashboard','url'=>url('dashboard'),'icon'=>$icn['dashboard'],'active'=>$current==='dashboard'],
        ['page'=>'organisasi','label'=>'Kelola Organisasi','url'=>url('organisasi'),'icon'=>$icn['org'],'active'=>$current==='organisasi'],
        ['page'=>'pengguna','label'=>'Kelola Pengguna','url'=>url('pengguna'),'icon'=>$icn['users'],'active'=>$current==='pengguna'],
        ['page'=>'pengumuman','label'=>'Pengumuman','url'=>url('pengumuman'),'icon'=>$icn['announce'],'active'=>$current==='pengumuman'],
        ['page'=>'laporan','label'=>'Laporan','url'=>url('laporan'),'icon'=>$icn['report'],'active'=>$current==='laporan'],
        ['page'=>'log','label'=>'Log Aktivitas','url'=>url('log'),'icon'=>$icn['inbox'],'active'=>$current==='log'],
    ];
} else {
    // Mahasiswa persona
    $menus = [
        ['page'=>'dashboard','label'=>'Dashboard','url'=>url('dashboard'),'icon'=>$icn['dashboard'],'active'=>$current==='dashboard'],
        ['page'=>'organisasi','label'=>'Jelajah Organisasi','url'=>url('organisasi'),'icon'=>$icn['org'],'active'=>$current==='organisasi'],
        ['page'=>'profil','label'=>'Profil','url'=>url('profil'),'icon'=>$icn['profile'],'active'=>$current==='profil'],
        ['page'=>'notifikasi','label'=>'Notifikasi','url'=>url('notifikasi'),'icon'=>$icn['bell'],'active'=>$current==='notifikasi'],
    ];
    // Build per-organisation submenus based on the user's org role
    foreach (my_organisasi((int)($_SESSION['user_id'] ?? 0)) as $org) {
        $oid = (int) $org['id'];
        $r = $org['my_role'];
        $items = [];
        if ($r === 'leader') {
            $items[] = ['label'=>'Kelola Organisasi','url'=>url('organisasi/'.$oid),'icon'=>$icn['org'],'active'=>$current==='org_detail' && $current_org_id===$oid];
        } else {
            $items[] = ['label'=>'Detail Organisasi','url'=>url('organisasi/'.$oid),'icon'=>$icn['org'],'active'=>$current==='org_detail' && $current_org_id===$oid];
        }
        $items[] = ['label'=>($r==='member'?'Lihat Member':'Manajemen Member'),'url'=>url('organisasi/'.$oid.'/member'),'icon'=>$icn['member'],'active'=>$current==='org_member' && $current_org_id===$oid];
        $items[] = ['label'=>($r==='member'?'Lihat Kegiatan':'Manajemen Kegiatan'),'url'=>url('organisasi/'.$oid.'/kegiatan'),'icon'=>$icn['kegiatan'],'active'=>$current==='org_kegiatan' && $current_org_id===$oid];
        if (in_array($r, ['leader','staff'], true)) {
            $items[] = ['label'=>'Permintaan Bergabung','url'=>url('organisasi/'.$oid.'/permintaan'),'icon'=>$icn['inbox'],'active'=>$current==='org_permintaan' && $current_org_id===$oid];
        }
        $org_groups[] = ['name'=>$org['singkatan'] ?: $org['nama'], 'role'=>$r, 'items'=>$items];
    }
}
?>
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-[280px] h-screen transition-transform -translate-x-full lg:translate-x-0 bg-primary-light">
    <div class="h-full flex flex-col">
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <div class="w-9 h-9 rounded-lg bg-accent flex items-center justify-center">
                <span class="text-primary font-extrabold text-sm">EO</span>
            </div>
            <span class="text-white font-bold text-lg tracking-tight">E-ORMAWA</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            <?= makeMenu($menus) ?>
            <?php foreach ($org_groups as $group): ?>
            <div class="pt-4 mt-2 border-t border-white/10">
                <div class="px-6 pb-2 flex items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-white/50 truncate"><?= e($group['name']) ?></span>
                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-accent/20 text-accent"><?= e(org_role_label($group['role'])) ?></span>
                </div>
                <?= makeMenu($group['items']) ?>
            </div>
            <?php endforeach; ?>
        </nav>
        <div class="p-4 border-t border-white/10">
            <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-white/80 hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9.75"/></svg>
                <span class="text-sm font-medium">Keluar</span>
            </a>
        </div>
    </div>
</aside>
<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>
