<?php
// pos_system/reports.php

// Start the session at the very beginning of the script
session_start();

// Include necessary helper files
require_once __DIR__ . '/includes/data_handler.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/ui_components.php';

// Define file paths for data storage
$salesFile = __DIR__ . '/data/sales.json';

// Check if user is logged in and has Admin role
if (!isLoggedIn() || !hasRole('Admin')) {
    redirectTo('auth.php'); // Redirect to login if not authorized
}

// Read all sales data
$sales = readJsonFile($salesFile);

// Filter sales based on date range (if provided)
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$filteredSales = [];
if (!empty($startDate) && !empty($endDate)) {
    $startDateTime = strtotime($startDate . ' 00:00:00');
    $endDateTime = strtotime($endDate . ' 23:59:59');

    foreach ($sales as $sale) {
        $saleDateTime = strtotime($sale['date']);
        if ($saleDateTime >= $startDateTime && $saleDateTime <= $endDateTime) {
            $filteredSales[] = $sale;
        }
    }
} else {
    $filteredSales = $sales; // If no date range, show all sales
}

// Calculate total sales amount for the filtered period
$totalSalesAmount = 0;
foreach ($filteredSales as $sale) {
    $totalSalesAmount += $sale['total_amount'];
}

// Sort sales by date in descending order (most recent first)
usort($filteredSales, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

renderHeader('Sales Reports');
renderNavbar();
?>

<div class="container mx-auto mt-8 p-6 bg-white rounded-lg shadow-xl">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Sales Reports</h2>

    <!-- Date Range Filter Form -->
    <div class="mb-8 p-6 border border-gray-200 rounded-lg shadow-sm bg-gray-50">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Filter Sales by Date</h3>
        <form action="reports.php" method="GET" class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
            <div>
                <label for="start_date" class="block text-gray-700 text-sm font-semibold mb-2">Start Date:</label>
                <input type="date" id="start_date" name="start_date"
                       value="<?php echo htmlspecialchars($startDate); ?>"
                       class="shadow-sm appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>
            <div>
                <label for="end_date" class="block text-gray-700 text-sm font-semibold mb-2">End Date:</label>
                <input type="date" id="end_date" name="end_date"
                       value="<?php echo htmlspecialchars($endDate); ?>"
                       class="shadow-sm appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>
            <div class="md:self-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md">
                    Apply Filter
                </button>
                <a href="reports.php"
                   class="ml-2 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105 shadow-md inline-block">
                    Clear Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Sales Summary -->
    <div class="mb-8 p-6 bg-blue-50 border border-blue-200 rounded-lg shadow-md text-center">
        <h3 class="text-2xl font-bold text-blue-800 mb-2">Total Sales Amount</h3>
        <p class="text-4xl font-extrabold text-blue-900">$<?php echo number_format($totalSalesAmount, 2); ?></p>
        <?php if (!empty($startDate) && !empty($endDate)): ?>
            <p class="text-blue-700 text-sm mt-2">For the period: <?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?></p>
        <?php else: ?>
            <p class="text-blue-700 text-sm mt-2">Showing total sales for all time.</p>
        <?php endif; ?>
    </div>

    <!-- Sales History Table -->
    <h3 class="text-2xl font-semibold text-gray-700 mb-4">Sales History</h3>
    <?php if (empty($filteredSales)): ?>
        <p class="text-gray-600 text-center py-4">No sales records found for the selected period.</p>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg shadow-md border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Sold</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($filteredSales as $sale): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sale['transaction_id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800"><?php echo htmlspecialchars($sale['date']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <ul class="list-disc list-inside">
                                    <?php foreach ($sale['items'] as $item): ?>
                                        <li><?php echo htmlspecialchars($item['name']); ?> (x<?php echo htmlspecialchars($item['quantity']); ?>) - $<?php echo number_format($item['price'], 2); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">$<?php echo number_format($sale['total_amount'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800"><?php echo htmlspecialchars($sale['payment_method']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800"><?php echo htmlspecialchars($sale['cashier_id']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
renderFooter();
?>