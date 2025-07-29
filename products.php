<?php
// pos_system/products.php

// Start the session at the very beginning of the script
session_start();

// Include necessary helper files
require_once __DIR__ . '/includes/data_handler.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/ui_components.php';

// Define file paths for data storage
$productsFile = __DIR__ . '/data/products.json';

// Check if user is logged in and has Admin role
if (!isLoggedIn() || !hasRole('Admin')) {
    redirectTo('auth.php'); // Redirect to login if not authorized
}

$message = '';
$messageType = '';

// Handle form submissions for Add, Edit, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $productId = trim($_POST['product_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $category = trim($_POST['category'] ?? '');

        if (empty($productId) || empty($name) || $price <= 0 || $stock < 0 || empty($category)) {
            $message = "All fields are required and price/stock must be valid numbers.";
            $messageType = 'error';
        } else {
            $products = readJsonFile($productsFile);

            if ($action === 'add') {
                // Check for duplicate product ID
                $exists = false;
                foreach ($products as $product) {
                    if ($product['id'] === $productId) {
                        $exists = true;
                        break;
                    }
                }

                if ($exists) {
                    $message = "Product ID already exists. Please use a unique ID.";
                    $messageType = 'error';
                } else {
                    $newProduct = [
                        'id' => $productId,
                        'name' => $name,
                        'price' => $price,
                        'stock' => $stock,
                        'category' => $category
                    ];
                    $products[] = $newProduct;
                    if (writeJsonFile($productsFile, $products)) {
                        $message = "Product '{$name}' added successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Failed to add product.";
                        $messageType = 'error';
                    }
                }
            } elseif ($action === 'edit') {
                $found = false;
                foreach ($products as &$product) { // Use reference to modify array in place
                    if ($product['id'] === $productId) {
                        $product['name'] = $name;
                        $product['price'] = $price;
                        $product['stock'] = $stock;
                        $product['category'] = $category;
                        $found = true;
                        break;
                    }
                }
                unset($product); // Break the reference

                if ($found) {
                    if (writeJsonFile($productsFile, $products)) {
                        $message = "Product '{$name}' updated successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Failed to update product.";
                        $messageType = 'error';
                    }
                } else {
                    $message = "Product not found for editing.";
                    $messageType = 'error';
                }
            }
        }
    } elseif ($action === 'delete') {
        $productIdToDelete = $_POST['product_id_to_delete'] ?? '';
        $products = readJsonFile($productsFile);
        $initialCount = count($products);
        $products = array_filter($products, function($product) use ($productIdToDelete) {
            return $product['id'] !== $productIdToDelete;
        });
        // Re-index array after filtering
        $products = array_values($products);

        if (count($products) < $initialCount) {
            if (writeJsonFile($productsFile, $products)) {
                $message = "Product '{$productIdToDelete}' deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to delete product.";
                $messageType = 'error';
            }
        } else {
            $message = "Product '{$productIdToDelete}' not found for deletion.";
            $messageType = 'error';
        }
    }
}

// Read products data for display
$products = readJsonFile($productsFile);

renderHeader('Product Management');
renderNavbar();
?>

<div class="container mx-auto mt-8 p-6 bg-white rounded-lg shadow-xl">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Product Management</h2>

    <?php
    // Display messages
    if (!empty($message)) {
        displayMessage($message, $messageType);
    }
    ?>

    <!-- Add/Edit Product Form -->
    <div class="mb-10 p-6 border border-gray-200 rounded-lg shadow-sm bg-gray-50">
        <h3 class="text-2xl font-semibold text-gray-700 mb-4">Add/Edit Product</h3>
        <form id="productForm" action="products.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="hidden" name="action" id="formAction" value="add">

            <div>
                <label for="product_id" class="block text-gray-700 text-sm font-semibold mb-2">Product ID:</label>
                <input type="text" id="product_id" name="product_id" required
                       class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                       placeholder="e.g., P001" oninput="this.value = this.value.toUpperCase()">
            </div>
            <div>
                <label for="name" class="block text-gray-700 text-sm font-semibold mb-2">Product Name:</label>
                <input type="text" id="name" name="name" required
                       class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                       placeholder="e.g., Apple (1kg)">
            </div>
            <div>
                <label for="price" class="block text-gray-700 text-sm font-semibold mb-2">Price:</label>
                <input type="number" id="price" name="price" step="0.01" min="0.01" required
                       class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                       placeholder="e.g., 1.99">
            </div>
            <div>
                <label for="stock" class="block text-gray-700 text-sm font-semibold mb-2">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" min="0" required
                       class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                       placeholder="e.g., 100">
            </div>
            <div>
                <label for="category" class="block text-gray-700 text-sm font-semibold mb-2">Category:</label>
                <input type="text" id="category" name="category" required
                       class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                       placeholder="e.g., Fruits, Vegetables">
            </div>
            <div class="md:col-span-2 flex justify-end space-x-4 mt-4">
                <button type="submit" id="submitButton"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md">
                    Add Product
                </button>
                <button type="button" id="clearFormButton"
                        class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md">
                    Clear Form
                </button>
            </div>
        </form>
    </div>

    <!-- Product List -->
    <h3 class="text-2xl font-semibold text-gray-700 mb-4">Existing Products</h3>
    <?php if (empty($products)): ?>
        <p class="text-gray-600 text-center py-4">No products added yet. Use the form above to add your first product!</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($products as $product): ?>
                        <tr class="<?php echo ($product['stock'] < 10) ? 'bg-red-50' : ''; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">$<?php echo number_format($product['price'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo ($product['stock'] < 10) ? 'text-red-600 font-semibold' : 'text-gray-800'; ?>">
                                <?php echo htmlspecialchars($product['stock']); ?>
                                <?php if ($product['stock'] < 10): ?>
                                    <span class="text-xs text-red-500 ml-1">(Low Stock!)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800"><?php echo htmlspecialchars($product['category']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="editProduct('<?php echo htmlspecialchars(json_encode($product)); ?>')"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3 px-3 py-1 rounded-md border border-indigo-500 hover:bg-indigo-50 transition-colors duration-200">
                                    Edit
                                </button>
                                <form action="products.php" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id_to_delete" value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md border border-red-500 hover:bg-red-50 transition-colors duration-200">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    // JavaScript for handling edit form population and clearing
    function editProduct(productJson) {
        const product = JSON.parse(productJson);
        document.getElementById('product_id').value = product.id;
        document.getElementById('name').value = product.name;
        document.getElementById('price').value = product.price;
        document.getElementById('stock').value = product.stock;
        document.getElementById('category').value = product.category;

        // Change form action to 'edit' and button text
        document.getElementById('formAction').value = 'edit';
        document.getElementById('submitButton').textContent = 'Update Product';
        document.getElementById('submitButton').classList.remove('bg-blue-600', 'hover:bg-blue-700');
        document.getElementById('submitButton').classList.add('bg-green-600', 'hover:bg-green-700');

        // Make product ID read-only during edit
        document.getElementById('product_id').readOnly = true;
        document.getElementById('product_id').classList.add('bg-gray-200', 'cursor-not-allowed');

        // Scroll to the form
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.getElementById('clearFormButton').addEventListener('click', function() {
        document.getElementById('productForm').reset();
        document.getElementById('formAction').value = 'add';
        document.getElementById('submitButton').textContent = 'Add Product';
        document.getElementById('submitButton').classList.remove('bg-green-600', 'hover:bg-green-700');
        document.getElementById('submitButton').classList.add('bg-blue-600', 'hover:bg-blue-700');

        // Make product ID editable again
        document.getElementById('product_id').readOnly = false;
        document.getElementById('product_id').classList.remove('bg-gray-200', 'cursor-not-allowed');
    });
</script>

<?php
renderFooter();
?>