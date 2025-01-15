 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1>Welcome to Our Hotel Booking System</h1>

<nav>
    <ul>
        <li><a href="register.php">Register</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="admin_login.php">Admin Login</a></li>
    </ul>
</nav>

<h2>Available Rooms</h2>
<table>
    <tr>
        <th>Room Number</th>
        <th>Type</th>
        <th>Price (EUR)</th>
        <th>Available From</th>
        <th>Available Until</th>
    </tr>
    <?php
    require '../Core/db.php';
    global$roomsCollection;
    $rooms = $roomsCollection->find(['status' => 'available']);
    foreach ($rooms as $room): ?>
        <tr>
            <td><?php echo $room['roomNumber']; ?></td>
            <td><?php echo $room['type']; ?></td>
            <td><?php echo $room['price']; ?> EUR</td>
            <td><?php echo $room['availabilityStart']; ?></td>
            <td><?php echo $room['availabilityEnd']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
