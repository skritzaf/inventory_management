<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$host = 'localhost';
$db = 'inventorymanagement';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch the logged-in user's information
$userQuery = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$userQuery->execute([$_SESSION['user_id']]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

// Fetch product and inventory data
$productQuery = $pdo->query("SELECT product_name, category, sku, supplier, cost FROM products");
$products = $productQuery->fetchAll(PDO::FETCH_ASSOC);

$inventoryQuery = $pdo->query("SELECT p.product_name, p.category, i.stock, i.reorder_threshold, i.inventory_id
                                FROM inventory i
                                INNER JOIN products p ON i.product_id = p.product_id");
$inventory = $inventoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Low stock alert
$lowStockQuery = $pdo->query("SELECT p.product_name, p.category, i.stock 
                                FROM inventory i
                                INNER JOIN products p ON i.product_id = p.product_id
                                WHERE i.stock < i.reorder_threshold");
$lowStockAlerts = $lowStockQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch orders data
$orderQuery = $pdo->query("SELECT o.order_id, c.customer_name, o.order_date, o.total_amount
                            FROM orders o
                            INNER JOIN customers c ON o.customer_id = c.customer_id");
$orders = $orderQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="dashboardstyles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Welcome, <?= htmlspecialchars($user['username']) ?></h2>
        
        <!-- Buttons Group for Navigation -->
        <div class="button-group">
            <button class="products-btn" onclick="window.location.href='products.php'">Products</button>
            <button class="orders-btn" onclick="window.location.href='orders.php'">Orders</button>
            <button class="inventory-btn" onclick="window.location.href='inventory.php'">Inventory</button>
        </div>

        <!-- Log Out Button -->
        <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Inventory Dashboard</h1>
        <div class="container">
            <!-- Product List -->
            <div class="box">
                <h3>Product List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Supplier</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td><?= htmlspecialchars($product['sku']) ?></td>
                                <td><?= htmlspecialchars($product['supplier']) ?></td>
                                <td><?= htmlspecialchars($product['cost']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Inventory List -->
            <div class="box">
                <h3>Inventory In Stock</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Reorder Threshold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= htmlspecialchars($item['category']) ?></td>
                                <td><?= htmlspecialchars($item['stock']) ?></td>
                                <td><?= htmlspecialchars($item['reorder_threshold']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Low Stock Alerts -->
            <div class="box">
                <h3>Low Stock Alert!</h3>
                <?php if (count($lowStockAlerts) > 0): ?>
                    <div class="alert">
                        <?php foreach ($lowStockAlerts as $alert): ?>
                            <?= htmlspecialchars($alert['product_name']) ?> (Category: <?= htmlspecialchars($alert['category']) ?>): Only <?= htmlspecialchars($alert['stock']) ?> left!<br>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No low stock items.</p>
                <?php endif; ?>
            </div>

            <!-- Orders Table -->
            <div class="box">
                <h3>Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_id']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['order_date']) ?></td>
                                <td><?= htmlspecialchars($order['total_amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
