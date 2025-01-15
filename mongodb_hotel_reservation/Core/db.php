<?php
require '../Core/vendor/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$usersCollection = $client->hotel_booking_system->users;
$adminsCollection = $client->hotel_booking_system->admins;
$bookingsCollection = $client->hotel_booking_system->bookings;
$roomsCollection = $client->hotel_booking_system->rooms;