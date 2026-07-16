/**
 * MyWisata Application - Loading Utilities
 * 
 * Client-side loading indicators and progress feedback
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

const Loading = {
    /**
     * Show loading overlay
     * @param {string} message - Loading message
     * @param {boolean} blur - Blur background
     */
    showOverlay: function(message = 'Memuat...', blur = true) {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.id = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content">
                <div class="spinner"></div>
                <p class="loading-message">${message}</p>
            </div>
        `;
        overlay.style.cssText = `
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9998;
            ${blur ? 'backdrop-filter: blur(5px);' : ''}
        `;
        
        const content = overlay.querySelector('.loading-content');
        content.style.cssText = 'text-align: center;';
        
        const spinner = overlay.querySelector('.spinner');
        spinner.style.cssText = `
            width: 50px; height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        `;
        
        const msg = overlay.querySelector('.loading-message');
        msg.style.cssText = 'color: #666; font-size: 16px; margin: 0;';
        
        document.body.appendChild(overlay);
        
        // Add keyframes if not exists
        if (!document.getElementById('loading-styles')) {
            const style = document.createElement('style');
            style.id = 'loading-styles';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    },
    
    /**
     * Hide loading overlay
     */
    hideOverlay: function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.3s ease';
            setTimeout(() => overlay.remove(), 300);
        }
    },
    
    /**
     * Show inline spinner
     * @param {HTMLElement} element - Target element
     * @param {string} size - Size (small, medium, large)
     */
    showSpinner: function(element, size = 'medium') {
        const sizes = { small: '20px', medium: '30px', large: '40px' };
        const spinnerSize = sizes[size] || sizes.medium;
        
        const spinner = document.createElement('div');
        spinner.className = 'inline-spinner';
        spinner.style.cssText = `
            width: ${spinnerSize}; height: ${spinnerSize};
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
        `;
        
        element.innerHTML = '';
        element.appendChild(spinner);
    },
    
    /**
     * Show progress bar
     * @param {number} progress - Progress percentage (0-100)
     * @param {string} message - Optional message
     */
    showProgress: function(progress, message = null) {
        let container = document.getElementById('progress-container');
        
        if (!container) {
            container = document.createElement('div');
            container.id = 'progress-container';
            container.style.cssText = `
                position: fixed;
                top: 20px; left: 50%;
                transform: translateX(-50%);
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                min-width: 300px;
            `;
            document.body.appendChild(container);
        }
        
        const progressValue = Math.min(100, Math.max(0, progress));
        
        container.innerHTML = `
            ${message ? `<p class="progress-message" style="margin: 0 0 10px 0; font-size: 14px; color: #666;">${message} (${progressValue}%)</p>` : ''}
            <div class="progress-bar" style="
                width: 100%;
                height: 8px;
                background: #e0e0e0;
                border-radius: 4px;
                overflow: hidden;
            ">
                <div class="progress-fill" style="
                    width: ${progressValue}%;
                    height: 100%;
                    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
                    transition: width 0.3s ease;
                    border-radius: 4px;
                "></div>
            </div>
        `;
    },
    
    /**
     * Hide progress bar
     */
    hideProgress: function() {
        const container = document.getElementById('progress-container');
        if (container) {
            container.style.opacity = '0';
            container.style.transition = 'opacity 0.3s ease';
            setTimeout(() => container.remove(), 300);
        }
    },
    
    /**
     * Show toast notification
     * @param {string} message - Message
     * @param {string} type - Type (success, error, warning, info)
     * @param {number} duration - Duration in milliseconds
     */
    showToast: function(message, type = 'info', duration = 3000) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        const icon = icons[type] || icons.info;
        const color = colors[type] || colors.info;
        
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.cssText = `
            position: fixed;
            top: 20px; right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            border-left: 4px solid ${color};
            max-width: 350px;
        `;
        
        toast.innerHTML = `
            <div class="toast-icon" style="
                width: 24px; height: 24px;
                background: ${color};
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                flex-shrink: 0;
            ">${icon}</div>
            <div class="toast-message" style="font-size: 14px; color: #333; word-break: break-word;">${message}</div>
        `;
        
        document.body.appendChild(toast);
        
        // Add keyframes if not exists
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    /**
     * Show skeleton loader
     * @param {HTMLElement} container - Container element
     * @param {string} type - Type (text, avatar, card, list)
     * @param {number} count - Number of items
     */
    showSkeleton: function(container, type = 'text', count = 1) {
        let html = '<div class="skeleton-loader">';
        
        for (let i = 0; i < count; i++) {
            switch (type) {
                case 'avatar':
                    html += `<div class="skeleton-avatar" style="
                        width: 50px; height: 50px;
                        border-radius: 50%;
                        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                        background-size: 200% 100%;
                        animation: shimmer 1.5s infinite;
                        margin-bottom: 10px;
                    "></div>`;
                    break;
                case 'card':
                    html += `<div class="skeleton-card" style="
                        width: 100%; height: 150px;
                        border-radius: 8px;
                        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                        background-size: 200% 100%;
                        animation: shimmer 1.5s infinite;
                        margin-bottom: 15px;
                    "></div>`;
                    break;
                case 'list':
                    html += `<div class="skeleton-list-item" style="
                        display: flex; align-items: center;
                        margin-bottom: 15px;
                    ">
                        <div class="skeleton-avatar" style="
                            width: 40px; height: 40px;
                            border-radius: 50%;
                            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                            background-size: 200% 100%;
                            animation: shimmer 1.5s infinite;
                            margin-right: 15px;
                        "></div>
                        <div class="skeleton-text" style="
                            flex: 1; height: 20px;
                            border-radius: 4px;
                            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                            background-size: 200% 100%;
                            animation: shimmer 1.5s infinite;
                        "></div>
                    </div>`;
                    break;
                case 'text':
                default:
                    html += `<div class="skeleton-text" style="
                        width: 100%; height: 20px;
                        border-radius: 4px;
                        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                        background-size: 200% 100%;
                        animation: shimmer 1.5s infinite;
                        margin-bottom: 10px;
                    "></div>`;
                    break;
            }
        }
        
        html += '</div>';
        
        container.innerHTML = html;
        
        // Add keyframes if not exists
        if (!document.getElementById('skeleton-styles')) {
            const style = document.createElement('style');
            style.id = 'skeleton-styles';
            style.textContent = `
                @keyframes shimmer {
                    0% { background-position: 200% 0; }
                    100% { background-position: -200% 0; }
                }
            `;
            document.head.appendChild(style);
        }
    },
    
    /**
     * Set button loading state
     * @param {HTMLElement} button - Button element
     * @param {boolean} loading - Loading state
     * @param {string} originalText - Original button text
     */
    setButtonLoading: function(button, loading, originalText) {
        if (loading) {
            button.dataset.originalText = originalText || button.textContent;
            button.disabled = true;
            button.innerHTML = `
                <span class="btn-spinner" style="
                    display: inline-block;
                    width: 16px; height: 16px;
                    border: 2px solid #f3f3f3;
                    border-top: 2px solid white;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-right: 8px;
                    vertical-align: middle;
                "></span>
                <span>Memuat...</span>
            `;
            button.style.opacity = '0.7';
            button.style.cursor = 'not-allowed';
        } else {
            button.disabled = false;
            button.textContent = button.dataset.originalText || originalText;
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
        }
    },
    
    /**
     * Wrap async function with loading indicator
     * @param {Function} asyncFn - Async function
     * @param {string} message - Loading message
     * @param {HTMLElement} button - Optional button to set loading state
     */
    withLoading: async function(asyncFn, message = 'Memuat...', button = null) {
        if (button) {
            Loading.setButtonLoading(button, true);
        } else {
            Loading.showOverlay(message);
        }
        
        try {
            const result = await asyncFn();
            return result;
        } catch (error) {
            Loading.showToast('Terjadi kesalahan: ' + error.message, 'error');
            throw error;
        } finally {
            if (button) {
                Loading.setButtonLoading(button, false);
            } else {
                Loading.hideOverlay();
            }
        }
    },
    
    /**
     * Show step progress
     * @param {Array} steps - Array of step names
     * @param {number} currentStep - Current step index
     * @param {HTMLElement} container - Container element
     */
    showStepProgress: function(steps, currentStep, container) {
        let html = '<div class="step-progress" style="display: flex; align-items: center; justify-content: space-between; margin: 20px 0;">';
        
        steps.forEach((step, index) => {
            const isCompleted = index < currentStep;
            const isCurrent = index === currentStep;
            const isPending = index > currentStep;
            
            html += `<div class="step-item" style="
                display: flex;
                flex-direction: column;
                align-items: center;
                flex: 1;
                position: relative;
            ">`;
            
            html += `<div class="step-circle" style="
                width: 40px; height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                margin-bottom: 8px;
                ${isCompleted ? 'background: #10b981; color: white;' : ''}
                ${isCurrent ? 'background: #3b82f6; color: white;' : ''}
                ${isPending ? 'background: #e0e0e0; color: #999;' : ''}
            ">${isCompleted ? '✓' : (index + 1)}</div>`;
            
            html += `<div class="step-label" style="
                font-size: 12px;
                text-align: center;
                ${isCurrent ? 'color: #3b82f6; font-weight: 600;' : ''}
                ${isCompleted ? 'color: #10b981;' : ''}
                ${isPending ? 'color: #999;' : ''}
            ">${step}</div>`;
            
            if (index < steps.length - 1) {
                html += `<div class="step-connector" style="
                    position: absolute;
                    top: 20px;
                    left: 50%;
                    width: 100%;
                    height: 2px;
                    background: ${isCompleted ? '#10b981' : '#e0e0e0'};
                "></div>`;
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        container.innerHTML = html;
    }
};

// Auto-initialize loading states for AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    // Intercept fetch requests for loading indicators
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const url = args[0];
        
        // Skip for non-API requests
        if (typeof url === 'string' && !url.includes('/api/')) {
            return originalFetch.apply(this, args);
        }
        
        Loading.showOverlay('Memuat data...');
        
        return originalFetch.apply(this, args)
            .finally(() => {
                Loading.hideOverlay();
            });
    };
    
    // Show loading on form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                Loading.setButtonLoading(button, true);
            }
        });
    });
    
    // Show skeleton loaders for lazy-loaded content
    const lazyElements = document.querySelectorAll('[data-lazy]');
    lazyElements.forEach(element => {
        const type = element.dataset.lazyType || 'text';
        const count = parseInt(element.dataset.lazyCount) || 1;
        Loading.showSkeleton(element, type, count);
    });
});
