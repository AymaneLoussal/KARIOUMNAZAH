<?php
session_start();
require './config/database.php';
require 'AdmineManager.php';

use App\AdmineManager;

// DB connection
$db = Database::getInstance()->getConnection();
$admin = new AdmineManager($db);

// Only admins can access
$stmt = $db->prepare("
    SELECT r.name FROM user_roles ur
    JOIN roles r ON ur.role_id = r.id
    WHERE ur.user_id = :user_id
");
$stmt->execute([':user_id' => $_SESSION['user_id'] ?? 0]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$roles || !in_array('admin', $roles)) {
    die("Access denied");
}
?>

<h1>Admin Dashboard</h1>
<ul>
    <li><a href="users.php">Manage Users</a></li>
    <li><a href="admin_renatls.php">Manage Rentals</a></li>
</ul>
