

document.addEventListener('DOMContentLoaded', function() {
    
    // Number input validation
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });

    // Page-specific enhancements moved from inline scripts
    // 1) Autocomplete on search.php
    const keywordInput = document.getElementById('keyword');
    const autocompleteResults = document.getElementById('autocomplete-results');
    if (keywordInput && autocompleteResults) {
        const acFetch = debounce(term => {
            if (!term || term.length < 2) { autocompleteResults.innerHTML = ''; autocompleteResults.style.display = 'none'; return; }
            fetch(`search.php?autocomplete=1&term=${encodeURIComponent(term)}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length) {
                        autocompleteResults.innerHTML = data.map(i => `<div class="autocomplete-item" data-value="${i}">${i}</div>`).join('');
                        autocompleteResults.style.display = 'block';
                    } else {
                        autocompleteResults.innerHTML = '';
                        autocompleteResults.style.display = 'none';
                    }
                }).catch(() => { autocompleteResults.innerHTML = ''; autocompleteResults.style.display = 'none'; });
        }, 300);

        keywordInput.addEventListener('input', e => acFetch(e.target.value));
        autocompleteResults.addEventListener('click', e => {
            const it = e.target.closest('.autocomplete-item');
            if (it) { keywordInput.value = it.dataset.value; autocompleteResults.style.display = 'none'; }
        });
        document.addEventListener('click', e => { if (e.target !== keywordInput) autocompleteResults.style.display = 'none'; });
        window.resetSearch = () => { window.location.href = 'search.php'; };
    }

    // 2) Add/Edit forms numeric checks (moved from inline)
    const addForm = document.getElementById('addProductForm');
    if (addForm) addForm.addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value || 0);
        const quantity = parseInt(document.getElementById('quantity').value || 0, 10);
        if (price < 0 || quantity < 0) { e.preventDefault(); alert('Price and quantity must be non-negative numbers'); }
    });
    const editForm = document.getElementById('editProductForm');
    if (editForm) editForm.addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value || 0);
        const quantity = parseInt(document.getElementById('quantity').value || 0, 10);
        if (price < 0 || quantity < 0) { e.preventDefault(); alert('Price and quantity must be non-negative numbers'); }
    });

    // 3) Profile password match (moved from inline)
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordMatch = document.getElementById('password-match');
    if (newPasswordInput && confirmPasswordInput && passwordMatch) {
        const checkPasswordMatch = () => {
            if (confirmPasswordInput.value === '') { passwordMatch.textContent = ''; return; }
            if (newPasswordInput.value === confirmPasswordInput.value) {
                passwordMatch.textContent = '✓ Passwords match'; passwordMatch.style.color = 'var(--success-color)';
            } else {
                passwordMatch.textContent = '✗ Passwords do not match'; passwordMatch.style.color = 'var(--danger-color)';
            }
        };
        newPasswordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) passwordForm.addEventListener('submit', function(e) {
            if (newPasswordInput.value !== confirmPasswordInput.value) { e.preventDefault(); alert('Passwords do not match!'); }
        });
    }

    // 4) updateSort used in index.php
    window.updateSort = function() {
        const sortEl = document.getElementById('sortSelect');
        const orderEl = document.getElementById('orderSelect');
        if (!sortEl || !orderEl) return;
        const sort = sortEl.value; const order = orderEl.value;
        window.location.href = `index.php?sort=${sort}&order=${order}`;
    };
    
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