<?php
require '../Core/db.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$adminsCollection = $client->hotel_booking_system->admins;

$adminEmail = 'admin@example.com';
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);

try {
    $admin = $adminsCollection->findOne(['email' => $adminEmail]);
    if (!$admin) {
        $result = $adminsCollection->insertOne([
            'email' => $adminEmail,
            'password' => $adminPassword
        ]);

        if ($result->getInsertedCount() > 0) {
            echo "Admin user created successfully!";
        } else {
            echo "Failed to create admin user.";
        }
    } else {
        echo "Admin user already exists.";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>
