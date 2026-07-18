/**
 * MyWisata Application - Network Resilience Helper
 * 
 * Provides network resilience with retry mechanism and offline detection
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

const NetworkHelper = {
    /**
     * Fetch with retry mechanism
     * @param {string} url - Request URL
     * @param {object} options - Fetch options
     * @param {number} retries - Number of retries (default: 3)
     * @param {number} delay - Initial delay in ms (default: 1000)
     * @returns {Promise} - Fetch response
     */
    fetchWithRetry: async function(url, options = {}, retries = 3, delay = 1000) {
        for (let i = 0; i < retries; i++) {
            try {
                const response = await fetch(url, options);
                if (response.ok) return response;
                
                // If response is not ok, check if we should retry
                if (response.status >= 500 && i < retries - 1) {
                    console.warn(`Request failed with status ${response.status}, retrying... (${i + 1}/${retries})`);
                    await this.sleep(delay * Math.pow(2, i)); // Exponential backoff
                    continue;
                }
                
                return response;
            } catch (error) {
                if (i === retries - 1) throw error;
                
                console.warn(`Request failed, retrying... (${i + 1}/${retries})`, error);
                await this.sleep(delay * Math.pow(2, i)); // Exponential backoff
            }
        }
    },

    /**
     * Sleep function for delays
     * @param {number} ms - Milliseconds to sleep
     * @returns {Promise}
     */
    sleep: function(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    /**
     * Check if online
     * @returns {boolean}
     */
    isOnline: function() {
        return navigator.onLine;
    },

    /**
     * Setup offline detection
     */
    setupOfflineDetection: function() {
        window.addEventListener('offline', () => {
            this.showOfflineWarning();
        });

        window.addEventListener('online', () => {
            this.showOnlineNotification();
        });
    },

    /**
     * Show offline warning
     */
    showOfflineWarning: function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Offline',
                text: 'Anda sedang offline. Beberapa fitur mungkin tidak berfungsi.',
                confirmButtonColor: '#0d6efd',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
        }
    },

    /**
     * Show online notification
     */
    showOnlineNotification: function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Online',
                text: 'Koneksi internet telah kembali.',
                confirmButtonColor: '#0d6efd',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    },

    /**
     * AJAX wrapper with retry and error handling
     * @param {object} config - AJAX configuration
     * @returns {Promise}
     */
    ajax: async function(config) {
        const { url, method = 'GET', data = null, retries = 3, delay = 1000 } = config;
        
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await this.fetchWithRetry(url, options, retries, delay);
            const responseData = await response.json();
            
            if (!response.ok) {
                throw new Error(responseData.message || 'Request failed');
            }
            
            return responseData;
        } catch (error) {
            console.error('AJAX request failed:', error);
            
            if (typeof Toast !== 'undefined') {
                Toast.error('Gagal menghubungi server. Periksa koneksi internet Anda.');
            }
            
            throw error;
        }
    },

    /**
     * Get connection type
     * @returns {string}
     */
    getConnectionType: function() {
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (connection) {
            return connection.effectiveType || 'unknown';
        }
        return 'unknown';
    },

    /**
     * Check if connection is slow
     * @returns {boolean}
     */
    isSlowConnection: function() {
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (connection) {
            return connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g';
        }
        return false;
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    NetworkHelper.setupOfflineDetection();
    
    // Make NetworkHelper available globally
    window.NetworkHelper = NetworkHelper;
    console.log('Network Helper: Initialized successfully');
});
