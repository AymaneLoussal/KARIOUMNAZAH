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
if (isset($_GET['toggle_user'])) {
    $admin->toggleUserStatus((int)$_GET['toggle_user']);
    header("Location: users.php");
    exit;
}

// Handle role change (optional)
// if (isset($_POST['set_role'])) {
//     $admin->setUserRole((int)$_POST['user_id'], $_POST['role']);
//     header("Location: users.php");
//     exit;
// }

// List users
$users = $admin->getAllUsers();
?>

<h1>Manage Users</h1>
<table border="1" cellpadding="5">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Roles</th>
    <th>Active</th>
    <th>Actions</th>
</tr>
<?php foreach ($users as $u): ?>
<tr>
    <td><?= $u['id'] ?></td>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['roles']) ?></td>
    <td><?= $u['is_active'] ? 'Yes' : 'No' ?></td>
    <td>
        <a href="?toggle_user=<?= $u['id'] ?>"><?= $u['is_active'] ? 'Deactivate' : 'Activate' ?></a>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <select name="role">
                <option value="traveler">Traveler</option>
                <option value="host">Host</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="set_role">Change Role</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="admin_dashboard.php">Back to dashboard</a>
