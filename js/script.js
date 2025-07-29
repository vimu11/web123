// pos_system/js/script.js

// This file is for general client-side JavaScript interactions.
// Specific page-related scripts (like the product edit form population)
// are currently embedded directly in their respective PHP files for simplicity
// and to keep the number of separate JS files minimal.

// You can add global JavaScript functionalities here, such as:
// - Dynamic form validation (beyond basic HTML5 validation)
// - AJAX requests (if you decide to implement them later for smoother UX)
// - Interactive UI elements (e.g., modals, dropdowns, tabs)
// - Keyboard shortcuts

// Example: A simple script to automatically focus on the product ID input
// on the sales page when the page loads.
document.addEventListener('DOMContentLoaded', function() {
    const productIdSearchInput = document.getElementById('product_id_search');
    if (productIdSearchInput) {
        productIdSearchInput.focus();
    }

    // Example: A more robust confirmation dialog for delete actions (replacing browser's confirm)
    // This would require a custom modal implementation in ui_components.php or directly in the page.
    /*
    document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default submission
            const message = this.getAttribute('onsubmit').match(/confirm\(['"]([^'"]+)['"]\)/);
            if (message && message[1]) {
                // Here you would trigger a custom modal with 'message[1]'
                // For now, we'll just log it to show the concept.
                console.log("Custom confirmation needed: " + message[1]);
                // If user confirms in custom modal:
                // this.submit(); // Programmatically submit the form
            } else {
                this.submit(); // If no specific message, just submit
            }
        });
    });
    */
});