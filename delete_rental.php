<?php
session_start();
require './config/database.php';
require 'rentail.php';

use App\Rental;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'host') {
    header("Location: ../../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$hostId = $_SESSION['user_id'];

$db = Database::getInstance()->getConnection();
$rental = new Rental($db);

$rental->delete($id, $hostId);

header("Location: dashboard_host.php");
exit;
