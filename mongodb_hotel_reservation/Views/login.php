<?php
global $usersCollection;
session_start();
require '../Core/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: client_dashboard.php');
    die;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['logged_user'] = $user['name'];
            header('Location: client_dashboard.php');
            exit;
        } else {
            echo "Invalid email or password<br>";
        }
    } else {
        echo "Please fill in all fields!<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Login</h1>
<form method="POST" action="">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit" name="loginUser">Login</button>
</form>
<a href="register.php">Don't have an account? Register here</a>
</body>
</html>
