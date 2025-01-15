<?php
session_start();
require '../Core/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$roomsCollection = $client->hotel_booking_system->rooms;
$reservationsCollection = $client->hotel_booking_system->reservations;
$usersCollection = $client->hotel_booking_system->users;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addRoom'])) {
    $roomNumber = $_POST['roomNumber'] ?? null;
    $type = $_POST['type'] ?? null;
    $price = $_POST['price'] ?? null;
    $availabilityStart = $_POST['availabilityStart'] ?? null;
    $availabilityEnd = $_POST['availabilityEnd'] ?? null;

    if ($roomNumber && $type && $price && $availabilityStart && $availabilityEnd) {
        $existingRoom = $roomsCollection->findOne(['roomNumber' => $roomNumber]);
        if ($existingRoom) {
            echo "Room with roomNumber {$roomNumber} already exists!<br>";
        } else {
            $result = $roomsCollection->insertOne([
                'roomNumber' => $roomNumber,
                'type' => $type,
                'price' => $price,
                'availabilityStart' => $availabilityStart,
                'availabilityEnd' => $availabilityEnd,
                'status' => 'available',
                'reservations' => [],
            ]);
            echo "Room successfully added: " . $result->getInsertedId() . "<br>";
        }
    } else {
        echo "Please fill in all fields!<br>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteRoom'])) {
    $roomNumber = $_POST['roomNumberToDelete'] ?? null;

    if ($roomNumber) {
        $result = $roomsCollection->deleteOne(['roomNumber' => $roomNumber]);
        echo "Room successfully deleted: " . $result->getDeletedCount() . "<br>";
    } else {
        echo "Please select a room to delete!<br>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Admin Panel</h1>

<h2>Add Room</h2>
<form method="POST" action="">
    <label for="roomNumber">Room Number:</label>
    <input type="text" id="roomNumber" name="roomNumber" required>
    <br>
    <label for="type">Type:</label>
    <input type="text" id="type" name="type" required>
    <br>
    <label for="price">Price:</label>
    <input type="number" id="price" name="price" required>
    <br>
    <label for="availabilityStart">Available From:</label>
    <input type="date" id="availabilityStart" name="availabilityStart" required>
    <br>
    <label for="availabilityEnd">Available Until:</label>
    <input type="date" id="availabilityEnd" name="availabilityEnd" required>
    <br>
    <button type="submit" name="addRoom">Add Room</button>
</form>

<h2>Delete Room</h2>
<form method="POST" action="">
    <label for="roomNumberToDelete">Select Room to Delete:</label>
    <select id="roomNumberToDelete" name="roomNumberToDelete" required>
        <option value="" disabled selected>Select a room</option>
        <?php
        $rooms = $roomsCollection->find();
        foreach ($rooms as $room) {
            echo "<option value=\"{$room['roomNumber']}\">Room Number: {$room['roomNumber']}, Type: {$room['type']}, Price: \${$room['price']}</option>";
        }
        ?>
    </select>
    <br>
    <button type="submit" name="deleteRoom">Delete Room</button>
</form>

<h2>Available Rooms</h2>
<ul>
    <?php
    $rooms = $roomsCollection->find(['status' => 'available']);
    foreach ($rooms as $room): ?>
        <li>Room Number: <?php echo $room['roomNumber']; ?>, Type: <?php echo $room['type']; ?>, Price:
            $<?php echo $room['price']; ?>, Available From: <?php echo $room['availabilityStart']; ?>, Available
            Until: <?php echo $room['availabilityEnd']; ?></li>
    <?php endforeach; ?>
</ul>

<h2>Booked rooms</h2>
<ul>
    <?php
    $bookedRooms = $roomsCollection->find(['status' => 'booked']);
    foreach ($bookedRooms as $room):
        $reservations = $room['reservations'] ?? null;
        if (!is_object($reservations) || $reservations->count() < 1) {
            continue;
        }
        foreach ($reservations as $reservation):
            if (!is_null($reservation->checkOutDate)) continue;

            $user = $usersCollection->findOne(['_id' => $reservation->user_id]);
        ?>
            <li>Room Number: <?php echo $room['roomNumber']; ?>, Type: <?php echo $room['type']; ?>, Price:
                $<?php echo $room['price']; ?>, Booked By: <?php echo $user['name']; ?>, Check-In
                Date: <?php echo $reservation->checkInDate->toDateTime()->format('Y-m-d H:i:s'); ?></li>
        <?php endforeach; ?>
    <?php endforeach; ?>
</ul>

<h2>All reservations</h2>
<ul>
    <?php
    $bookedRooms = $roomsCollection->find([]);
    foreach ($bookedRooms as $room):
        $reservations = $room['reservations'] ?? null;
        if (!is_object($reservations) || $reservations->count() < 1) {
            continue;
        }
        foreach ($reservations as $reservation):
            $user = $usersCollection->findOne(['_id' => $reservation->user_id]);
            ?>
            <li>Room Number: <?php echo $room['roomNumber']; ?>, Type: <?php echo $room['type']; ?>, Price:
                $<?php echo $room['price']; ?>, Booked By: <?php echo $user['name']; ?>, Check-In
                Date: <?php echo $reservation->checkInDate->toDateTime()->format('Y-m-d H:i:s'); ?>, Check-Out
                Date: <?php echo ($reservation->checkOutDate ? $reservation->checkOutDate->toDateTime()->format('Y-m-d H:i:s') : '-'); ?></li>
        <?php endforeach; ?>
    <?php endforeach; ?>
</ul>

</body>
</html>
