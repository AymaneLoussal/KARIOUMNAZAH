<?php
session_start();
require './config/database.php';
require 'rentail.php';

use App\Rental;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'host') {
    header("Location: ../../login.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$rental = new Rental($db);

$errors = [];
$id = (int)($_GET['id'] ?? 0);
$hostId = $_SESSION['user_id'];
$currentRental = $rental->findById($id);

// Check ownership
if (!$currentRental || $currentRental['host_id'] != $hostId) {
    die("Rental not found or access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['title','description','city','address','price_per_night','max_guests'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "$field is required.";
        }
    }

    if (!$errors) {
        $data = [
            'title'          => $_POST['title'],
            'description'    => $_POST['description'],
            'city'           => $_POST['city'],
            'address'        => $_POST['address'],
            'price_per_night'=> $_POST['price_per_night'],
            'max_guests'     => $_POST['max_guests']
        ];

        if ($rental->update($id, $hostId, $data)) {
            header("Location: dashboard_host.php");
            exit;
        } else {
            $errors[] = "Failed to update rental.";
        }
    }
    $image_url = $currentRental['image_url']; // default to old image
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','gif'];
    if (in_array(strtolower($ext), $allowed)) {
        $filename = uniqid().'.'.$ext;
        $destination = '../../uploads/'.$filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_url = 'uploads/'.$filename;
        }
    }
}
$data['image_url'] = $image_url;
}

?>

<!doctype html>
<html lang="en">
<head>
    <title>Edit Rental</title>
</head>
<body>
<h1>Edit Rental</h1>
<?php if ($errors): ?>
    <ul style="color:red;">
        <?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?>
    </ul>
<?php endif; ?>
<form method="POST" action="">
    <input type="text" name="title" placeholder="Title" value="<?= htmlspecialchars($currentRental['title']) ?>"><br>
    <textarea name="description" placeholder="Description"><?= htmlspecialchars($currentRental['description']) ?></textarea><br>
    <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($currentRental['city']) ?>"><br>
    <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($currentRental['address']) ?>"><br>
    <input type="number" step="0.01" name="price_per_night" placeholder="Price per night" value="<?= htmlspecialchars($currentRental['price_per_night']) ?>"><br>
    <input type="number" name="max_guests" placeholder="Max Guests" value="<?= htmlspecialchars($currentRental['max_guests']) ?>"><br>
    <label>Image</label>
<input type="file" name="image">
<?php if($currentRental['image_url']): ?>
    <img src="../../<?= $currentRental['image_url'] ?>" width="100" alt="Current Image">
<?php endif; ?>

    <button type="submit">Update Rental</button>
</form>
<a href="dashboard_host.php">Back to Dashboard</a>
</body>
</html>
