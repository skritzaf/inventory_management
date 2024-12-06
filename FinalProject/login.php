<?php
session_start();
$conn = new mysqli("localhost", "root", "", "InventoryManagement");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
        } else {
            echo "Invalid credentials!";
        }
    } else {
        echo "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <div class="login-container">
      <div class="login-card">
        <div class="login-logo">
          <img src="shopping-cart-icon.png" alt="Shopping Cart Icon" />
        </div>
        <h2 class="login-title">Login</h2>
        <form action="login.php" method="POST">
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
  <button type="submit" class="login-button">Login</button>
  <a href="register.php" class="register">Register</a>
</form>
      </div>
    </div>
  </body>
</html>
