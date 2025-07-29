<?php
// pos_system/includes/data_handler.php

/**
 * Reads data from a JSON file.
 *
 * @param string $filename The path to the JSON file.
 * @return array An associative array of data, or an empty array if the file doesn't exist or is invalid.
 */
function readJsonFile($filename) {
    if (!file_exists($filename)) {
        // If the file doesn't exist, return an empty array.
        // This is useful for initial setup where files might not be created yet.
        return [];
    }

    $fileContent = file_get_contents($filename);
    if ($fileContent === false) {
        // Error reading file
        error_log("Error reading file: " . $filename);
        return [];
    }

    $data = json_decode($fileContent, true); // Decode as associative array
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Error decoding JSON
        error_log("Error decoding JSON from file: " . $filename . " - " . json_last_error_msg());
        return [];
    }

    return $data;
}

/**
 * Writes data to a JSON file.
 *
 * @param string $filename The path to the JSON file.
 * @param array $data The data to write.
 * @return bool True on success, false on failure.
 */
function writeJsonFile($filename, $data) {
    // Ensure the directory exists
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create directory: " . $dir);
            return false;
        }
    }

    $jsonContent = json_encode($data, JSON_PRETTY_PRINT); // Pretty print for readability
    if ($jsonContent === false) {
        // Error encoding JSON
        error_log("Error encoding JSON data for file: " . $filename . " - " . json_last_error_msg());
        return false;
    }

    // Use FILE_APPEND if you want to add to existing content, but for data files,
    // usually you overwrite the entire file with the updated array.
    // Use LOCK_EX to prevent race conditions during writing.
    if (file_put_contents($filename, $jsonContent, LOCK_EX) === false) {
        error_log("Error writing to file: " . $filename);
        return false;
    }

    return true;
}

// Example usage (for testing purposes, remove in production)
/*
$testProductsFile = __DIR__ . '/../data/products.json';
$testUsersFile = __DIR__ . '/../data/users.json';

// Test writing initial product data
$initialProducts = [
    ["id" => "PROD001", "name" => "Milk (1L)", "price" => 2.50, "stock" => 50, "category" => "Dairy"],
    ["id" => "PROD002", "name" => "Bread (Whole Wheat)", "price" => 3.00, "stock" => 30, "category" => "Bakery"]
];
if (writeJsonFile($testProductsFile, $initialProducts)) {
    echo "Initial products written successfully.\n";
} else {
    echo "Failed to write initial products.\n";
}

// Test reading product data
$products = readJsonFile($testProductsFile);
echo "Products loaded:\n";
print_r($products);

// Test writing initial user data (password should be hashed in a real scenario)
$initialUsers = [
    ["username" => "admin", "password_hash" => password_hash("adminpass", PASSWORD_DEFAULT), "role" => "Admin"],
    ["username" => "cashier1", "password_hash" => password_hash("cashierpass", PASSWORD_DEFAULT), "role" => "Cashier"]
];
if (writeJsonFile($testUsersFile, $initialUsers)) {
    echo "Initial users written successfully.\n";
} else {
    echo "Failed to write initial users.\n";
}

// Test reading user data
$users = readJsonFile($testUsersFile);
echo "Users loaded:\n";
print_r($users);
*/
?>