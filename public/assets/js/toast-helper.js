/**
 * MyWisata Application - Toast Notification Helper
 * 
 * Provides quick, non-intrusive toast notifications using SweetAlert2
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

const Toast = {
    /**
     * Show success toast
     * @param {string} message - Success message
     * @param {number} duration - Duration in milliseconds (default: 3000)
     */
    success: function(message, duration = 3000) {
        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            }).fire({
                icon: 'success',
                title: message
            });
        } else {
            console.log('Toast (success):', message);
        }
    },

    /**
     * Show error toast
     * @param {string} message - Error message
     * @param {number} duration - Duration in milliseconds (default: 4000)
     */
    error: function(message, duration = 4000) {
        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            }).fire({
                icon: 'error',
                title: message
            });
        } else {
            console.error('Toast (error):', message);
        }
    },

    /**
     * Show warning toast
     * @param {string} message - Warning message
     * @param {number} duration - Duration in milliseconds (default: 3500)
     */
    warning: function(message, duration = 3500) {
        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            }).fire({
                icon: 'warning',
                title: message
            });
        } else {
            console.warn('Toast (warning):', message);
        }
    },

    /**
     * Show info toast
     * @param {string} message - Info message
     * @param {number} duration - Duration in milliseconds (default: 3000)
     */
    info: function(message, duration = 3000) {
        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            }).fire({
                icon: 'info',
                title: message
            });
        } else {
            console.info('Toast (info):', message);
        }
    },

    /**
     * Show custom toast
     * @param {object} options - SweetAlert2 options
     */
    custom: function(options) {
        if (typeof Swal !== 'undefined') {
            const defaultOptions = {
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            };
            Swal.mixin(defaultOptions).fire(options);
        } else {
            console.log('Toast (custom):', options);
        }
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Make Toast available globally
    window.Toast = Toast;
    console.log('Toast Helper: Initialized successfully');
});
