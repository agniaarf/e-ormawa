function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        overlay.classList.add('hidden');
    } else {
        sidebar.classList.add('open');
        overlay.classList.remove('hidden');
    }
}

function confirmDelete(message = 'Apakah Anda yakin?') {
    return Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ba1a1a',
        cancelButtonColor: '#717974',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then(r => r.isConfirmed);
}

// Modal helpers
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

// Auto close alert
$(document).ready(function() {
    $('.alert-dismissible').delay(4000).fadeOut(300);
});
