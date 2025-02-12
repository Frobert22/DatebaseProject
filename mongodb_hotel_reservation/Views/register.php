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
            $stmt = $conn->prepare("SELECT * FROM users WHERE name = ? OR email = ?");
            $stmt->bind_param('ss', $name, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                echo "User already exists with this email or name";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmtInsert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmtInsert->bind_param('sss', $name, $email, $hashedPassword);
                $stmtInsert->execute();
                $userId = $stmtInsert->insert_id;
                $stmtInsert->close();
                $_SESSION['user_id'] = $userId;
                $_SESSION['logged_user'] = $name;
                header('Location: client_dashboard.php');
                exit;
            }
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
