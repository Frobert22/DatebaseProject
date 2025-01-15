<?php
global $adminsCollection;
session_start();
require '../Core/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($email && $password) {
        $admin = $adminsCollection->findOne(['email' => $email]);
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = (string) $admin['_id'];
            header('Location: admin.php');
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Admin Login</h1>
<form method="POST" action="">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit" name="loginAdmin">Login</button>
</form>
</body>
</html>
