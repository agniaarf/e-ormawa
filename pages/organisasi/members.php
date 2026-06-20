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
$sql = "SELECT uo.*, u.nama, u.nim, u.email FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.organisasi_id=?";
$params = [$org_id];
if ($search !== '') { $sql .= " AND (u.nama LIKE ? OR u.nim LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY FIELD(uo.role,'leader','staff','member'), u.nama";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $list = $stmt->fetchAll();

$edit = null;
if ($can_manage && isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT uo.*, u.nama FROM user_organisasi uo JOIN users u ON uo.user_id=u.id WHERE uo.id=? AND uo.organisasi_id=? LIMIT 1");
    $edit->execute([(int)$_GET['edit'], $org_id]); $edit = $edit->fetch();
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
                <form method="GET" action="" class="flex gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-input !h-9 !text-sm" placeholder="Cari member...">
                    <button type="submit" class="btn-primary !h-9 !px-3 !text-sm">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead><tr><th>Nama</th><th>NIM</th><th>Role</th><th>Status</th><?php if ($can_manage): ?><th class="text-right">Aksi</th><?php endif; ?></tr></thead>
                    <tbody>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
                            <td class="text-sm text-on-surface-variant"><?= e($row['nim']) ?></td>
                            <td><span class="badge <?= memberRoleBadge($row['role']) ?>"><?= e(org_role_label($row['role'])) ?></span></td>
                            <td><span class="badge <?= $row['status']==='aktif'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' ?>"><?= e($row['status']) ?></span></td>
                            <?php if ($can_manage): ?>
                            <td class="text-right whitespace-nowrap">
                                <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
                                <form method="POST" action="" class="inline" onsubmit="return confirm('Keluarkan member ini?')">
                                    <?= csrf_input() ?><input type="hidden" name="intent" value="remove_member"><input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Keluarkan</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?><tr><td colspan="<?= $can_manage?5:4 ?>" class="text-center text-on-surface-variant py-8">Belum ada member</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php if ($can_manage && $edit):
$modal_id = 'modalMember'; $modal_title = 'Edit Member'; ob_start();
?>
<form method="POST" action="" class="space-y-4">
    <?= csrf_input() ?>
    <input type="hidden" name="intent" value="update_member">
    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
    <p class="text-sm text-on-surface-variant">Member: <strong class="text-on-surface"><?= e($edit['nama']) ?></strong></p>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Role</label>
        <select name="role" class="form-input" <?= $can_assign ? '' : 'disabled' ?>>
            <option value="member" <?= $edit['role']==='member'?'selected':'' ?>>Member</option>
            <option value="staff" <?= $edit['role']==='staff'?'selected':'' ?>>Staff</option>
            <option value="leader" <?= $edit['role']==='leader'?'selected':'' ?>>Leader</option>
        </select>
        <?php if (!$can_assign): ?><p class="text-xs text-on-surface-variant mt-1">Hanya Leader yang dapat mengubah role.</p><?php endif; ?>
    </div>
    <div>
        <label class="block text-sm font-semibold text-on-surface mb-1.5">Status</label>
        <select name="status" class="form-input">
            <option value="aktif" <?= $edit['status']==='aktif'?'selected':'' ?>>Aktif</option>
            <option value="nonaktif" <?= $edit['status']==='nonaktif'?'selected':'' ?>>Nonaktif</option>
        </select>
    </div>
    <div class="flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalMember')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-sm font-medium hover:bg-surface-low">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </div>
</form>
<?php $modal_content = ob_get_clean(); require __DIR__ . '/../../components/modal.php'; ?>
<script>document.addEventListener('DOMContentLoaded',()=>openModal('modalMember'));</script>
<?php endif; ?>

<?php require __DIR__ . '/../../components/footer.php'; ?>
