<?php
// pos_system/auth.php

// Start the session at the very beginning of the script
session_start();

// Include necessary helper files
require_once __DIR__ . '/includes/data_handler.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/ui_components.php'; // For rendering UI elements like header/footer

// Define file paths for data storage
$usersFile = __DIR__ . '/data/users.json';

// Handle login and logout actions
$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    // Clear all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    redirectTo('auth.php');
}

// Handle POST request for login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // Do not trim password as it might contain leading/trailing spaces

    if (empty($username) || empty($password)) {
        $errorMessage = "Username and password are required.";
    } else {
        $users = readJsonFile($usersFile);
        $authenticatedUser = null;

        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password_hash'])) {
                $authenticatedUser = $user;
                break;
            }
        }

        if ($authenticatedUser) {
            // Set session variables upon successful login
            $_SESSION['user_id'] = $authenticatedUser['username']; // Using username as ID for simplicity
            $_SESSION['username'] = $authenticatedUser['username'];
            $_SESSION['user_role'] = $authenticatedUser['role'];

            // Redirect based on role or to a default page
            if ($authenticatedUser['role'] === 'Admin') {
                redirectTo('products.php'); // Admins go to product management
            } else {
                redirectTo('sales.php');    // Cashiers go to sales page
            }
        } else {
            $errorMessage = "Invalid username or password.";
        }
    }
}

// Render the login page HTML
renderHeader('Login');
renderNavbar(); // Navbar will show 'Login' button if not logged in

?>

<div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-xl max-w-md">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Login to POS System</h2>

    <?php
    // Display error message if any
    if (isset($errorMessage)) {
        displayMessage($errorMessage, 'error');
    }
    ?>

    <form action="auth.php" method="POST" class="space-y-6">
        <div>
            <label for="username" class="block text-gray-700 text-sm font-semibold mb-2">Username:</label>
            <input type="text" id="username" name="username" required
                   class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Enter your username">
        </div>
        <div>
            <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password:</label>
            <input type="password" id="password" name="password" required
                   class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Enter your password">
        </div>
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md">
            Login
        </button>
    </form>
</div>

<?php
renderFooter();
?>