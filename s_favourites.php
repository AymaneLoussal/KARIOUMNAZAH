<?php
session_start();

require './config/database.php';
require 'rentail.php';
require 'Favourite.php';

use App\Rental;
use App\Favourite;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$rental = new Rental($db);
$favourite = new Favourite($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_fav']) && isset($_POST['rental_id'])) {
    $favourite->removeFavourite($_SESSION['user_id'], $_POST['rental_id']);
    header("Location: s_favourites.php"); // refresh page
    exit;
}

$favRentals = $favourite->findUserFavourite($_SESSION['user_id']);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites | Rental Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #ff385c;
            --primary-dark: #e31c5f;
            --secondary: #222222;
            --light-gray: #f7f7f7;
            --medium-gray: #dddddd;
            --dark-gray: #717171;
            --white: #ffffff;
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--light-gray);
            color: var(--secondary);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo i {
            font-size: 28px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--secondary);
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        /* Page Header */
        .page-header {
            padding: 40px 0 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-header p {
            color: var(--dark-gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Favorites Grid */
        .favorites-container {
            padding: 0 0 60px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--medium-gray);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .empty-state p {
            color: var(--dark-gray);
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        /* Favorite Card */
        .favorite-card {
            background-color: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .favorite-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .favorite-image {
            height: 220px;
            overflow: hidden;
            position: relative;
        }

        .favorite-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .favorite-card:hover .favorite-image img {
            transform: scale(1.05);
        }

        .favorite-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(255, 56, 92, 0.95);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .favorite-content {
            padding: 20px;
        }

        .favorite-title {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .favorite-location {
            color: var(--dark-gray);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .favorite-location i {
            font-size: 0.9rem;
        }

        .favorite-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .favorite-price span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--dark-gray);
        }

        .favorite-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--medium-gray);
        }

        .btn-view {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-view:hover {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-remove {
            background-color: transparent;
            color: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-remove:hover {
            background-color: #fff0f0;
            color: var(--primary);
            border-color: var(--primary);
        }

        /* Footer */
        footer {
            background-color: var(--secondary);
            color: var(--white);
            padding: 40px 0 20px;
            margin-top: 40px;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--white);
            text-decoration: none;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-links {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .footer-links a {
            color: var(--light-gray);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--white);
        }

        .copyright {
            color: var(--dark-gray);
            font-size: 0.9rem;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                gap: 20px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .favorites-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }

            .footer-links {
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .favorites-grid {
                grid-template-columns: 1fr;
            }

            .favorite-actions {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .btn-view, .btn-remove {
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-home"></i>
                    <span>RentalPlatform</span>
                </a>
                <div class="nav-links">
                    <a href="rentals.php">Browse Rentals</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="page-header">
            <div class="container">
                <h1>
                    <i class="fas fa-heart"></i>
                    My Favorite Rentals
                </h1>
                <p>Your saved properties for easy access and comparison</p>
            </div>
        </section>

        <section class="favorites-container">
            <div class="container">
                <?php if (!$favRentals): ?>
                    <div class="empty-state">
                        <i class="far fa-heart"></i>
                        <h3>No favorites yet</h3>
                        <p>You haven't saved any rentals to your favorites. Start exploring properties and add the ones you love!</p>
                        <a href="rentals.php" class="btn-primary">
                            <i class="fas fa-search"></i> Browse Rentals
                        </a>
                    </div>
                <?php else: ?>
                    <div class="favorites-grid">
                        <?php foreach ($favRentals as $r): ?>
                        <div class="favorite-card">
                            <div class="favorite-image">
                                <?php if (!empty($r['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Rental Property">
                                <?php endif; ?>
                                <div class="favorite-badge">
                                    <i class="fas fa-heart"></i> Favorited
                                </div>
                            </div>
                            <div class="favorite-content">
                                <h3 class="favorite-title"><?= htmlspecialchars($r['title']) ?></h3>
                                <div class="favorite-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($r['city']) ?>
                                </div>
                                <div class="favorite-price">
                                    <?= htmlspecialchars($r['price_per_night']) ?> MAD <span>/ night</span>
                                </div>
                                <div class="favorite-actions">
                                    <a href="rental_detail.php?id=<?= $r['id'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <form method="POST" class="remove-form">
                                        <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
                                        <button type="submit" name="remove_fav" class="btn-remove">
                                            <i class="far fa-trash-alt"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="rentals.php" class="btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to All Rentals
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <a href="index.php" class="footer-logo">
                    <i class="fas fa-home"></i>
                    <span>RentalPlatform</span>
                </a>
                <div class="footer-links">
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms of Service</a>
                </div>
                <div class="copyright">
                    &copy; <?= date('Y') ?> RentalPlatform. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</body>
</html>