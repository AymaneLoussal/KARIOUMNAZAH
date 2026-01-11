<?php
session_start();

require './config/database.php';
require 'booking.php';
require 'rentail.php';

use App\Booking;
use App\Rental;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$booking = new Booking($db);
$rental = new Rental($db);

$userId = $_SESSION['user_id'];

$bookings = $booking->findUserBookings($userId);

// Cancel booking
if (isset($_GET['cancel'])) {

    try {

        $booking->cancel($_GET['cancel'], $userId, $_SESSION['role']);
        header("Location: my_booking.php");
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | RentalPlatform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .booking-card {
            transition: all 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-4px);
        }
        
        .status-confirmed {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }
        
        .status-cancelled {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        }
        
        .status-completed {
            background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
        }
        
        .countdown-timer {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .empty-state {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <a href="index.php" class="flex items-center space-x-2 text-rose-600 font-bold text-2xl mb-4 md:mb-0">
                    <i class="fas fa-home text-3xl"></i>
                    <span>RentalPlatform</span>
                </a>
                
                <nav class="flex items-center space-x-6">
                    <a href="rentals.php" class="text-gray-700 hover:text-rose-600 font-medium transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Browse Rentals
                    </a>
                    <a href="s_favourites.php" class="text-gray-700 hover:text-rose-600 font-medium transition-colors duration-200">
                        <i class="fas fa-heart mr-2"></i>Favorites
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard_host.php" class="text-gray-700 hover:text-rose-600 font-medium transition-colors duration-200">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="logout.php" class="text-gray-700 hover:text-rose-600 font-medium transition-colors duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <i class="fas fa-calendar-alt text-rose-600 mr-3"></i>My Reservations
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Manage your upcoming stays and booking history
            </p>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'cancelled'): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg mb-8 animate-fade-in">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-bold text-green-800">Reservation Cancelled</h3>
                    <p class="text-green-700">Your booking has been cancelled successfully.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <?php if (!empty($bookings)): ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Bookings</p>
                        <h3 class="text-2xl font-bold text-gray-900"><?= count($bookings) ?></h3>
                    </div>
                </div>
            </div>
            
            <?php 
            $confirmed = array_filter($bookings, fn($b) => $b['status'] === 'confirmed');
            $cancelled = array_filter($bookings, fn($b) => $b['status'] === 'cancelled');
            $totalSpent = array_sum(array_column($bookings, 'total_price'));
            ?>
            
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Confirmed</p>
                        <h3 class="text-2xl font-bold text-gray-900"><?= count($confirmed) ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Cancelled</p>
                        <h3 class="text-2xl font-bold text-gray-900"><?= count($cancelled) ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Spent</p>
                        <h3 class="text-2xl font-bold text-gray-900"><?= $totalSpent ?> MAD</h3>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bookings Section -->
        <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-700 mb-4">No Reservations Yet</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    You haven't made any bookings yet. Start exploring amazing rental properties and plan your next stay!
                </p>
                <a href="rentals.php" 
                   class="inline-flex items-center bg-gradient-to-r from-rose-600 to-pink-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:from-rose-700 hover:to-pink-700 transition-all duration-200 transform hover:-translate-y-1 shadow-lg hover:shadow-xl">
                    <i class="fas fa-search mr-3"></i> Browse Available Rentals
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($bookings as $b): 
                $home = $rental->findById($b['home_id']);
                $checkInDate = new DateTime($b['check_in']);
                $checkOutDate = new DateTime($b['check_out']);
                $today = new DateTime();
                $isUpcoming = $checkInDate > $today;
                $isActive = $checkInDate <= $today && $checkOutDate >= $today;
            ?>
            <div class="booking-card bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start">
                        <!-- Property Image & Info -->
                        <div class="lg:w-1/3 mb-6 lg:mb-0 lg:pr-6">
                            <div class="flex flex-col sm:flex-row">
                                <div class="w-full sm:w-24 h-40 sm:h-24 rounded-lg overflow-hidden mb-4 sm:mb-0 sm:mr-4">
                                    <?php if (!empty($home['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($home['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($home['title']) ?>" 
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-home text-gray-400 text-2xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($home['title']) ?></h3>
                                    <div class="flex items-center text-gray-600 mb-3">
                                        <i class="fas fa-map-marker-alt text-rose-500 mr-2"></i>
                                        <?= htmlspecialchars($home['city']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Max Guests: <span class="font-semibold"><?= $home['max_guests'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="lg:w-2/3 border-t lg:border-t-0 lg:border-l border-gray-200 pt-6 lg:pt-0 lg:pl-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Dates -->
                                <div>
                                    <h4 class="text-gray-500 text-sm font-semibold mb-2">DATES</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-sign-in-alt text-blue-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Check-in</p>
                                                <p class="font-bold text-gray-900"><?= $b['check_in'] ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-sign-out-alt text-red-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Check-out</p>
                                                <p class="font-bold text-gray-900"><?= $b['check_out'] ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing -->
                                <div>
                                    <h4 class="text-gray-500 text-sm font-semibold mb-2">PRICING</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Total Price:</span>
                                            <span class="text-2xl font-bold text-rose-600"><?= $b['total_price'] ?> MAD</span>
                                        </div>
                                        <?php if ($isUpcoming): ?>
                                        <div class="text-sm text-gray-500">
                                            Payment status: <span class="font-semibold text-green-600">Paid</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Status & Actions -->
                                <div>
                                    <h4 class="text-gray-500 text-sm font-semibold mb-2">STATUS & ACTIONS</h4>
                                    <div class="space-y-4">
                                        <!-- Status Badge -->
                                        <div>
                                            <?php 
                                            $statusClass = 'status-' . strtolower($b['status']);
                                            $statusIcon = match(strtolower($b['status'])) {
                                                'confirmed' => 'fa-check-circle',
                                                'cancelled' => 'fa-times-circle',
                                                'pending' => 'fa-clock',
                                                default => 'fa-info-circle'
                                            };
                                            ?>
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-white text-sm font-semibold <?= $statusClass ?>">
                                                <i class="fas <?= $statusIcon ?> mr-2"></i>
                                                <?= ucfirst($b['status']) ?>
                                            </span>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex space-x-3">
                                            <?php if ($b['status'] === 'confirmed'): ?>
                                                <a href="my_booking.php?cancel=<?= $b['id'] ?>" 
                                                   onclick="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.')"
                                                   class="flex-1 bg-red-100 text-red-600 hover:bg-red-200 px-4 py-2 rounded-lg font-semibold text-center transition-colors duration-200">
                                                    <i class="fas fa-times mr-2"></i> Cancel
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="rental_detail.php?id=<?= $home['id'] ?>" 
                                               class="flex-1 bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 rounded-lg font-semibold text-center transition-colors duration-200">
                                                <i class="fas fa-eye mr-2"></i> View Details
                                            </a>
                                        </div>

                                        <!-- Additional Info -->
                                        <?php if ($isActive): ?>
                                        <div class="text-sm text-green-600 font-semibold">
                                            <i class="fas fa-check-circle mr-2"></i> Currently Active Stay
                                        </div>
                                        <?php elseif ($isUpcoming): ?>
                                        <div class="text-sm text-blue-600">
                                            <i class="fas fa-calendar-day mr-2"></i> 
                                            <?php 
                                            $daysUntil = $today->diff($checkInDate)->days;
                                            echo $daysUntil . ' day' . ($daysUntil !== 1 ? 's' : '') . ' until check-in';
                                            ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline Indicator -->
                <?php if ($isActive): ?>
                <div class="bg-green-50 border-t border-green-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-key text-green-600 mr-3"></i>
                            <span class="text-green-800 font-semibold">Check-in completed • Active stay</span>
                        </div>
                        <div class="countdown-timer text-green-600 font-bold">
                            <i class="fas fa-clock mr-2"></i>Enjoy your stay!
                        </div>
                    </div>
                </div>
                <?php elseif ($isUpcoming): ?>
                <div class="bg-blue-50 border-t border-blue-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-hourglass-half text-blue-600 mr-3"></i>
                            <span class="text-blue-800">Upcoming reservation • Prepare for your trip</span>
                        </div>
                        <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">
                            <i class="fas fa-question-circle mr-2"></i> Need help?
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Back to Rentals -->
        <div class="mt-12 text-center">
            <a href="rentals.php" 
               class="inline-flex items-center bg-gray-900 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-black transition-all duration-200 transform hover:-translate-y-1 shadow-lg hover:shadow-xl">
                <i class="fas fa-arrow-left mr-3"></i> Back to All Rentals
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="text-center">
                <a href="index.php" class="inline-flex items-center text-2xl font-bold text-white mb-8">
                    <i class="fas fa-home text-3xl mr-3"></i>
                    <span>RentalPlatform</span>
                </a>
                
                <div class="flex flex-wrap justify-center gap-8 mb-8">
                    <a href="about.php" class="text-gray-300 hover:text-white transition-colors duration-200">About Us</a>
                    <a href="contact.php" class="text-gray-300 hover:text-white transition-colors duration-200">Contact</a>
                    <a href="privacy.php" class="text-gray-300 hover:text-white transition-colors duration-200">Privacy Policy</a>
                    <a href="terms.php" class="text-gray-300 hover:text-white transition-colors duration-200">Terms of Service</a>
                </div>
                
                <div class="text-gray-500 text-sm pt-8 border-t border-gray-800">
                    &copy; <?= date('Y') ?> RentalPlatform. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <!-- Booking Status Update Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth transitions to booking cards
            const bookingCards = document.querySelectorAll('.booking-card');
            bookingCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Enhanced cancellation confirmation
            const cancelLinks = document.querySelectorAll('a[href*="cancel"]');
            cancelLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to cancel this booking?\n\nThis action cannot be undone. Any refund will be processed according to our cancellation policy.')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Update countdown timers for upcoming bookings
            function updateCountdowns() {
                const upcomingElements = document.querySelectorAll('.text-blue-600:contains("day")');
                upcomingElements.forEach(el => {
                    // This is a placeholder - in a real app, you would calculate actual remaining time
                    console.log('Countdown update logic would go here');
                });
            }
            
            // Update every minute
            setInterval(updateCountdowns, 60000);
        });
    </script>
</body>
</html>