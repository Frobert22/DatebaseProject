<?php
session_start();
require '../Core/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logoutUser'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$reservationMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['check_out'])) {
        $reservationId = $_POST['reservation_id'] ?? null;
        $roomId = $_POST['room_id'] ?? null;

        if ($roomId && $reservationId) {
            $roomId = new MongoDB\BSON\ObjectID($roomId);
            $reservationId = new MongoDB\BSON\ObjectID($reservationId);
            $room = $roomsCollection->findOne(['_id' => $roomId, 'status' => 'booked']);

            if (!$room) {
                echo "Room is not available.";
            } else {
                $result = $roomsCollection->updateOne(
                    [
                        '_id' => $roomId,
                        'reservations' => [
                            '$elemMatch' => [
                                'user_id' => $userId,
                                '_id' => $reservationId,
                                'checkOutDate' => null
                            ]
                        ]
                    ],
                    [
                        '$set' => [
                            'reservations.$.checkOutDate' => new MongoDB\BSON\UTCDateTime(),
                            'status' => 'available'
                        ]
                    ]
                );

                if ($result->getModifiedCount() > 0) {
                    echo "Reservation successfully checked out!";
                } else {
                    echo "Update error!";
                }
            }
        }
    } else if (isset($_POST['addReservation'])) {
        $roomNumber = $_POST['roomNumber'] ?? null;

        if ($userId && $roomNumber) {
            $room = $roomsCollection->findOne(['roomNumber' => $roomNumber, 'status' => 'available']);

            if ($room && ($room['status'] ?? '') == 'available') {
                $reservation = [
                    '_id' => new MongoDB\BSON\ObjectID(),
                    'user_id' => $userId,
                    'checkInDate' => new MongoDB\BSON\UTCDateTime(),
                    'checkOutDate' => null
                ];

                $result = $roomsCollection->updateOne(
                    ['_id' => $room['_id']],
                    [
                        '$push' => ['reservations' => $reservation],
                        '$set' => ['status' => 'booked'],
                    ]
                );

                if ($result->getModifiedCount() > 0) {
                    $reservationMessage = "Reservation successfully added!";
                } else {
                    $reservationMessage = "Failed to add reservation. Please try again.";
                }
            } else {
                $reservationMessage = "Room is not available.";
            }
        } else {
            $reservationMessage = "Please select a room!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
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
<div style="font-style: italic; color: #979ca0;">Welcome: <?php echo $_SESSION['logged_user'] ?></div>
<h1>Client Dashboard</h1>

<form method="POST" action="">
    <button type="submit" name="logoutUser">Logout</button>
</form>

<h2>Add Reservation</h2>
<form method="POST" action="">
    <label for="roomNumber">Room Number:</label>
    <select id="roomNumber" name="roomNumber" required>
        <?php
        $rooms = $roomsCollection->find(['status' => 'available']);
        foreach ($rooms as $room) {
            echo "<option value=\"{$room['roomNumber']}\">Room Number: {$room['roomNumber']}, Type: {$room['type']}, Price: \${$room['price']} EUR</option>";
        }
        ?>
    </select>
    <br>
    <button type="submit" name="addReservation">Add Reservation</button>
</form>

<?php if ($reservationMessage): ?>
    <p><?php echo $reservationMessage; ?></p>
<?php endif; ?>

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

<h2>Booked Rooms</h2>
<table>
    <tr>
        <th>Room Number</th>
        <th>Type</th>
        <th>Price (EUR)</th>
        <th>Booked By</th>
        <th>Check-In Date</th>
    </tr>
    <?php
    $bookedRooms = $roomsCollection->find(['status' => 'booked']);
    foreach ($bookedRooms as $room):
        $reservations = $room['reservations'] ?? null;
        if (!is_object($reservations) || $reservations->count() < 1) {
            continue;
        }
        foreach ($reservations as $reservation):
            if ($reservation['user_id'] !== $userId || !is_null($reservation->checkOutDate)) {
                continue;
            }
            ?>
            <tr>
                <td><?php echo $room['roomNumber']; ?></td>
                <td><?php echo $room['type']; ?></td>
                <td><?php echo $room['price']; ?> EUR</td>
                <td><?php echo $reservation['checkInDate']->toDateTime()->format('Y-m-d H:i:s'); ?></td>
                <td>-</td>
                <td>
                    <form method="post" action="">
                        <input type="hidden" name="check_out" value="1">
                        <input type="hidden" name="room_id" value="<?php echo $room['_id']; ?>">
                        <input type="hidden" name="reservation_id" value="<?php echo $reservation->_id; ?>">
                        <button type="submit">Check out</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>
</body>
</html>
