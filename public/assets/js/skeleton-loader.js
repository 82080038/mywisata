/**
 * MyWisata Application - Skeleton Loader
 * 
 * Handles skeleton loading screens for images and content
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

const SkeletonLoader = {
    /**
     * Initialize skeleton loading for images
     */
    init: function() {
        // Find all images with lazy loading
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            this.setupLazyImage(img);
        });

        // Setup Intersection Observer for lazy loading
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without Intersection Observer
            images.forEach(img => {
                this.loadImage(img);
            });
        }
    },

    /**
     * Setup lazy image with skeleton
     * @param {HTMLImageElement} img - Image element
     */
    setupLazyImage: function(img) {
        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'lazyload-wrapper';
        
        // Create skeleton
        const skeleton = document.createElement('div');
        skeleton.className = 'skeleton';
        
        // Insert before image
        img.parentNode.insertBefore(wrapper, img);
        wrapper.appendChild(skeleton);
        wrapper.appendChild(img);
        
        // Add loading class
        img.classList.add('lazyload');
    },

    /**
     * Load image
     * @param {HTMLImageElement} img - Image element
     */
    loadImage: function(img) {
        const src = img.getAttribute('data-src');
        if (!src) return;

        img.onload = function() {
            this.classList.add('loaded');
            const skeleton = this.parentElement.querySelector('.skeleton');
            if (skeleton) {
                skeleton.style.display = 'none';
            }
        };

        img.onerror = function() {
            const skeleton = this.parentElement.querySelector('.skeleton');
            if (skeleton) {
                skeleton.style.background = '#dc3545';
            }
        };

        img.src = src;
    },

    /**
     * Create skeleton card
     * @param {number} count - Number of skeleton cards
     * @returns {string} - HTML string
     */
    createSkeletonCards: function(count = 3) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="card mb-4">
                    <div class="skeleton skeleton-image"></div>
                    <div class="card-body">
                        <div class="skeleton skeleton-title"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text-short"></div>
                    </div>
                </div>
            `;
        }
        return html;
    },

    /**
     * Show skeleton loading
     * @param {string} selector - Container selector
     * @param {number} count - Number of items
     */
    showSkeleton: function(selector, count = 3) {
        const container = document.querySelector(selector);
        if (!container) return;

        container.innerHTML = this.createSkeletonCards(count);
    },

    /**
     * Hide skeleton loading
     * @param {string} selector - Container selector
     * @param {string} content - Content to show
     */
    hideSkeleton: function(selector, content) {
        const container = document.querySelector(selector);
        if (!container) return;

        container.innerHTML = content;
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    SkeletonLoader.init();
    
    // Make SkeletonLoader available globally
    window.SkeletonLoader = SkeletonLoader;
    console.log('Skeleton Loader: Initialized successfully');
});
