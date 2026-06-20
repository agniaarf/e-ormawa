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
        <a href="?edit=<?= $row['id'] ?>" title="Edit" class="inline-flex items-center px-2 py-1.5 rounded-md bg-primary/10 text-primary hover:bg-primary/20 mr-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
        </a>
        <form method="POST" action="" class="inline" onsubmit="return confirm('Keluarkan member ini?')">
            <?= csrf_input() ?><input type="hidden" name="intent" value="remove_member"><input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit" title="Keluarkan" class="inline-flex items-center px-2 py-1.5 rounded-md bg-red-50 text-error hover:bg-red-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </form>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="<?= $can_manage?5:4 ?>" class="text-center text-on-surface-variant py-8">Belum ada member</td></tr><?php endif; ?>
