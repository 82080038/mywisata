/**
 * MyWisata Application - Progress Indicator Helper
 * 
 * Provides progress indicators for long-running operations
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

const ProgressHelper = {
    /**
     * Show progress modal
     * @param {string} title - Modal title
     * @param {string} message - Progress message
     * @param {number} progress - Initial progress (0-100)
     */
    showProgress: function(title = 'Memproses...', message = 'Mohon tunggu', progress = 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                html: `
                    <div class="text-center">
                        <p>${message}</p>
                        <div class="progress" style="height: 20px; margin-top: 15px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: ${progress}%" 
                                 id="progressBar"></div>
                        </div>
                        <p class="mt-2 mb-0"><small id="progressText">${progress}%</small></p>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    },

    /**
     * Update progress
     * @param {number} progress - Progress value (0-100)
     * @param {string} message - Optional message update
     */
    updateProgress: function(progress, message = null) {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
        
        if (progressText) {
            progressText.textContent = `${progress}%`;
        }
        
        if (message && typeof Swal !== 'undefined') {
            Swal.update({
                html: `
                    <div class="text-center">
                        <p>${message}</p>
                        <div class="progress" style="height: 20px; margin-top: 15px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: ${progress}%" 
                                 id="progressBar"></div>
                        </div>
                        <p class="mt-2 mb-0"><small id="progressText">${progress}%</small></p>
                    </div>
                `
            });
        }
    },

    /**
     * Hide progress modal
     * @param {string} title - Success title
     * @param {string} message - Success message
     */
    hideProgress: function(title = 'Selesai', message = 'Operasi berhasil diselesaikan') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }
    },

    /**
     * Show progress with steps
     * @param {string} title - Modal title
     * @param {array} steps - Array of step names
     * @param {number} currentStep - Current step index
     */
    showStepProgress: function(title, steps, currentStep = 0) {
        let stepsHtml = '';
        steps.forEach((step, index) => {
            const isActive = index === currentStep;
            const isCompleted = index < currentStep;
            const statusClass = isCompleted ? 'success' : (isActive ? 'primary' : 'secondary');
            const icon = isCompleted ? 'fa-check' : (isActive ? 'fa-spinner fa-spin' : 'fa-circle');
            
            stepsHtml += `
                <div class="step-item ${isActive ? 'active' : ''}">
                    <div class="step-icon text-${statusClass}">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="step-text">${step}</div>
                </div>
            `;
        });

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                html: `
                    <div class="steps-container">
                        ${stepsHtml}
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false
            });
        }
    },

    /**
     * Update step progress
     * @param {number} currentStep - Current step index
     */
    updateStepProgress: function(currentStep) {
        const stepItems = document.querySelectorAll('.step-item');
        stepItems.forEach((item, index) => {
            const icon = item.querySelector('.step-icon i');
            const isActive = index === currentStep;
            const isCompleted = index < currentStep;
            
            item.classList.toggle('active', isActive);
            
            if (isCompleted) {
                icon.className = 'fas fa-check';
                item.querySelector('.step-icon').className = 'step-icon text-success';
            } else if (isActive) {
                icon.className = 'fas fa-spinner fa-spin';
                item.querySelector('.step-icon').className = 'step-icon text-primary';
            } else {
                icon.className = 'fas fa-circle';
                item.querySelector('.step-icon').className = 'step-icon text-secondary';
            }
        });
    }
};

// Add CSS for step progress
const style = document.createElement('style');
style.textContent = `
    .steps-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }
    
    .step-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-radius: 5px;
        background: #f8f9fa;
    }
    
    .step-item.active {
        background: #e7f1ff;
        border: 2px solid #0d6efd;
    }
    
    .step-icon {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: white;
    }
    
    .step-text {
        flex: 1;
    }
`;
document.head.appendChild(style);

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Make ProgressHelper available globally
    window.ProgressHelper = ProgressHelper;
    console.log('Progress Helper: Initialized successfully');
});
