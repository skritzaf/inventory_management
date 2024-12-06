<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
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

// Fetch existing orders with customer names
$orderQuery = $pdo->query("SELECT orders.order_id, customers.customer_name, orders.order_date, orders.total_amount
                           FROM orders
                           JOIN customers ON orders.customer_id = customers.customer_id");
$orders = $orderQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch customers for order creation and editing
$customerQuery = $pdo->query("SELECT customer_id, customer_name FROM customers");
$customers = $customerQuery->fetchAll(PDO::FETCH_ASSOC);

// Track edit action
$editOrder = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_action'])) {
    $orderId = $_POST['order_id'];
    $editOrderQuery = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $editOrderQuery->execute([$orderId]);
    $editOrder = $editOrderQuery->fetch(PDO::FETCH_ASSOC);
}

// Handle editing an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    $orderId = $_POST['order_id'];
    $customerId = $_POST['customer_id'];
    $orderDate = $_POST['order_date'];
    $totalAmount = $_POST['total_amount'];

    $stmt = $pdo->prepare("UPDATE orders SET customer_id = ?, order_date = ?, total_amount = ? WHERE order_id = ?");
    $stmt->execute([$customerId, $orderDate, $totalAmount, $orderId]);
    header("Location: orders.php");
    exit();
}

// Handle adding a new order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_order'])) {
    $customerId = $_POST['customer_id'];
    $orderDate = $_POST['order_date'];
    $totalAmount = $_POST['total_amount'];

    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, order_date, total_amount) VALUES (?, ?, ?)");
    $stmt->execute([$customerId, $orderDate, $totalAmount]);
    header("Location: orders.php");
    exit();
}

// Handle adding a new customer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $customerName = $_POST['customer_name'];
    $stmt = $pdo->prepare("INSERT INTO customers (customer_name) VALUES (?)");
    $stmt->execute([$customerName]);
    header("Location: orders.php");
    exit();
}

// Handle deleting an order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order'])) {
    $orderId = $_POST['order_id'];

    $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            flex-direction: row;
            margin: 20px;
        }

        .orders-table {
            flex: 1;
            margin-right: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .orders-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th, .orders-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .orders-table th {
            background: #007BFF;
            color: white;
        }

        .add-order-form {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .add-order-form label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .add-order-form input,
        .add-order-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .add-order-form button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-order-form button:hover {
            background-color: #0056b3;
        }

        .add-order-form .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .back-btn {
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Orders Table -->
        <div class="orders-table">
            <h2>Orders List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td><?= htmlspecialchars($order['total_amount']) ?></td>
                            <td>
                                <form style="display: inline-block;" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <button type="submit" name="delete_order">Delete</button>
                                </form>
                                <form style="display: inline-block;" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <button type="submit" name="edit_action">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
                <!-- Add New Customer -->
                <div class="add-order-form">
            <h2>Add New Customer</h2>
            <form action="orders.php" method="POST">
                <label for="customer_name">Customer Name:</label>
                <input type="text" id="customer_name" name="customer_name" required>

                <button type="submit" name="add_customer">Add Customer</button>
            </form>
        </div>

 <!-- Add/Edit Order Form -->
<div class="add-order-form">
    <?php if ($editOrder): ?>
        <h2>Edit Order</h2>
        <form action="orders.php" method="POST">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($editOrder['order_id']) ?>">
            
            <label for="customer_id">Customer:</label>
            <select id="customer_id" name="customer_id" required>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['customer_id'] ?>" <?= $customer['customer_id'] == $editOrder['customer_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($customer['customer_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="order_date">Order Date:</label>
            <input type="date" id="order_date" name="order_date" value="<?= htmlspecialchars($editOrder['order_date']) ?>" required>
            
            <label for="total_amount">Total Amount:</label>
            <input type="number" step="0.01" id="total_amount" name="total_amount" value="<?= htmlspecialchars($editOrder['total_amount']) ?>" required>
            
            <div class="button-group">
                <button type="submit" name="edit_order">Save Changes</button>
                <!-- Cancel Button -->
                <button type="button" class="cancel-btn" onclick="window.location.href='orders.php'">Cancel</button>
            </div>
        </form>
    <?php else: ?>
        <h2>Add New Order</h2>
        <form action="orders.php" method="POST">
            <label for="customer_id">Customer:</label>
            <select id="customer_id" name="customer_id" required>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['customer_id'] ?>"><?= htmlspecialchars($customer['customer_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="order_date">Order Date:</label>
            <input type="date" id="order_date" name="order_date" required>

            <label for="total_amount">Total Amount:</label>
            <input type="number" step="0.01" id="total_amount" name="total_amount" required>

            <button type="submit" name="add_order">Add Order</button>
            <button class="back-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
        </form>
    <?php endif; ?>
</div>

    </div>

</body>
</html>
