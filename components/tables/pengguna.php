<?php
foreach ($list as $row):
?>
<tr>
    <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
    <td class="text-sm text-on-surface-variant"><?= e($row['email']) ?></td>
    <td class="text-sm text-on-surface-variant"><?= e($row['nim']) ?></td>
    <td class="text-sm text-on-surface-variant"><?= e($row['role_name']) ?></td>
    <td><span class="badge <?= $row['status']==='aktif'?'bg-green-100 text-green-700':($row['status']==='menunggu'?'bg-yellow-100 text-yellow-700':'bg-gray-100 text-gray-600') ?>"><?= e($row['status']) ?></span></td>
    <td class="text-right whitespace-nowrap">
        <?php if ($show_arsip): ?>
            <a href="?arsip=1&restore=<?= $row['id'] ?>" title="Pulihkan" class="inline-flex items-center px-2 py-1.5 rounded-md bg-green-50 text-green-700 hover:bg-green-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
            </a>
        <?php else: ?>
            <a href="?edit=<?= $row['id'] ?>" title="Edit" class="inline-flex items-center px-2 py-1.5 rounded-md bg-primary/10 text-primary hover:bg-primary/20 mr-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
            </a>
            <?php if ($row['role_id'] != 1): ?>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Arsipkan pengguna ini?')" title="Hapus" class="inline-flex items-center px-2 py-1.5 rounded-md bg-red-50 text-error hover:bg-red-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            </a>
            <?php endif; ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="6" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr><?php endif; ?>
