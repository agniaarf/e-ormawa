<?php
foreach ($list as $row):
?>
<tr>
    <td class="text-sm font-medium text-on-surface"><?= e($row['nama']) ?></td>
    <td class="text-sm text-on-surface-variant"><?= ucfirst($row['tipe']) ?></td>
    <td class="text-sm text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($row['tanggal_mulai']))) ?></td>
    <td class="text-sm text-on-surface-variant"><?= e($row['lokasi'] ?: '-') ?></td>
    <td><span class="badge <?= $row['status']==='berlangsung'?'bg-green-100 text-green-700':($row['status']==='selesai'?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-600') ?>"><?= ucfirst($row['status']) ?></span></td>
    <td class="text-right whitespace-nowrap">
        <a href="<?= url('organisasi/' . $org_id . '/kegiatan/' . $row['id']) ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-surface-low text-on-surface-variant text-xs font-semibold hover:bg-surface-high mr-1">Detail</a>
        <?php if ($can_manage): ?>
        <a href="?edit=<?= $row['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 mr-1">Edit</a>
        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Arsipkan kegiatan ini?')" class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-error text-xs font-semibold hover:bg-red-100">Hapus</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="6" class="text-center text-on-surface-variant py-8">Belum ada kegiatan</td></tr><?php endif; ?>
