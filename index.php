<?php
// pos_system/index.php

// Start the session at the very beginning of the script
session_start();

// Include necessary helper files
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/ui_components.php';

// This is the main entry point. It will redirect users based on their login status.

// If a user is not logged in, redirect them to the login page.
if (!isLoggedIn()) {
    redirectTo('auth.php');
} else {
    // If logged in, redirect to an appropriate page based on role or a default.
    // For simplicity, admins go to products, cashiers go to sales.
    if (hasRole('Admin')) {
        redirectTo('products.php');
    } else {
        redirectTo('sales.php');
    }
}

// This part of the code will generally not be reached if redirects work correctly.
// However, it's good practice to have a fallback or a simple message.
renderHeader('Welcome');
renderNavbar();
?>

<div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-xl text-center">
    <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Simple POS System!</h2>
    <p class="text-gray-600 mb-6">You should be redirected automatically. If not, please check your login status.</p>
    <p class="text-gray-600">
        If you are seeing this, there might be an issue with redirection or you are already logged in.
        Please use the navigation bar to proceed.
    </p>
</div>

<?php
renderFooter();
?>
