<?php
// pos_system/includes/ui_components.php

// Ensure session is started for user role checks if this file is included before session_start()
// In index.php, we will ensure session_start() is called first.

/**
 * Renders the HTML header for the application.
 * Includes Tailwind CSS CDN and basic meta tags.
 *
 * @param string $title The title for the HTML page.
 */
if (!function_exists('renderHeader')) {
    function renderHeader($title = 'POS System') {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($title); ?> | Simple POS</title>
            <!-- Tailwind CSS CDN -->
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Inter', sans-serif;
                    background-color: #f3f4f6; /* Light gray background */
                }
                .container {
                    max-width: 1200px;
                }
                /* Custom scrollbar for better UX */
                ::-webkit-scrollbar {
                    width: 8px;
                    height: 8px;
                }
                ::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }
                ::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 10px;
                }
                ::-webkit-scrollbar-thumb:hover {
                    background: #555;
                }
            </style>
        </head>
        <body class="min-h-screen flex flex-col">
            <div class="flex-grow">
        <?php
    }
}

/**
 * Renders the navigation bar.
 * Displays different links based on user login status and role.
 */
if (!function_exists('renderNavbar')) {
    function renderNavbar() {
        ?>
        <nav class="bg-gradient-to-r from-blue-600 to-indigo-700 p-4 shadow-lg">
            <div class="container mx-auto flex justify-between items-center">
                <a href="index.php" class="text-white text-2xl font-bold rounded-md px-3 py-1 hover:bg-blue-700 transition-colors duration-300">
                    Simple POS
                </a>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('Admin')): ?>
                            <a href="products.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md transition-colors duration-300">
                                Product Management
                            </a>
                            <a href="reports.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md transition-colors duration-300">
                                Reports
                            </a>
                        <?php endif; ?>
                        <a href="sales.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md transition-colors duration-300">
                            New Sale
                        </a>
                        <span class="text-white text-sm">
                            Logged in as: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?> (<?php echo htmlspecialchars($_SESSION['user_role'] ?? 'N/A'); ?>)
                        </span>
                        <a href="auth.php?action=logout" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg shadow-md transition-colors duration-300">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="auth.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow-md transition-colors duration-300">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <?php
    }
}

/**
 * Renders the HTML footer for the application.
 */
if (!function_exists('renderFooter')) {
    function renderFooter() {
        ?>
            </div> <!-- End of flex-grow div -->
            <footer class="bg-gray-800 text-white p-4 text-center mt-8 shadow-inner">
                <div class="container mx-auto">
                    &copy; <?php echo date('Y'); ?> Simple POS System. All rights reserved.
                </div>
            </footer>
        </body>
        </html>
        <?php
    }
}
?>