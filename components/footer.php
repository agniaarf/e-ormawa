<?php declare(strict_types=1); ?>
    </main>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php
$flash = get_flash();
if ($flash):
?>
<script>
    Swal.fire({
        icon: '<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : 'info') ?>',
        title: '<?= e($flash['message']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
</script>
<?php endif; ?>
</body>
</html>
