<?php
session_start();

require './config/database.php';
require 'rentail.php';

use App\Rental;

// Allow only hosts
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'host') {
    header("Location: login.php");
    exit;
}

// DB connection
$db = Database::getInstance()->getConnection();
$rental = new Rental($db);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Required fields
    $required = ['title','description','city','address','price_per_night','max_guests'];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_',' ', $field)) . " is required.";
        }
    }

    // ---------- IMAGE UPLOAD ----------
    $image_url = null;

    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image type. Allowed: jpg, jpeg, png, gif";
        } else {

            // uploads directory next to this file
            $uploadDir = __DIR__ . '/uploads/';

            // create folder if missing
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . "." . $ext;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                // path stored in DB (public path)
                $image_url = "uploads/" . $fileName;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // ---------- INSERT RENTAL ----------
    if (empty($errors)) {

        $data = [
            'host_id' => $_SESSION['user_id'],
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'city' => trim($_POST['city']),
            'address' => trim($_POST['address']),
            'price_per_night' => $_POST['price_per_night'],
            'max_guests' => $_POST['max_guests'],
            'image_url' => $image_url
        ];

        if ($rental->create($data)) {
            header("Location: dashboard_host.php");
            exit;
        } else {
            $errors[] = "Failed to add rental.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>Add Rental</title>
</head>

<body>

<h1>Add New Rental</h1>

<?php if (!empty($errors)): ?>
<ul style="color:red;">
    <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="Title"><br><br>

    <textarea name="description" placeholder="Description"></textarea><br><br>

    <input type="text" name="city" placeholder="City"><br><br>

    <input type="text" name="address" placeholder="Address"><br><br>

    <input type="number" step="0.01" name="price_per_night" placeholder="Price per night"><br><br>

    <input type="number" name="max_guests" placeholder="Max Guests"><br><br>

    <label>Rental Image</label><br>
    <input type="file" name="image"><br><br>

    <button type="submit">Add Rental</button>
</form>

<br>
<a href="dashboard_host.php">Back to Dashboard</a>

</body>
</html>
