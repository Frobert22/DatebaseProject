<?php
require '../Core/vendor/autoload.php';

// MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$roomsCollection = $client->hotel_booking_system->rooms;

// MySqli
$host = "localhost";
$username = "root";
$password = "";
$database = "hotel_booking_system";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
