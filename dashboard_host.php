<?php
session_start();
require './config/database.php';
require 'rentail.php';

use App\Rental;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'host') {
    header("Location: login.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$rental = new Rental($db);

$hostId = $_SESSION['user_id'];
$rentals = $rental->findAllByHost($hostId);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Dashboard | RentalPlatform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .dashboard-card {
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-4px);
        }
        
        .table-row-hover:hover {
            background-color: rgba(244, 63, 94, 0.05);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card-tertiary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .sidebar {
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -300px;
                top: 0;
                bottom: 0;
                z-index: 50;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-4 left-4 z-30">
        <button id="menu-toggle" class="bg-rose-600 text-white p-3 rounded-xl shadow-lg">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar w-64 bg-gray-900 text-white h-screen fixed lg:relative overflow-y-auto z-50">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-800">
            <a href="index.php" class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-rose-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-home text-xl"></i>
                </div>
                <span class="text-xl font-bold">RentalPlatform</span>
            </a>
        </div>

        <!-- User Profile -->
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-rose-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg"><?= htmlspecialchars($_SESSION['user_name']) ?></h3>
                    <p class="text-gray-400 text-sm">Host Account</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard_host.php" class="flex items-center space-x-3 p-3 bg-rose-600 rounded-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="rentals.php" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-search"></i>
                        <span>Browse Rentals</span>
                    </a>
                </li>
                <li>
                    <a href="add_rental.php" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Rental</span>
                    </a>
                </li>
                <li>
                    <a href="my_booking.php" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Reservations</span>
                    </a>
                </li>
                <li>
                    <a href="s_favourites.php" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-heart"></i>
                        <span>Favorites</span>
                    </a>
                </li>
                <li class="pt-4 border-t border-gray-800">
                    <a href="logout.php" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 p-4 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">
                    Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>! 👋
                </h1>
                <p class="text-gray-600 mt-2">Manage your rental properties and reservations</p>
            </div>
            <a href="add_rental.php" 
               class="mt-4 lg:mt-0 bg-gradient-to-r from-rose-600 to-pink-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-rose-700 hover:to-pink-700 transition-all duration-200 transform hover:-translate-y-1 shadow-lg hover:shadow-xl flex items-center">
                <i class="fas fa-plus-circle mr-3"></i> Add New Rental
            </a>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="stat-card text-white p-6 rounded-2xl shadow-xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-rose-100">Total Rentals</p>
                        <h3 class="text-3xl font-bold mt-2"><?= count($rentals) ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-home text-2xl"></i>
                    </div>
                </div>
                <p class="text-rose-100 text-sm mt-4">Properties you're hosting</p>
            </div>

            <div class="stat-card-secondary text-white p-6 rounded-2xl shadow-xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-pink-100">Total Revenue</p>
                        <h3 class="text-3xl font-bold mt-2">$<?= array_sum(array_column($rentals, 'price_per_night')) ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                </div>
                <p class="text-pink-100 text-sm mt-4">Potential monthly revenue</p>
            </div>

            <div class="stat-card-tertiary text-white p-6 rounded-2xl shadow-xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-blue-100">Total Capacity</p>
                        <h3 class="text-3xl font-bold mt-2"><?= array_sum(array_column($rentals, 'max_guests')) ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-friends text-2xl"></i>
                    </div>
                </div>
                <p class="text-blue-100 text-sm mt-4">Maximum guests across all rentals</p>
            </div>
        </div>

        <!-- Rentals Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Table Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 lg:mb-0">
                        <i class="fas fa-list mr-3 text-rose-600"></i> Your Rentals
                    </h2>
                    <div class="text-gray-600">
                        Showing <span class="font-bold"><?= count($rentals) ?></span> propert<?= count($rentals) === 1 ? 'y' : 'ies' ?>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-4 px-6 text-left text-gray-700 font-semibold">Image</th>
                            <th class="py-4 px-6 text-left text-gray-700 font-semibold">Property Details</th>
                            <th class="py-4 px-6 text-left text-gray-700 font-semibold">Location</th>
                            <th class="py-4 px-6 text-left text-gray-700 font-semibold">Price & Capacity</th>
                            <th class="py-4 px-6 text-left text-gray-700 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($rentals)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-home text-5xl text-gray-300 mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No rentals yet</h3>
                                    <p class="text-gray-500 mb-6">Start by adding your first rental property</p>
                                    <a href="add_rental.php" 
                                       class="bg-rose-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-rose-700 transition-colors">
                                        Add Your First Rental
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($rentals as $r): ?>
                            <tr class="table-row-hover">
                                <td class="py-4 px-6">
                                    <div class="w-20 h-20 rounded-lg overflow-hidden shadow-sm">
                                        <?php if (!empty($r['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($r['image_url']) ?>" 
                                                 alt="<?= htmlspecialchars($r['title']) ?>" 
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-home text-gray-400 text-2xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <h4 class="font-bold text-gray-900 mb-1"><?= htmlspecialchars($r['title']) ?></h4>
                                    <p class="text-gray-600 text-sm line-clamp-2">
                                        <?= strlen($r['description']) > 100 ? substr(htmlspecialchars($r['description']), 0, 100) . '...' : htmlspecialchars($r['description']) ?>
                                    </p>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center text-gray-700 mb-2">
                                        <i class="fas fa-city text-rose-500 mr-2"></i>
                                        <?= htmlspecialchars($r['city']) ?>
                                    </div>
                                    <div class="flex items-center text-gray-600 text-sm">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <?= htmlspecialchars($r['address']) ?>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-money-bill-wave text-rose-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Price/Night</p>
                                                <p class="font-bold text-gray-900">$<?= htmlspecialchars($r['price_per_night']) ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-user-friends text-blue-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Max Guests</p>
                                                <p class="font-bold text-gray-900"><?= htmlspecialchars($r['max_guests']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-3">
                                        <a href="edit_rental.php?id=<?= $r['id'] ?>" 
                                           class="flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_rental.php?id=<?= $r['id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this rental? This action cannot be undone.');"
                                           class="flex items-center justify-center w-10 h-10 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                           title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        <a href="rental_detail.php?id=<?= $r['id'] ?>" 
                                           class="flex items-center justify-center w-10 h-10 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-bolt text-yellow-500 mr-3"></i> Quick Actions
                </h3>
                <div class="space-y-4">
                    <a href="rentals.php" 
                       class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-search text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Browse All Rentals</h4>
                            <p class="text-gray-600 text-sm">Explore other properties on the platform</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                    </a>
                    
                    <a href="my_booking.php" 
                       class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-calendar-check text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">View Reservations</h4>
                            <p class="text-gray-600 text-sm">Check upcoming bookings and manage reservations</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-rose-50 to-pink-50 p-6 rounded-2xl shadow-lg border border-rose-100">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-rose-600 mr-3"></i> Performance Tips
                </h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span class="text-gray-700">High-quality photos increase bookings by 24%</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span class="text-gray-700">Detailed descriptions improve guest satisfaction</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span class="text-gray-700">Quick responses to inquiries boost your ranking</span>
                    </li>
                </ul>
            </div>
        </div>
    </main>

    <!-- JavaScript for Mobile Menu -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (menuToggle && sidebar && overlay) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                });
                
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
                
                // Close menu when clicking on a link (mobile)
                sidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 768) {
                            sidebar.classList.remove('active');
                            overlay.classList.remove('active');
                        }
                    });
                });
            }
            
            // Close menu on window resize (if resized to desktop)
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>