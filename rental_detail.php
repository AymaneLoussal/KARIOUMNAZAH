<?php
session_start();

require './config/database.php';
require 'rentail.php';
require 'booking.php';

use App\Rental;
use App\Booking;

$db = Database::getInstance()->getConnection();

$rental = new Rental($db);
$booking = new Booking($db);

if (!isset($_GET['id'])) {
    echo "Rental not found";
    exit;
}

$home = $rental->findById($_GET['id']);

if (!$home) {
    echo "Rental not found";
    exit;
}

$errors = [];
$success = "";

// ============ HANDLE BOOKING SUBMIT ============
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Only travelers can book
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'traveler') {
        $errors[] = "Only travelers can book rentals.";
    }

    if (empty($_POST['check_in']) || empty($_POST['check_out'])) {
        $errors[] = "Please select check-in and check-out dates.";
    }

    if (!$errors) {
        try {

            $booking->create([
                'home_id'   => $home['id'],
                'user_id'   => $_SESSION['user_id'],
                'check_in'  => $_POST['check_in'],
                'check_out' => $_POST['check_out']
            ]);

            $success = "Reservation successfully created 🎉";

        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

?>
<!doctype html>
<html>
<head>
    <title><?= htmlspecialchars($home['title']) ?></title>
</head>

<body>

<h1><?= htmlspecialchars($home['title']) ?></h1>
<p><b>City:</b> <?= htmlspecialchars($home['city']) ?></p>
<p><b>Address:</b> <?= htmlspecialchars($home['address']) ?></p>
<p><b>Price per night:</b> <?= $home['price_per_night'] ?> MAD</p>
<p><b>Max guests:</b> <?= $home['max_guests'] ?></p>
<p><?= nl2br(htmlspecialchars($home['description'])) ?></p>

<?php if ($home['image_url']): ?>
    <img src="../<?= $home['image_url'] ?>" width="320">
<?php endif; ?>


<hr>

<h2>Book this rental</h2>

<?php if ($errors): ?>
<ul style="color:red">
    <?php foreach($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if ($success): ?>
<p style="color:green"><?= $success ?></p>
<?php endif; ?>


<?php if (!isset($_SESSION['user_id'])): ?>

    <p><a href="../login.php">Login to book</a></p>

<?php elseif ($_SESSION['role'] !== 'traveler'): ?>

    <p style="color:orange">
        Only travelers can make bookings.
    </p>

<?php else: ?>

<form method="POST">

    <label>Check in</label>
    <input type="date" name="check_in" required>

    <label>Check out</label>
    <input type="date" name="check_out" required>

    <button type="submit">Book Now</button>
</form>

<?php endif; ?>

<a href="rentals.php">Back to rentals</a>

</body>
</html>
