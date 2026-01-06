<?php
session_start();
require './config/database.php';
require 'rentail.php'; // make sure the file name is correct

use App\Rental;

// Only allow hosts
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'host') {
    header("Location: ../../login.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$rental = new Rental($db);

$hostId = $_SESSION['user_id'];
$rentals = $rental->findAllByHost($hostId);
?>

<!doctype html>
<html lang="en">
<head>
    <title>Host Dashboard</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        img { max-width: 100px; height: auto; display: block; }
    </style>
</head>
<body>
    <a href="rentals.php">View Rentals</a>
<h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
<a href="add_rental.php">Add New Rental</a>

<h2>Your Rentals</h2>
<table>
    <tr>
        <th>Image</th>
        <th>Title</th>
        <th>City</th>
        <th>Price/Night</th>
        <th>Guests</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($rentals as $r): ?>
        <tr>
            <td>
                <?php if (!empty($r['image_url'])): ?>
                    <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['city']) ?></td>
            <td>$<?= htmlspecialchars($r['price_per_night']) ?></td>
            <td><?= htmlspecialchars($r['max_guests']) ?></td>
            <td>
                <a href="edit_rental.php?id=<?= $r['id'] ?>">Edit</a> | 
                <a href="delete_rental.php?id=<?= $r['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
