<?php
foreach ($list as $row):
?>
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
