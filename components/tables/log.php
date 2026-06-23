<?php
foreach ($list as $log):
?>
<tr>
    <td class="text-sm font-medium text-on-surface"><?= e($log['nama'] ?? 'Sistem') ?></td>
    <td class="text-sm text-on-surface-variant"><?= e($log['aksi']) ?></td>
    <td class="text-sm text-on-surface-variant"><?= e($log['detail'] ?? '-') ?></td>
    <td class="text-sm text-on-surface-variant"><?= e($log['ip_address'] ?? '-') ?></td>
    <td class="text-sm text-on-surface-variant"><?= e(date('d M Y H:i', strtotime($log['created_at']))) ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="5" class="text-center text-on-surface-variant py-8">Belum ada aktivitas</td></tr><?php endif; ?>
<?php if (!empty($list)): ?>
<tr><td colspan="5" class="bg-white border-t border-outline-variant"><?= renderPagination($p) ?></td></tr>
<?php endif; ?>
