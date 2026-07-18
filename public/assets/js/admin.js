/**
 * MyWisata Application - Admin JavaScript
 * 
 * @package MyWisata
 * @version 2.0.0
 * @since 2026-06-30
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTables
    const datatables = document.querySelectorAll('.datatable');
    datatables.forEach(table => {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $(table).DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 20,
                lengthMenu: [10, 20, 50, 100]
            });
        }
    });

    // Initialize dark mode for admin
    initializeAdminDarkMode();
});

/**
 * Initialize dark mode for admin panel
 */
function initializeAdminDarkMode() {
    const themeToggle = document.querySelector('.admin-theme-toggle');
    if (!themeToggle) return;
    
    const savedTheme = localStorage.getItem('adminTheme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateAdminThemeIcon(savedTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('adminTheme', newTheme);
        updateAdminThemeIcon(newTheme);
    });
}

function updateAdminThemeIcon(theme) {
    const themeToggle = document.querySelector('.admin-theme-toggle');
    if (!themeToggle) return;
    
    const icon = themeToggle.querySelector('i');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

/**
 * AJAX helper function (vanilla JS)
 */
window.ajax = async function(options) {
    const defaults = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        },
        body: JSON.stringify(options.data || {})
    };
    
    const config = { ...defaults, ...options };
    delete config.data;
    
    try {
        const response = await fetch(config.url, config);
        const data = await response.json();
        
        if (config.success) {
            config.success(data);
        }
        return data;
    } catch (error) {
        console.error('AJAX error:', error);
        if (config.error) {
            config.error(error);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan. Silakan coba lagi.',
                confirmButtonColor: '#0d6efd'
            });
        }
    }
};

/**
 * Approve guide function
 */
window.approveGuide = async function (id) {
    const result = await Swal.fire({
        title: 'Setujui Tour Guide?',
        text: 'Apakah Anda yakin ingin menyetujui tour guide ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await ajax({
                url: window.APP_URL + 'admin/approveGuide',
                method: 'POST',
                data: { id: id }
            });
            
            if (response.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                location.reload();
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonColor: '#0d6efd'
                });
            }
        } catch (error) {
            console.error('Approve error:', error);
        }
    }
};

/**
 * Delete confirmation
 */
window.confirmDelete = async function (url, message) {
    const result = await Swal.fire({
        title: 'Hapus Data?',
        text: message || 'Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });
    
    if (result.isConfirmed) {
        window.location.href = url;
    }
};
