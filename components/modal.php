<?php declare(strict_types=1);
$id = $modal_id ?? 'modal';
$title = $modal_title ?? '';
?>
<div id="<?= e($id) ?>" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeModal('<?= e($id) ?>')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-outline-variant shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-lg"><?= e($title) ?></h3>
                <button type="button" onclick="closeModal('<?= e($id) ?>')" class="p-1.5 rounded-lg hover:bg-surface-low text-on-surface-variant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <?= $modal_content ?? '' ?>
            </div>
        </div>
    </div>
</div>
