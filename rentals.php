<?php
session_start();

require './config/database.php';
require 'rentail.php';

use App\Rental;

$db = Database::getInstance()->getConnection();
$rental = new Rental($db);

// --- Read search criteria ---
$criteria = [
    'city' => $_GET['city'] ?? null,
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null
];

// --- Pagination ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 6;

$results = $rental->search($criteria, $page, $limit);
?>

<!doctype html>
<html>
<head>
<title>Rentals</title>
</head>

<body>

<h1>Available Rentals</h1>

<form method="GET">

    <input type="text" name="city" placeholder="City"
           value="<?= htmlspecialchars($criteria['city'] ?? '') ?>">

    <input type="number" step="0.01" name="min_price"
           placeholder="Min Price"
           value="<?= htmlspecialchars($criteria['min_price'] ?? '') ?>">

    <input type="number" step="0.01" name="max_price"
           placeholder="Max Price"
           value="<?= htmlspecialchars($criteria['max_price'] ?? '') ?>">

    <button type="submit">Search</button>
</form>

<hr>

<?php if (!$results): ?>
    <p>No rentals found.</p>
<?php endif; ?>

<?php foreach ($results as $r): ?>

<div style="border:1px solid gray; padding:10px; margin:10px; width:300px;">

    <?php if (!empty($r['image_url'])): ?>
        <img src="<?= htmlspecialchars($r['image_url']) ?>" width="300">
    <?php endif; ?>

    <h3><?= htmlspecialchars($r['title']) ?></h3>

    <p>
        <?= htmlspecialchars($r['city']) ?><br>
        <?= htmlspecialchars($r['price_per_night']) ?> MAD / night
    </p>

    <a href="rental_detail.php?id=<?= $r['id'] ?>">View Details</a>

</div>

<?php endforeach; ?>

<!-- Pagination -->
<div>
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>">Previous</a>
    <?php endif; ?>

    <strong>Page <?= $page ?></strong>

    <a href="?page=<?= $page+1 ?>">Next</a>
</div>

</body>
</html>
