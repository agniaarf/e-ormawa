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

require_can('member.read', $org_id);

$page_title = 'Member · ' . $org['nama'];
$current_page = 'org_member';
$current_org_id = $org_id;
$can_manage = can('member.manage', $org_id);
$can_assign = can('member.assign_role', $org_id); // leader only

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) { set_flash('error', 'Token CSRF tidak valid.'); redirect('/organisasi/' . $org_id . '/member'); }
    require_can('member.manage', $org_id);
    $intent = $_POST['intent'] ?? '';

    if ($intent === 'update_member') {
        $mid = (int) ($_POST['id'] ?? 0);
        $new_role = $_POST['role'] ?? 'member';
        $new_status = $_POST['status'] ?? 'aktif';
        // Only leader may change role; staff can only change status
        if (!$can_assign) { $new_role = null; }
        if (!in_array($new_role, ['leader','staff','member', null], true)) { $new_role = 'member'; }
        if ($new_role !== null) {
            $pdo->prepare("UPDATE user_organisasi SET role=?, status=? WHERE id=? AND organisasi_id=?")
                ->execute([$new_role, $new_status, $mid, $org_id]);
        } else {
            $pdo->prepare("UPDATE user_organisasi SET status=? WHERE id=? AND organisasi_id=?")
                ->execute([$new_status, $mid, $org_id]);
        }
        log_activity($user_id, 'Update Member', "Org: $org_id, Member: $mid");
        set_flash('success', 'Data member diperbarui.');
        redirect('/organisasi/' . $org_id . '/member');
    }

    if ($intent === 'remove_member') {
        $mid = (int) ($_POST['id'] ?? 0);
        // prevent removing the last leader
        $pdo->prepare("DELETE FROM user_organisasi WHERE id=? AND organisasi_id=?")->execute([$mid, $org_id]);
        log_activity($user_id, 'Hapus Member', "Org: $org_id, Member: $mid");
        set_flash('success', 'Member dikeluarkan dari organisasi.');
        redirect('/organisasi/' . $org_id . '/member');
    }
}

$search = trim($_GET['search'] ?? '');
$pageNum = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$sql = "SELECT uo.*, u.nama, u.nim, u.email FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.organisasi_id=?";
$params = [$org_id];
if ($search !== '') { $sql .= " AND (u.nama LIKE ? OR u.nim LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY FIELD(uo.role,'leader','staff','member'), u.nama";
$result = fetchPaginated($pdo, $sql, $params, $pageNum, $perPage);
$list = $result['list'];
$p = $result['p'];

if (($_GET['ajax'] ?? '') === 'table') {
    include __DIR__ . '/../../components/tables/members.php';
    exit;
}

function memberRoleBadge(string $role): string {
    return match ($role) {
        'leader' => 'bg-primary/10 text-primary',
        'staff'  => 'bg-blue-100 text-blue-700',
        default  => 'bg-gray-100 text-gray-600',
    };
}
?>
<?php require __DIR__ . '/../../components/head.php'; ?>
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<?php require __DIR__ . '/../../components/navbar.php'; ?>

<main class="p-6 lg:ml-[280px]">
    <div class="max-w-6xl mx-auto space-y-6">
        <a href="<?= url('organisasi/' . $org_id) ?>" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            <?= e($org['nama']) ?>
        </a>
        <div class="bg-white rounded-2xl border border-outline-variant shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-bold text-on-surface">Daftar Member</h3>
                <div class="relative max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-on-surface-variant" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <form method="GET" action="" class="w-full">
                        <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-10 !pl-10 !text-sm w-full" placeholder="Cari member..." autocomplete="off" data-live-search data-target="#member-table-body">
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>NIM</th><th>Role</th><th>Status</th><?php if ($can_manage): ?><th class="text-right">Aksi</th><?php endif; ?></tr></thead>
                    <tbody id="member-table-body">
                        <?php include __DIR__ . '/../../components/tables/members.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($can_manage): ?>
<div id="modalMember" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalMember')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-lg">Edit Member</h3>
                <button type="button" onclick="closeModal('modalMember')" class="p-1.5 rounded-lg hover:bg-surface-low text-on-surface-variant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <form method="POST" action="" class="space-y-4" id="formMember">
                    <?= csrf_input() ?>
                    <input type="hidden" name="intent" value="update_member">
                    <input type="hidden" name="id" id="memberId" value="">
                    <p class="text-sm text-on-surface-variant">Member: <strong class="text-on-surface" id="memberNama"></strong></p>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Role</label>
                        <select name="role" id="memberRole" class="form-input" <?= $can_assign ? '' : 'disabled' ?>>
                            <option value="member">Member</option>
                            <option value="staff">Staff</option>
                            <option value="leader">Leader</option>
                        </select>
                        <?php if (!$can_assign): ?><p class="text-xs text-on-surface-variant mt-1">Hanya Leader yang dapat mengubah role.</p><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
                        <select name="status" id="memberStatus" class="form-input">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal('modalMember')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openMemberModal(row) {
    document.getElementById('memberId').value = row.id;
    document.getElementById('memberNama').textContent = row.nama;
    document.getElementById('memberRole').value = row.role;
    document.getElementById('memberStatus').value = row.status;
    openModal('modalMember');
}
</script>
<?php endif; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
