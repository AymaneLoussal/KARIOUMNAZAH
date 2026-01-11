<?php
session_start();

require './config/database.php';
require 'rentail.php';
require 'booking.php';
require './config/mailer.php';
$mailer = new Mailer('smtp.gmail.com', 'aymanloussal552@gmail.com', 'yijbfnjkptwusshx', true, 587, 'tls');


use App\Rental;
use App\Booking;

$db = Database::getInstance()->getConnection();

$rental = new Rental($db);
$booking = new Booking($db);

if (!isset($_GET['id'])) {
    echo "Rental not found";
    exit;
}

$home = $rental->findById($_GET['id']);

if (!$home) {
    echo "Rental not found";
    exit;
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'traveler') {
        $errors[] = "Only travelers can book rentals.";
    }

    if (empty($_POST['check_in']) || empty($_POST['check_out'])) {
        $errors[] = "Please select check-in and check-out dates.";
    }

    if (!$errors) {
        try {

            $booking->create([
    'home_id'   => $home['id'],
    'user_id'   => $_SESSION['user_id'],
    'check_in'  => $_POST['check_in'],
    'check_out' => $_POST['check_out']
]);

$reservation = $booking->findLastByUser($_SESSION['user_id']);

$traveler = $booking->getUser($_SESSION['user_id']);

$host = $booking->getUser($home['host_id']);

$travelerMsg = "
    Reservation Confirmed ✔<br>
    Rental: {$home['title']}<br>
    City: {$home['city']}<br>
    Check-in: {$reservation['check_in']}<br>
    Check-out: {$reservation['check_out']}<br>
    Total Price: {$reservation['total_price']} MAD
";

$hostMsg = "
    New Booking <br>
    Traveler: {$traveler['name']}<br>
    Rental: {$home['title']}<br>
    Dates: {$reservation['check_in']} → {$reservation['check_out']}<br>
    Total: {$reservation['total_price']} MAD
";

$mailer->sendOTP($traveler['email'], $travelerMsg);
$mailer->sendOTP($host['email'], $hostMsg);

$success = "Reservation successfully created 🎉 Email notifications sent.";

header("Location: rentals.php");


        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($home['title']) ?> | RentalPlatform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .booking-form-shadow {
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        }
        
        .property-image {
            transition: transform 0.3s ease;
        }
        
        .property-image:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
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
                    <a href="my_booking.php" class="text-gray-700 hover:text-rose-600 font-medium transition-colors duration-200">
                        <i class="fas fa-calendar-alt mr-2"></i>My Bookings
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Property Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($home['title']) ?></h1>
            <div class="flex items-center text-gray-600 mb-6">
                <i class="fas fa-map-marker-alt text-rose-500 mr-2"></i>
                <span class="text-lg"><?= htmlspecialchars($home['city']) ?>, <?= htmlspecialchars($home['address']) ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Property Details -->
            <div class="lg:col-span-2">
                <!-- Property Image -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-lg mb-8 property-image">
                    <?php if ($home['image_url']): ?>
                        <img src="../<?= $home['image_url'] ?>" 
                             alt="<?= htmlspecialchars($home['title']) ?>" 
                             class="w-full h-96 object-cover">
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                             alt="Rental Property" 
                             class="w-full h-96 object-cover">
                    <?php endif; ?>
                </div>

                <!-- Property Information -->
                <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b">Property Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                            <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-money-bill-wave text-rose-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Price per night</p>
                                <p class="text-2xl font-bold text-rose-600"><?= $home['price_per_night'] ?> <span class="text-gray-600 text-lg">MAD</span></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-user-friends text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Max Guests</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $home['max_guests'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-rose-500 mr-3"></i> Description
                        </h3>
                        <p class="text-gray-700 leading-relaxed text-lg"><?= nl2br(htmlspecialchars($home['description'])) ?></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-5 rounded-xl">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-city text-gray-500 mr-2"></i> City
                            </h4>
                            <p class="text-gray-700"><?= htmlspecialchars($home['city']) ?></p>
                        </div>
                        
                        <div class="bg-gray-50 p-5 rounded-xl">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-map-pin text-gray-500 mr-2"></i> Address
                            </h4>
                            <p class="text-gray-700"><?= htmlspecialchars($home['address']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Booking Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl booking-form-shadow p-8 sticky top-24">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 pb-4 border-b">Book This Rental</h2>
                    
                    <!-- Price Display -->
                    <div class="text-center mb-8">
                        <div class="text-5xl font-bold text-rose-600 mb-2"><?= $home['price_per_night'] ?> <span class="text-3xl text-gray-600">MAD</span></div>
                        <p class="text-gray-500">per night</p>
                    </div>

                    <!-- Error Messages -->
                    <?php if ($errors): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                            <h3 class="font-bold text-red-800">Please fix the following errors:</h3>
                        </div>
                        <ul class="mt-2 text-red-700 list-disc list-inside">
                            <?php foreach($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Success Message -->
                    <?php if ($success && isset($reservation['id'])): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-6 mb-6 rounded-xl text-center">
                        <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                        <h3 class="text-xl font-bold text-green-800 mb-2">Booking Successful!</h3>
                        <p class="text-green-700 mb-4"><?= $success ?></p>
                        <a href="receipt.php?id=<?= $reservation['id'] ?>" 
                           class="inline-flex items-center justify-center bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-black transition-colors duration-200">
                            <i class="fas fa-download mr-2"></i> Download Receipt PDF
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Booking Form or Login Prompt -->
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="text-center p-6 bg-blue-50 rounded-xl">
                            <i class="fas fa-lock text-blue-500 text-4xl mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Login to Book</h3>
                            <p class="text-gray-600 mb-4">Please login to make a reservation</p>
                            <a href="login.php" 
                               class="inline-flex items-center justify-center bg-rose-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-rose-700 transition-colors duration-200">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login Now
                            </a>
                        </div>
                    
                    <?php elseif ($_SESSION['role'] !== 'traveler'): ?>
                        <div class="text-center p-6 bg-amber-50 rounded-xl">
                            <i class="fas fa-user-slash text-amber-500 text-4xl mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Booking Restricted</h3>
                            <p class="text-amber-700 font-medium">Only travelers can make bookings.</p>
                        </div>
                    
                    <?php else: ?>
                        <form method="POST" class="space-y-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">
                                    <i class="fas fa-calendar-check text-rose-500 mr-2"></i> Check-in Date
                                </label>
                                <input type="date" 
                                       name="check_in" 
                                       required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all duration-200">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">
                                    <i class="fas fa-calendar-times text-rose-500 mr-2"></i> Check-out Date
                                </label>
                                <input type="date" 
                                       name="check_out" 
                                       required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all duration-200">
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-rose-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:from-rose-700 hover:to-pink-700 transition-all duration-200 transform hover:-translate-y-1 shadow-lg hover:shadow-xl">
                                <i class="fas fa-calendar-plus mr-2"></i> Book Now
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Additional Info -->
                    <div class="mt-8 pt-6 border-t">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-shield-alt text-gray-500 mr-2"></i> Booking Protection
                        </h4>
                        <p class="text-gray-600 text-sm">Your booking is protected by our secure payment system and cancellation policy.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-center mt-12 py-6 border-t border-b">
            <a href="rentals.php" 
               class="flex items-center text-gray-700 hover:text-rose-600 font-semibold mb-4 sm:mb-0 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-3"></i> Back to Rentals
            </a>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'traveler'): ?>
            <a href="my_booking.php" 
               class="flex items-center bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-black transition-colors duration-200">
                <i class="fas fa-calendar-alt mr-3"></i> View My Bookings
            </a>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-12">
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

    <!-- Smooth scroll for anchor links -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>