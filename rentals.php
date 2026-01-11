<?php
session_start();

require './config/database.php';
require 'rentail.php';
require 'Favourite.php';

use App\Rental;
use App\Favourite;

$db = Database::getInstance()->getConnection();

$rental = new Rental($db);
$favourite = new Favourite($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {

    if (isset($_POST['add_fav']) && isset($_POST['rental_id'])) {
        $favourite->addFavourite($_SESSION['user_id'], $_POST['rental_id']);
    }

    if (isset($_POST['remove_fav']) && isset($_POST['rental_id'])) {
        $favourite->removeFavourite($_SESSION['user_id'], $_POST['rental_id']);
    }

    header("Location: ".$_SERVER['REQUEST_URI']);
    exit;
}

$criteria = [
    'city' => $_GET['city'] ?? null,
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null
];

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 6;

$results = $rental->search($criteria, $page, $limit);
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Listings | Find Your Perfect Stay</title>
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
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--secondary);
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .favorites-link {
            color: var(--primary);
            font-weight: 600;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('https://images.unsplash.com/photo-1518780664697-55e3ad937233?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 80px 0 60px;
            text-align: center;
            border-radius: 0 0 var(--radius) var(--radius);
            margin-bottom: 40px;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        /* Search Form */
        .search-section {
            background-color: var(--white);
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
        }

        .search-section h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
        }

        .form-control {
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 56, 92, 0.2);
        }

        .btn-search {
            background-color: var(--primary);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 46px;
        }

        .btn-search:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Results Header */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .results-count {
            font-size: 1.2rem;
            color: var(--secondary);
        }

        .add-rental-btn {
            background-color: var(--secondary);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-rental-btn:hover {
            background-color: #000;
            transform: translateY(-2px);
        }

        /* Rentals Grid */
        .rentals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .rental-card {
            background-color: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .rental-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .rental-image {
            height: 240px;
            overflow: hidden;
            position: relative;
        }

        .rental-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .rental-card:hover .rental-image img {
            transform: scale(1.05);
        }

        .rental-price {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .favorite-btn-container {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .favorite-btn {
            background-color: rgba(255, 255, 255, 0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1.2rem;
        }

        .favorite-btn:hover {
            background-color: var(--white);
            transform: scale(1.1);
        }

        .favorite-btn.added {
            color: var(--primary);
        }

        .favorite-btn.not-added {
            color: var(--dark-gray);
        }

        .rental-content {
            padding: 20px;
        }

        .rental-title {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .rental-location {
            color: var(--dark-gray);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rental-location i {
            font-size: 0.9rem;
        }

        .rental-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--medium-gray);
        }

        .btn-view {
            background-color: var(--primary);
            color: var(--white);
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-view:hover {
            background-color: var(--primary-dark);
        }

        /* No Results State */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            grid-column: 1 / -1;
        }

        .no-results i {
            font-size: 4rem;
            color: var(--medium-gray);
            margin-bottom: 20px;
        }

        .no-results h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .no-results p {
            color: var(--dark-gray);
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin: 40px 0;
        }

        .pagination a, .pagination span {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .pagination a {
            background-color: var(--white);
            color: var(--secondary);
            border: 1px solid var(--medium-gray);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pagination a:hover {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .pagination span {
            background-color: var(--primary);
            color: var(--white);
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
        @media (max-width: 992px) {
            .rentals-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 25px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                gap: 20px;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .rentals-grid {
                grid-template-columns: 1fr;
            }

            .results-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .footer-links {
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 60px 0 40px;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .search-section {
                padding: 20px;
            }

            .rental-actions {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .btn-view {
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
                    <a href="s_favourites.php" class="favorites-link">
                        <i class="fas fa-heart"></i>
                        My Favorites
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php">
                            <i class="fas fa-user-circle"></i>
                            Dashboard
                        </a>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Find Your Perfect Stay</h1>
                <p>Discover amazing rental properties in your preferred locations with the best prices.</p>
            </div>
        </section>

        <section class="container">
            <div class="search-section">
                <h2><i class="fas fa-search"></i> Search Rentals</h2>
                <form method="GET" class="search-form">
                    <div class="form-group">
                        <label for="city"><i class="fas fa-city"></i> City</label>
                        <input type="text" 
                               id="city" 
                               name="city" 
                               class="form-control"
                               placeholder="Enter city name"
                               value="<?= htmlspecialchars($criteria['city'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="min_price"><i class="fas fa-money-bill-wave"></i> Min Price (MAD)</label>
                        <input type="number" 
                               step="0.01" 
                               id="min_price" 
                               name="min_price" 
                               class="form-control"
                               placeholder="Minimum price"
                               value="<?= htmlspecialchars($criteria['min_price'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_price"><i class="fas fa-money-bill-wave"></i> Max Price (MAD)</label>
                        <input type="number" 
                               step="0.01" 
                               id="max_price" 
                               name="max_price" 
                               class="form-control"
                               placeholder="Maximum price"
                               value="<?= htmlspecialchars($criteria['max_price'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Search Rentals
                    </button>
                </form>
            </div>

            <div class="results-header">
                <div class="results-count">
                    <?php if ($results): ?>
                        <i class="fas fa-home"></i> 
                        <?= count($results) ?> Rental<?= count($results) !== 1 ? 's' : '' ?> Found
                    <?php else: ?>
                        <i class="fas fa-home"></i> Search Results
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="add_rental.php" class="add-rental-btn">
                        <i class="fas fa-plus-circle"></i> Add New Rental
                    </a>
                <?php endif; ?>
            </div>

            <div class="rentals-grid">
                <?php if (!$results): ?>
                    <div class="no-results">
                        <i class="fas fa-home"></i>
                        <h3>No rentals found</h3>
                        <p>Try adjusting your search criteria or browse all available properties.</p>
                        <a href="?" class="btn-search">
                            <i class="fas fa-redo"></i> Reset Search
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($results as $r): ?>
                    <div class="rental-card">
                        <div class="rental-image">
                            <?php if (!empty($r['image_url'])): ?>
                                <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Rental Property">
                            <?php endif; ?>
                            
                            <div class="rental-price"><?= htmlspecialchars($r['price_per_night']) ?> MAD / night</div>
                            
                            <?php if (isset($_SESSION['user_id'])): 
                                $isFav = $favourite->isFavourite($_SESSION['user_id'], $r['id']);
                            ?>
                            <div class="favorite-btn-container">
                                <form method="POST">
                                    <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="<?= $isFav ? 'remove_fav' : 'add_fav' ?>" 
                                            class="favorite-btn <?= $isFav ? 'added' : 'not-added' ?>">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="rental-content">
                            <h3 class="rental-title"><?= htmlspecialchars($r['title']) ?></h3>
                            <div class="rental-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($r['city']) ?>
                            </div>
                            
                            <div class="rental-actions">
                                <a href="rental_detail.php?id=<?= $r['id'] ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                
                                <?php if (isset($_SESSION['user_id']) && $isFav): ?>
                                    <span style="color: var(--primary); font-size: 0.9rem;">
                                        <i class="fas fa-heart"></i> In your favorites
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($results): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>

                <span>Page <?= $page ?></span>

                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <?php endif; ?>
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