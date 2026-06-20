// ===========================
// Close Alert Messages
// ===========================
document.addEventListener('DOMContentLoaded', function() {
    const alertCloseButtons = document.querySelectorAll('.alert-close');
    alertCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            alert.style.display = 'none';
            // Optional: Add fade-out animation
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        });
    });
});

// ===========================
// Confirm Before Delete
// ===========================
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// ===========================
// Format Currency
// ===========================
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

// ===========================
// Format Date
// ===========================
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// ===========================
// Toggle Sidebar (Mobile)
// ===========================
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
    }
}

// ===========================
// Table Row Selection
// ===========================
const tableCheckboxes = document.querySelectorAll('table input[type="checkbox"]');
const selectAllCheckbox = document.querySelector('table thead input[type="checkbox"]');

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        tableCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
}

tableCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allChecked = Array.from(tableCheckboxes).every(cb => cb.checked);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allChecked;
        }
    });
});

// ===========================
// Form Validation
// ===========================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const inputs = form.querySelectorAll('[required]');

    inputs.forEach(input => {
        if (input.value.trim() === '') {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
}

// ===========================
// AJAX Request Helper
// ===========================
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    const response = await fetch(url, {
        ...defaultOptions,
        ...options,
    });

    return response.json();
}

// ===========================
// Copy to Clipboard
// ===========================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

// ===========================
// Debounce Function
// ===========================
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
}

// ===========================
// Search Filter
// ===========================
const searchInputs = document.querySelectorAll('[data-search]');
searchInputs.forEach(input => {
    input.addEventListener('keyup', debounce(function() {
        const searchTerm = this.value.toLowerCase();
        const targetSelector = this.getAttribute('data-search');
        const items = document.querySelectorAll(targetSelector);

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }, 300));
});

console.log('SMIS Application Loaded');
