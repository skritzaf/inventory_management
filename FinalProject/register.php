<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "InventoryManagement");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check if the username already exists
    $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Username already exists!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO Users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            echo "Registration successful!";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <div class="login-container">
      <div class="login-card">
        <div class="login-logo">
          <img src="shopping-cart-icon.png" alt="Shopping Cart Icon" />
        </div>
        <h2 class="login-title">Register</h2>
        <form action="register.php" method="POST">
          <div class="input-group">
            <label for="username">
              <i class="fa fa-user"></i>
            </label>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Username"
              required
            />
          </div>
          <div class="input-group">
            <label for="password">
              <i class="fa fa-lock"></i>
            </label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Password"
              required
            />
          </div>
          <div class="input-group">
            <label for="role">
              <i class="fa fa-briefcase"></i>
            </label>
            <select id="role" name="role" required>
              <option value="admin">Admin</option>
              <option value="user">User</option>
            </select>
          </div>
          <button type="submit" class="login-button">Register</button>
          <a href="login.php" class="back-to-login">Back to Login</a>
        </form>
      </div>
    </div>
  </body>
</html>
