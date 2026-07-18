/**
 * MyWisata Application - Form Validation Helper
 * 
 * Provides real-time form validation with user feedback
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

const FormValidation = {
    /**
     * Initialize validation for a form
     * @param {string} formSelector - Form selector
     * @param {object} rules - Validation rules
     */
    init: function(formSelector, rules = {}) {
        const form = document.querySelector(formSelector);
        if (!form) return;

        // Add real-time validation to all inputs
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            const fieldName = input.name;
            if (rules[fieldName]) {
                input.addEventListener('input', () => this.validateField(input, rules[fieldName]));
                input.addEventListener('blur', () => this.validateField(input, rules[fieldName]));
            }
        });

        // Validate on form submit
        form.addEventListener('submit', (e) => {
            let isValid = true;
            inputs.forEach(input => {
                const fieldName = input.name;
                if (rules[fieldName]) {
                    if (!this.validateField(input, rules[fieldName])) {
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                Toast.error('Mohon perbaiki error pada form sebelum submit');
            }
        });
    },

    /**
     * Validate a single field
     * @param {HTMLElement} field - Input field
     * @param {object} rule - Validation rule
     * @returns {boolean} - Is valid
     */
    validateField: function(field, rule) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (rule.required && value === '') {
            isValid = false;
            errorMessage = rule.requiredMessage || 'Field ini wajib diisi';
        }

        // Email validation
        if (rule.email && value !== '' && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Format email tidak valid';
        }

        // Password strength validation
        if (rule.passwordStrength && value !== '') {
            const strength = this.checkPasswordStrength(value);
            if (strength < rule.minStrength) {
                isValid = false;
                errorMessage = 'Password harus lebih kuat (minimal 8 karakter, huruf dan angka)';
            }
        }

        // Min length validation
        if (rule.minLength && value.length < rule.minLength) {
            isValid = false;
            errorMessage = `Minimal ${rule.minLength} karakter`;
        }

        // Max length validation
        if (rule.maxLength && value.length > rule.maxLength) {
            isValid = false;
            errorMessage = `Maksimal ${rule.maxLength} karakter`;
        }

        // Pattern validation
        if (rule.pattern && value !== '' && !rule.pattern.test(value)) {
            isValid = false;
            errorMessage = rule.patternMessage || 'Format tidak valid';
        }

        // Show/hide error
        this.showFieldError(field, isValid, errorMessage);

        // Update password strength indicator if applicable
        if (rule.passwordStrength) {
            this.updatePasswordStrengthIndicator(field, value);
        }

        return isValid;
    },

    /**
     * Check if email is valid
     * @param {string} email - Email address
     * @returns {boolean}
     */
    isValidEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    /**
     * Check password strength
     * @param {string} password - Password
     * @returns {number} - Strength score (0-4)
     */
    checkPasswordStrength: function(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        return strength;
    },

    /**
     * Show field error
     * @param {HTMLElement} field - Input field
     * @param {boolean} isValid - Is valid
     * @param {string} message - Error message
     */
    showFieldError: function(field, isValid, message) {
        // Remove existing error
        const existingError = field.parentElement.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }

        field.classList.remove('is-invalid', 'is-valid');

        if (!isValid) {
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentElement.appendChild(errorDiv);
        } else {
            field.classList.add('is-valid');
        }
    },

    /**
     * Update password strength indicator
     * @param {HTMLElement} field - Password field
     * @param {string} password - Password value
     */
    updatePasswordStrengthIndicator: function(field, password) {
        // Check if strength indicator exists
        let indicator = field.parentElement.querySelector('.password-strength');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'password-strength mt-2';
            field.parentElement.appendChild(indicator);
        }

        const strength = this.checkPasswordStrength(password);
        const colors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
        const labels = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];

        let html = '<div class="progress" style="height: 5px;">';
        for (let i = 0; i < 5; i++) {
            const active = i < strength ? 'active' : '';
            html += `<div class="progress-bar ${active}" style="width: 20%; background-color: ${colors[i]};"></div>`;
        }
        html += '</div>';
        html += `<small class="text-muted">Kekuatan: ${labels[strength]}</small>`;

        indicator.innerHTML = html;
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Make FormValidation available globally
    window.FormValidation = FormValidation;
    console.log('Form Validation Helper: Initialized successfully');
});
