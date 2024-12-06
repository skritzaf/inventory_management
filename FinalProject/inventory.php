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

// Fetch existing inventory items
$inventoryQuery = $pdo->query("SELECT inventory.inventory_id, products.product_name, inventory.stock, inventory.reorder_threshold
                               FROM inventory
                               JOIN products ON inventory.product_id = products.product_id");
$inventoryItems = $inventoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch products for adding/editing inventory
$productQuery = $pdo->query("SELECT product_id, product_name FROM products");
$products = $productQuery->fetchAll(PDO::FETCH_ASSOC);

// Track edit action
$editInventory = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_action'])) {
    $inventoryId = $_POST['inventory_id'];
    $editInventoryQuery = $pdo->prepare("SELECT * FROM inventory WHERE inventory_id = ?");
    $editInventoryQuery->execute([$inventoryId]);
    $editInventory = $editInventoryQuery->fetch(PDO::FETCH_ASSOC);
}

// Handle editing an inventory item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_inventory'])) {
    $inventoryId = $_POST['inventory_id'];
    $productId = $_POST['product_id'];
    $stock = $_POST['stock'];
    $reorderThreshold = $_POST['reorder_threshold'];

    $stmt = $pdo->prepare("UPDATE inventory SET product_id = ?, stock = ?, reorder_threshold = ? WHERE inventory_id = ?");
    $stmt->execute([$productId, $stock, $reorderThreshold, $inventoryId]);
    header("Location: inventory.php");
    exit();
}

// Handle adding a new inventory item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_inventory'])) {
    $productId = $_POST['product_id'];
    $stock = $_POST['stock'];
    $reorderThreshold = $_POST['reorder_threshold'];

    $stmt = $pdo->prepare("INSERT INTO inventory (product_id, stock, reorder_threshold) VALUES (?, ?, ?)");
    $stmt->execute([$productId, $stock, $reorderThreshold]);
    header("Location: inventory.php");
    exit();
}

// Handle deleting an inventory item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_inventory'])) {
    $inventoryId = $_POST['inventory_id'];

    $stmt = $pdo->prepare("DELETE FROM inventory WHERE inventory_id = ?");
    $stmt->execute([$inventoryId]);
    header("Location: inventory.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
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

        .inventory-table {
            flex: 1;
            margin-right: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .inventory-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table th, .inventory-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .inventory-table th {
            background: #007BFF;
            color: white;
        }

        .add-inventory-form {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .add-inventory-form label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .add-inventory-form input,
        .add-inventory-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .add-inventory-form button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-inventory-form button:hover {
            background-color: #0056b3;
        }

        .add-inventory-form .button-group {
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
        <!-- Inventory Table -->
        <div class="inventory-table">
            <h2>Inventory List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Stock</th>
                        <th>Reorder Threshold</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventoryItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= htmlspecialchars($item['stock']) ?></td>
                            <td><?= htmlspecialchars($item['reorder_threshold']) ?></td>
                            <td>
                                <form style="display: inline-block;" method="POST">
                                    <input type="hidden" name="inventory_id" value="<?= $item['inventory_id'] ?>">
                                    <button type="submit" name="delete_inventory">Delete</button>
                                </form>
                                <form style="display: inline-block;" method="POST">
                                    <input type="hidden" name="inventory_id" value="<?= $item['inventory_id'] ?>">
                                    <button type="submit" name="edit_action">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Inventory Form -->
        <div class="add-inventory-form">
            <?php if ($editInventory): ?>
                <h2>Edit Inventory</h2>
                <form action="inventory.php" method="POST">
                    <input type="hidden" name="inventory_id" value="<?= htmlspecialchars($editInventory['inventory_id']) ?>">
                    
                    <label for="product_id">Product:</label>
                    <select id="product_id" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['product_id'] ?>" <?= $product['product_id'] == $editInventory['product_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['product_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($editInventory['stock']) ?>" required>
                    
                    <label for="reorder_threshold">Reorder Threshold:</label>
                    <input type="number" id="reorder_threshold" name="reorder_threshold" value="<?= htmlspecialchars($editInventory['reorder_threshold']) ?>" required>
                    
                    <div class="button-group">
                        <button type="submit" name="edit_inventory">Save Changes</button>
                        <!-- Cancel Button -->
                        <button type="button" class="cancel-btn" onclick="window.location.href='inventory.php'">Cancel</button>
                    </div>
                </form>
            <?php else: ?>
                <h2>Add New Inventory</h2>
                <form action="inventory.php" method="POST">
                    <label for="product_id">Product:</label>
                    <select id="product_id" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['product_id'] ?>"><?= htmlspecialchars($product['product_name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" required>

                    <label for="reorder_threshold">Reorder Threshold:</label>
                    <input type="number" id="reorder_threshold" name="reorder_threshold" required>

                    <button type="submit" name="add_inventory">Add Inventory</button>
                    <button class="back-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
                </form>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
