<?php
session_start();
require './config/database.php';
require 'AdmineManager.php';

use App\AdmineManager;

$db = Database::getInstance()->getConnection();
$admin = new AdmineManager($db);

// Admin check
$stmt = $db->prepare("
    SELECT r.name FROM user_roles ur
    JOIN roles r ON ur.role_id = r.id
    WHERE ur.user_id = :user_id
");
$stmt->execute([':user_id' => $_SESSION['user_id'] ?? 0]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!$roles || !in_array('admin', $roles)) die("Access denied");

// Handle toggle active
if (isset($_GET['toggle_rental'])) {
    $admin->toggleRentalStatus((int)$_GET['toggle_rental']);
    header("Location: rentals.php");
    exit;
}

// List rentals
$rentals = $admin->getAllRentals();
?>

<h1>Manage Rentals</h1>
<table border="1" cellpadding="5">
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Host</th>
    <th>City</th>
    <th>Price</th>
    <th>Active</th>
    <th>Actions</th>
</tr>
<?php foreach ($rentals as $r): ?>
<tr>
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= htmlspecialchars($r['host_name']) ?></td>
    <td><?= htmlspecialchars($r['city']) ?></td>
    <td><?= $r['price_per_night'] ?></td>
    <td><?= $r['is_active'] ? 'Yes' : 'No' ?></td>
    <td>
        <a href="?toggle_rental=<?= $r['id'] ?>"><?= $r['is_active'] ? 'Deactivate' : 'Activate' ?></a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="dashboard.php">Back to dashboard</a>
