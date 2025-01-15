<?php
global $usersCollection;
session_start();
require '../Core/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: client_dashboard.php');
    die;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        try {
            $result = $usersCollection->insertOne([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
            $userId = $result->getInsertedId();
            echo "User successfully registered: " . $userId . "<br>";

            $_SESSION['user_id'] = (string) $userId;
            header('Location: ../views/client_dashboard.php');
            exit;
        } catch (Exception $e) {
            echo "Registration failed: " . $e->getMessage() . "<br>";
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
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Register</h1>
<form method="POST" action="">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>
    <br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit" name="registerUser">Register</button>
</form>
<a href="login.php">Already have an account? Login here</a>
</body>
</html>
