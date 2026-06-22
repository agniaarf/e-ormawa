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
function toggleOrgSection(oid) {
    const content = document.getElementById('org-section-' + oid);
    const chevron = document.getElementById('org-chevron-' + oid);
    if (content) content.classList.toggle('collapsed');
    if (chevron) chevron.classList.toggle('collapsed');
}

// Global live search
function initLiveSearch() {
    document.querySelectorAll('[data-live-search]').forEach(input => {
        let t;
        const target = input.dataset.target ? document.querySelector(input.dataset.target) : null;
        input.addEventListener('keyup', () => {
            clearTimeout(t);
            t = setTimeout(() => {
                if (target) {
                    const url = new URL(location.href);
                    url.searchParams.set(input.dataset.searchParam || 'search', input.value);
                    url.searchParams.set('ajax', 'table');
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.text())
                        .then(html => { target.innerHTML = html; })
                        .catch(() => input.form?.submit());
                } else {
                    input.form?.submit();
                }
            }, 350);
        });
    });
}

function toggleProfileDropdown() {
    const menu = document.getElementById('profile-menu');
    const chevron = document.getElementById('profile-chevron');
    if (menu && chevron) {
        menu.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }
}

document.addEventListener('click', function (e) {
    const wrapper = document.getElementById('profile-dropdown');
    const menu = document.getElementById('profile-menu');
    const chevron = document.getElementById('profile-chevron');
    if (wrapper && menu && !wrapper.contains(e.target)) {
        menu.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }
});

document.addEventListener('DOMContentLoaded', initLiveSearch);

// Auto close alert
$(document).ready(function() {
    $('.alert-dismissible').delay(4000).fadeOut(300);
});
