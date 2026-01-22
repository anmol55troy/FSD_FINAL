/**
 * Product Inventory System - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide flash messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Form validation enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                } else {
                    field.style.borderColor = 'var(--border-color)';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
    
    // Price validation
    const priceInputs = document.querySelectorAll('input[type="number"][step="0.01"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    });
    
    // Confirm delete with better UX
    const deleteLinks = document.querySelectorAll('.btn-delete');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const productName = this.closest('tr').querySelector('strong').textContent;
            
            if (confirm(`Are you sure you want to delete "${productName}"?\n\nThis action cannot be undone.`)) {
                window.location.href = this.href;
            }
        });
    });
    
    // Table row highlighting
    const tableRows = document.querySelectorAll('.product-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't highlight if clicking on action buttons
            if (!e.target.closest('.actions')) {
                this.style.backgroundColor = '#EFF6FF';
                setTimeout(() => {
                    this.style.backgroundColor = '';
                }, 500);
            }
        });
    });
    
    // Live character count for product name
    const productNameInput = document.getElementById('product_name');
    if (productNameInput) {
        const maxLength = productNameInput.getAttribute('maxlength');
        const helpText = productNameInput.nextElementSibling;
        
        if (helpText && helpText.classList.contains('form-help')) {
            productNameInput.addEventListener('input', function() {
                const remaining = maxLength - this.value.length;
                helpText.textContent = `${remaining} characters remaining`;
                
                if (remaining < 20) {
                    helpText.style.color = 'var(--warning-color)';
                } else {
                    helpText.style.color = 'var(--secondary-color)';
                }
            });
        }
    }
    
    // Number input validation
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });
    
});

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show loading indicator
 */
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'loading-overlay';
    loader.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loader);
}

/**
 * Hide loading indicator
 */
function hideLoading() {
    const loader = document.getElementById('loading-overlay');
    if (loader) {
        loader.remove();
    }
}