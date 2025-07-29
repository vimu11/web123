// Define file paths for data storage
$productsFile = __DIR__ . '/data/products.json';
$salesFile = __DIR__ . '/data/sales.json';

// Check if user is logged in (Cashier or Admin can access sales)
if (!isLoggedIn()) {
    redirectTo('auth.php'); // Redirect to login if not authorized
}

$message = '';
$messageType = '';

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get all products for searching
$allProducts = readJsonFile($productsFile);

// Handle POST requests for sales actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_to_cart') {
        $productId = trim($_POST['product_id'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 1);

        if (empty($productId) || $quantity <= 0) {
            $message = "Please enter a valid Product ID and quantity.";
            $messageType = 'error';
        } else {
            $foundProduct = null;
            foreach ($allProducts as $product) {
                if ($product['id'] === $productId) {
                    $foundProduct = $product;
                    break;
                }
            }

            if ($foundProduct) {
                // Check if enough stock is available
                $currentCartQuantity = $_SESSION['cart'][$productId]['quantity'] ?? 0;
                if (($currentCartQuantity + $quantity) > $foundProduct['stock']) {
                    $message = "Not enough stock for {$foundProduct['name']}. Available: {$foundProduct['stock']}. In cart: {$currentCartQuantity}.";
                    $messageType = 'warning';
                } else {
                    if (isset($_SESSION['cart'][$productId])) {
                        // Update quantity if product already in cart
                        $_SESSION['cart'][$productId]['quantity'] += $quantity;
                        $message = "Quantity for '{$foundProduct['name']}' updated in cart.";
                    } else {
                        // Add new product to cart
                        $_SESSION['cart'][$productId] = [
                            'id' => $foundProduct['id'],
                            'name' => $foundProduct['name'],
                            'price' => $foundProduct['price'],
                            'quantity' => $quantity
                        ];
                        $message = "'{$foundProduct['name']}' added to cart.";
                    }
                    $messageType = 'success';
                }
            } else {
                $message = "Product with ID '{$productId}' not found.";
                $messageType = 'error';
            }
        }
    } elseif ($action === 'update_cart_quantity') {
        $productId = trim($_POST['product_id'] ?? '');
        $newQuantity = intval($_POST['new_quantity'] ?? 0);

        if (isset($_SESSION['cart'][$productId])) {
            if ($newQuantity <= 0) {
                unset($_SESSION['cart'][$productId]); // Remove if quantity is 0 or less
                $message = "Product removed from cart.";
                $messageType = 'info';
            } else {
                $foundProduct = null;
                foreach ($allProducts as $product) {
                    if ($product['id'] === $productId) {
                        $foundProduct = $product;
                        break;
                    }
                }

                if ($foundProduct && $newQuantity > $foundProduct['stock']) {
                    $message = "Cannot update quantity for {$foundProduct['name']}. Only {$foundProduct['stock']} available.";
                    $messageType = 'warning';
                } else {
                    $_SESSION['cart'][$productId]['quantity'] = $newQuantity;
                    $message = "Quantity for '{$_SESSION['cart'][$productId]['name']}' updated to {$newQuantity}.";
                    $messageType = 'success';
                }
            }
        } else {
            $message = "Product not found in cart.";
            $messageType = 'error';
        }
    } elseif ($action === 'remove_from_cart') {
        $productId = trim($_POST['product_id'] ?? '');
        if (isset($_SESSION['cart'][$productId])) {
            $productName = $_SESSION['cart'][$productId]['name'];
            unset($_SESSION['cart'][$productId]);
            $message = "'{$productName}' removed from cart.";
            $messageType = 'info';
        } else {
            $message = "Product not found in cart to remove.";
            $messageType = 'error';
        }
    } elseif ($action === 'clear_cart') {
        $_SESSION['cart'] = [];
        $message = "Cart cleared successfully.";
        $messageType = 'info';
    } elseif ($action === 'complete_sale') {
        if (empty($_SESSION['cart'])) {
            $message = "Cannot complete sale: Cart is empty.";
            $messageType = 'error';
        } else {
            $sales = readJsonFile($salesFile);
            $productsToUpdate = readJsonFile($productsFile);

            $transactionId = generateUniqueId('SALE');
            $totalAmount = 0;
            $saleItems = [];

            // Calculate total and prepare sale items
            foreach ($_SESSION['cart'] as $cartItem) {
                $itemTotal = $cartItem['price'] * $cartItem['quantity'];
                $totalAmount += $itemTotal;
                $saleItems[] = [
                    'product_id' => $cartItem['id'],
                    'name' => $cartItem['name'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'item_total' => $itemTotal // Add item total for easier reporting
                ];

                // Deduct stock from products
                foreach ($productsToUpdate as &$product) {
                    if ($product['id'] === $cartItem['id']) {
                        $product['stock'] -= $cartItem['quantity'];
                        break;
                    }
                }
                unset($product); // Break the reference
            }

            // Create new sale record
            $newSale = [
                'transaction_id' => $transactionId,
                'date' => date('Y-m-d H:i:s'),
                'items' => $saleItems,
                'total_amount' => $totalAmount,
                'payment_method' => $_POST['payment_method'] ?? 'Cash', // Get payment method from form
                'cashier_id' => $_SESSION['user_id'] ?? 'Guest'
            ];

            $sales[] = $newSale;

            // Write updated products and sales data
            if (writeJsonFile($salesFile, $sales) && writeJsonFile($productsFile, $productsToUpdate)) {
                $message = "Sale completed successfully! Transaction ID: {$transactionId}. Total: $" . number_format($totalAmount, 2);
                $messageType = 'success';
                $_SESSION['cart'] = []; // Clear cart after successful sale
            } else {
                $message = "Failed to complete sale. Please try again.";
                $messageType = 'error';
            }
        }
    }
}

// Calculate cart totals for display
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += ($item['price'] * $item['quantity']);
}

renderHeader('New Sale');
renderNavbar();
?>

<div class="container mx-auto mt-8 p-6 bg-white rounded-lg shadow-xl grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Product Search and Add to Cart -->
    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">New Sale</h2>
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Add Product to Cart</h3>

        <?php
        // Display messages
        if (!empty($message)) {
            displayMessage($message, $messageType);
        }
        ?>

        <form action="sales.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add_to_cart">
            <div>
                <label for="product_id_search" class="block text-gray-700 text-sm font-semibold mb-2">Product ID or Name:</label>
                <input type="text" id="product_id_search" name="product_id" required list="product-suggestions"
                       class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                       placeholder="Enter product ID or name">
                <datalist id="product-suggestions">
                    <?php foreach ($allProducts as $product): ?>
                        <option value="<?php echo htmlspecialchars($product['id']); ?>"><?php echo htmlspecialchars($product['name']); ?> (Stock: <?php echo htmlspecialchars($product['stock']); ?>)</option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div>
                <label for="quantity" class="block text-gray-700 text-sm font-semibold mb-2">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" required
                       class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md">
                Add to Cart
            </button>
        </form>
    </div>

    <!-- Shopping Cart Display -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Shopping Cart</h3>

        <?php if (empty($_SESSION['cart'])): ?>
            <p class="text-gray-600 text-center py-4">Your cart is empty. Add products to start a sale.</p>
        <?php else: ?>
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200 mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">$<?php echo number_format($item['price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    <form action="sales.php" method="POST" class="flex items-center space-x-2">
                                        <input type="hidden" name="action" value="update_cart_quantity">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <input type="number" name="new_quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1"
                                               class="w-20 shadow-sm border rounded-lg py-1 px-2 text-center text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-400">
                                        <button type="submit"
                                                class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded-md transition-colors duration-200">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="sales.php" method="POST" class="inline-block">
                                        <input type="hidden" name="action" value="remove_from_cart">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md border border-red-500 hover:bg-red-50 transition-colors duration-200">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center text-xl font-bold text-gray-800 mb-6">
                <span>Cart Total:</span>
                <span>$<?php echo number_format($cartTotal, 2); ?></span>
            </div>

            <div class="flex justify-end space-x-4">
                <form action="sales.php" method="POST" class="inline-block">
                    <input type="hidden" name="action" value="clear_cart">
                    <button type="submit"
                            class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md">
                        Clear Cart
                    </button>
                </form>
                <form action="sales.php" method="POST" class="inline-block">
                    <input type="hidden" name="action" value="complete_sale">
                    <select name="payment_method" class="border rounded-lg py-2 px-3 text-gray-700 mr-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                    </select>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md">
                        Complete Sale
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
renderFooter();
?>