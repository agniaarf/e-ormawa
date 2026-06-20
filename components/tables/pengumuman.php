<?php
foreach ($list as $row):
?>
<tr>
    <td class="text-sm font-medium text-on-surface"><?= e($row['judul']) ?></td>
    <td><span class="badge <?= $row['tipe']==='global'?'bg-blue-100 text-blue-700':'bg-purple-100 text-purple-700' ?>"><?= e($row['tipe']) ?></span></td>
    <td class="text-sm text-on-surface-variant"><?= e($row['org_nama'] ?? '-') ?></td>
    <td class="text-sm text-on-surface-variant"><?= e(date('d M Y', strtotime($row['created_at']))) ?></td>
    <td class="text-right whitespace-nowrap">
        <?php if (canModifyPengumuman($row)): ?>
        <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
        <?php else: ?>
        <span class="text-xs text-on-surface-variant">-</span>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="5" class="text-center text-on-surface-variant py-8">Tidak ada data</td></tr><?php endif; ?>
