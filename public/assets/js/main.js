/**
 * MyWisata Application - Main JavaScript
 * 
 * @package MyWisata
 * @version 2.0.0
 * @since 2026-06-30
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap components
    initializeBootstrapComponents();
    
    // Initialize dark mode
    initializeDarkMode();
    
    // Initialize sticky navbar
    initializeStickyNavbar();
    
    // Initialize scroll animations
    initializeScrollAnimations();
    
    // Initialize lazy loading
    initializeLazyLoading();
    
    // Initialize search autocomplete
    initializeSearchAutocomplete();
    
    // Initialize image gallery
    initializeImageGallery();
    
    // Initialize statistics counter
    initializeStatCounter();
    
    // Initialize swipe gestures
    initializeSwipeGestures();
    
    // Set up global helpers
    setupGlobalHelpers();
});

/**
 * Initialize Bootstrap components
 */
function initializeBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    popoverTriggerList.forEach(popoverTriggerEl => {
        new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Initialize toasts
    const toastTriggerList = document.querySelectorAll('[data-bs-toggle="toast"]');
    toastTriggerList.forEach(toastTriggerEl => {
        const toastEl = document.getElementById(toastTriggerEl.getAttribute('data-bs-target'));
        if (toastEl) {
            new bootstrap.Toast(toastEl);
        }
    });
}

/**
 * Initialize dark mode
 */
function initializeDarkMode() {
    const themeToggle = document.querySelector('.theme-toggle');
    if (!themeToggle) return;
    
    // Check for saved theme preference or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });
}

function updateThemeIcon(theme) {
    const themeToggle = document.querySelector('.theme-toggle');
    if (!themeToggle) return;
    
    const icon = themeToggle.querySelector('i');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

/**
 * Initialize sticky navbar
 */
function initializeStickyNavbar() {
    const navbar = document.querySelector('.navbar-sticky');
    if (!navbar) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

/**
 * Initialize scroll animations
 */
function initializeScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.card, .section-title, .stat-card').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Initialize lazy loading for images
 */
function initializeLazyLoading() {
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazyload');
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            }
        });
    });

    document.querySelectorAll('.lazyload').forEach(img => {
        imageObserver.observe(img);
    });
}

/**
 * Initialize search autocomplete
 */
function initializeSearchAutocomplete() {
    const searchInput = document.querySelector('.search-autocomplete input');
    if (!searchInput) return;
    
    let searchTimeout;
    const suggestionsContainer = document.querySelector('.search-autocomplete .suggestions');
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            if (suggestionsContainer) {
                suggestionsContainer.classList.remove('show');
            }
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    });
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-autocomplete')) {
            if (suggestionsContainer) {
                suggestionsContainer.classList.remove('show');
            }
        }
    });
}

async function fetchSearchSuggestions(query) {
    const suggestionsContainer = document.querySelector('.search-autocomplete .suggestions');
    const searchInput = document.querySelector('.search-autocomplete input');
    if (!suggestionsContainer) return;
    
    // Show loading state
    suggestionsContainer.innerHTML = '<div class="suggestion-item loading"><i class="fas fa-spinner fa-spin me-2"></i>Mencari...</div>';
    suggestionsContainer.classList.add('show');
    
    try {
        const response = await fetch(`${window.APP_URL}api/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.results.length > 0) {
            suggestionsContainer.innerHTML = data.results.map(item => `
                <div class="suggestion-item" data-url="${item.url}">
                    ${item.highlighted_name || item.name}
                    <small class="text-muted d-block">${item.type || 'Destination'}</small>
                </div>
            `).join('');
            
            // Add click handlers
            suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', function() {
                    window.location.href = this.dataset.url;
                });
            });
            
            suggestionsContainer.classList.add('show');
        } else {
            suggestionsContainer.classList.remove('show');
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

/**
 * Initialize image gallery
 */
function initializeImageGallery() {
    const gallery = document.querySelector('.destination-gallery');
    if (!gallery) return;
    
    const mainImage = gallery.querySelector('.main-image img');
    const thumbnails = gallery.querySelectorAll('.thumbnail');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main image
            if (mainImage && this.dataset.full) {
                mainImage.style.opacity = '0';
                setTimeout(() => {
                    mainImage.src = this.dataset.full;
                    mainImage.style.opacity = '1';
                }, 200);
            }
        });
    });
}

/**
 * Initialize statistics counter animation
 */
function initializeStatCounter() {
    const counters = document.querySelectorAll('.stat-counter');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.target || counter.textContent);
                animateValue(counter, 0, target, 2000);
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));
}

function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString('id-ID');
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

/**
 * Initialize swipe gestures for mobile
 */
function initializeSwipeGestures() {
    const gallery = document.querySelector('.destination-gallery');
    if (!gallery) return;
    
    let touchStartX = 0;
    let touchEndX = 0;
    const mainImage = gallery.querySelector('.main-image img');
    const thumbnails = Array.from(gallery.querySelectorAll('.thumbnail'));
    
    gallery.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    gallery.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const activeIndex = thumbnails.findIndex(t => t.classList.contains('active'));
        
        if (touchEndX < touchStartX - swipeThreshold) {
            // Swipe left - next image
            const nextIndex = (activeIndex + 1) % thumbnails.length;
            thumbnails[nextIndex].click();
        }
        
        if (touchEndX > touchStartX + swipeThreshold) {
            // Swipe right - previous image
            const prevIndex = (activeIndex - 1 + thumbnails.length) % thumbnails.length;
            thumbnails[prevIndex].click();
        }
    }
}

/**
 * Set up global helper functions
 */
function setupGlobalHelpers() {
    // AJAX helper function (vanilla JS)
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
    
    // Format currency helper
    window.formatCurrency = function(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
    };
    
    // Format date helper
    window.formatDate = function(dateString, format) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    };
    
    // Show toast notification
    window.showToast = function(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container') || createToastContainer();
        
        const toastHTML = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas fa-${type === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'} me-2"></i>
                    <strong class="me-auto">${type === 'success' ? 'Sukses' : 'Error'}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            const toast = toastContainer.lastElementChild;
            if (toast) {
                toast.remove();
            }
        }, 3000);
    };
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1100';
    document.body.appendChild(container);
    return container;
}

// Track recently viewed
function addToRecentlyViewed(destinationId) {
    let recent = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    recent = recent.filter(id => id !== destinationId);
    recent.unshift(destinationId);
    recent = recent.slice(0, 5);
    localStorage.setItem('recentlyViewed', JSON.stringify(recent));
}

// Export for use in other files
window.MyWisata = {
    addToRecentlyViewed,
    initializeDarkMode,
    initializeScrollAnimations
};
