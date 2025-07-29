<?php
// pos_system/includes/helpers.php

/**
 * Generates a unique ID based on current timestamp and a random number.
 * This is a simple approach for unique IDs in a small, non-distributed system.
 * For larger systems, UUIDs or database auto-increments are preferred.
 *
 * @param string $prefix An optional prefix for the ID (e.g., "PROD", "SALE").
 * @return string A unique ID string.
 */
function generateUniqueId($prefix = '') {
    // Generate a unique ID using microtime and a random number
    // Replace dots in microtime with empty string to ensure a valid ID format
    $uniquePart = str_replace('.', '', microtime(true)) . mt_rand(1000, 9999);
    return strtoupper($prefix) . $uniquePart;
}

/**
 * Redirects the user to a specified URL.
 *
 * @param string $url The URL to redirect to.
 * @param int $statusCode The HTTP status code for the redirect (default: 302 Found).
 */
function redirectTo($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit(); // Always exit after a redirect to prevent further script execution
}

/**
 * Checks if a user is logged in.
 * This function assumes session_start() has been called.
 *
 * @return bool True if a user is logged in, false otherwise.
 */
function isLoggedIn() {
    // Check if 'user_id' is set in the session
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the logged-in user has a specific role.
 * This function assumes session_start() has been called and user role is stored in session.
 *
 * @param string $requiredRole The role to check against (e.g., 'Admin', 'Cashier').
 * @return bool True if the user has the required role, false otherwise.
 */
function hasRole($requiredRole) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $requiredRole;
}

/**
 * Displays a simple message (e.g., success, error) to the user.
 * This function can be expanded to use session flashes for messages across redirects.
 * For now, it just prints.
 *
 * @param string $message The message to display.
 * @param string $type The type of message (e.g., 'success', 'error', 'info', 'warning').
 * Used for styling with Tailwind CSS.
 */
function displayMessage($message, $type = 'info') {
    $bgColor = '';
    $textColor = '';
    switch ($type) {
        case 'success':
            $bgColor = 'bg-green-100';
            $textColor = 'text-green-800';
            break;
        case 'error':
            $bgColor = 'bg-red-100';
            $textColor = 'text-red-800';
            break;
        case 'warning':
            $bgColor = 'bg-yellow-100';
            $textColor = 'text-yellow-800';
            break;
        case 'info':
        default:
            $bgColor = 'bg-blue-100';
            $textColor = 'text-blue-800';
            break;
    }
    echo "<div class='p-4 mb-4 rounded-lg {$bgColor} {$textColor}' role='alert'>{$message}</div>";
}

// You might want to implement a flash message system using sessions for messages
// that persist across redirects.
/*
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']); // Clear the message after reading
        return $message;
    }
    return null;
}
*/

?>