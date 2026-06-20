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
            <a href="?arsip=1&restore=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100">Pulihkan</a>
        <?php else: ?>
            <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
            <?php if ($row['role_id'] != 1): ?>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Arsipkan pengguna ini?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
            <?php endif; ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="6" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr><?php endif; ?>
