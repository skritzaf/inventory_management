<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "InventoryManagement");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch existing products
$products = [];
$result = $conn->query("SELECT * FROM Products");
if ($result->num_rows > 0) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission for adding a new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $productName = $_POST['product_name'];
    $category = $_POST['category'];
    $sku = $_POST['sku'];
    $supplier = $_POST['supplier'];
    $cost = $_POST['cost'];

    $stmt = $conn->prepare("INSERT INTO Products (product_name, category, sku, supplier, cost) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $productName, $category, $sku, $supplier, $cost);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!');</script>";
        header("Refresh:0");
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Handle deleting a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];

    $stmt = $conn->prepare("DELETE FROM Products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);

    if ($stmt->execute()) {
        echo "<script>alert('Product deleted successfully!');</script>";
        header("Refresh:0");
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Handle editing a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $category = $_POST['category'];
    $sku = $_POST['sku'];
    $supplier = $_POST['supplier'];
    $cost = $_POST['cost'];

    $stmt = $conn->prepare("UPDATE Products SET product_name = ?, category = ?, sku = ?, supplier = ?, cost = ? WHERE product_id = ?");
    $stmt->bind_param("ssssdi", $productName, $category, $sku, $supplier, $cost, $productId);

    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully!');</script>";
        header("Refresh:0");
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
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

        .products-table {
            flex: 1;
            margin-right: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th, .products-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .products-table th {
            background: #007BFF;
            color: white;
        }

        .add-product-form {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .add-product-form label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .add-product-form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .add-product-form button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-product-form button:hover {
            background-color: #0056b3;
        }

        <!--.back-btn {
            margin-top: 10px;
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Products Table -->
        <div class="products-table">
            <h2>Products List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th>Supplier</th>
                        <th>Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td><?= htmlspecialchars($product['sku']) ?></td>
                                <td><?= htmlspecialchars($product['supplier']) ?></td>
                                <td><?= htmlspecialchars(number_format($product['cost'], 2)) ?></td>
                                <td>
                                    <!-- Delete Button -->
                                    <form style="display: inline-block;" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                        <button type="submit" name="delete_product">Delete</button>
                                    </form>

                                    <!-- Edit Button -->
                                    <form style="display: inline-block;" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                        <button type="submit" name="edit_action">Edit</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No products available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

<!-- Add/Edit Product Form -->
<div class="add-product-form">
    <?php if (isset($_POST['edit_action']) && isset($_POST['product_id'])): ?>
        <?php
            $productId = intval($_POST['product_id']); // Secure the input
            $product = null;

            // Fetch the product details for editing
            $stmt = $conn->prepare("SELECT * FROM Products WHERE product_id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
            }
        ?>
        <?php if ($product): ?>
            <h2>Edit Product</h2>
            <form action="products.php" method="POST">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">

                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>

                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

                <label for="sku">SKU:</label>
                <input type="text" id="sku" name="sku" value="<?= htmlspecialchars($product['sku']) ?>" required>

                <label for="supplier">Supplier:</label>
                <input type="text" id="supplier" name="supplier" value="<?= htmlspecialchars($product['supplier']) ?>" required>

                <label for="cost">Cost:</label>
                <input type="number" step="0.01" id="cost" name="cost" value="<?= htmlspecialchars($product['cost']) ?>" required>

                <div class="button-group">
                    <button type="submit" name="edit_product">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='products.php'">Cancel</button>
                </div>
            </form>
        <?php else: ?>
            <p class="error">Error: Product not found.</p>
        <?php endif; ?>
    <?php else: ?>
        <h2>Add New Product</h2>
        <form action="products.php" method="POST">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required>

            <label for="sku">SKU:</label>
            <input type="text" id="sku" name="sku" required>

            <label for="supplier">Supplier:</label>
            <input type="text" id="supplier" name="supplier" required>

            <label for="cost">Cost:</label>
            <input type="number" step="0.01" id="cost" name="cost" required>

            <button type="submit" name="add_product">Add Product</button>
            <button class="back-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
        </form>
    <?php endif; ?>
</div>


