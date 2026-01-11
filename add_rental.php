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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $required = ['title','description','city','address','price_per_night','max_guests'];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_',' ', $field)) . " is required.";
        }
    }

    $image_url = null;

    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image type. Allowed: jpg, jpeg, png, gif";
        } else {

            $uploadDir = __DIR__ . '/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . "." . $ext;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                $image_url = "uploads/" . $fileName;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    if (empty($errors)) {

        $data = [
            'host_id' => $_SESSION['user_id'],
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'city' => trim($_POST['city']),
            'address' => trim($_POST['address']),
            'price_per_night' => $_POST['price_per_night'],
            'max_guests' => $_POST['max_guests'],
            'image_url' => $image_url
        ];

        if ($rental->create($data)) {
            header("Location: dashboard_host.php");
            exit;
        } else {
            $errors[] = "Failed to add rental.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Rental | RentalPlatform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .form-shadow {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            ring: 2px;
        }
        
        .file-upload {
            transition: all 0.3s ease;
        }
        
        .file-upload:hover {
            transform: translateY(-2px);
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
                    <a href="dashboard_host.php" class="text-gray-700 hover:text-rose-600 font-medium transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="rentals.php" class="text-gray-700 hover:text-rose-600 font-medium transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Browse Rentals
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
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
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <i class="fas fa-plus-circle text-rose-600 mr-3"></i>Add New Rental
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Share your space with travelers. Fill in the details below to list your property.
            </p>
        </div>

        <!-- Form Container -->
        <div class="max-w-4xl mx-auto">
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg mb-8 animate-fade-in">
                <div class="flex items-center mb-3">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                    <h3 class="text-xl font-bold text-red-800">Please fix the following errors:</h3>
                </div>
                <ul class="space-y-2">
                    <?php foreach ($errors as $e): ?>
                        <li class="text-red-700 flex items-center">
                            <i class="fas fa-circle text-red-500 text-xs mr-3"></i>
                            <?= htmlspecialchars($e) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl form-shadow p-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-8">
                    <!-- Title -->
                    <div>
                        <label class="block text-gray-800 font-semibold mb-3 text-lg">
                            <i class="fas fa-heading text-rose-500 mr-2"></i>Rental Title
                        </label>
                        <input type="text" 
                               name="title" 
                               placeholder="e.g., Beautiful Beachfront Villa with Pool"
                               class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all duration-300 text-lg"
                               required>
                        <p class="text-gray-500 text-sm mt-2">Choose a catchy title that highlights your property's best features.</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-gray-800 font-semibold mb-3 text-lg">
                            <i class="fas fa-align-left text-rose-500 mr-2"></i>Description
                        </label>
                        <textarea 
                            name="description" 
                            rows="6"
                            placeholder="Describe your rental property in detail. Mention amenities, nearby attractions, and what makes it special..."
                            class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all duration-300 text-lg resize-none"
                            required></textarea>
                        <p class="text-gray-500 text-sm mt-2">Detailed descriptions help travelers understand what to expect.</p>
                    </div>

                    <!-- Location Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- City -->
                        <div>
                            <label class="block text-gray-800 font-semibold mb-3 text-lg">
                                <i class="fas fa-city text-rose-500 mr-2"></i>City
                            </label>
                            <input type="text" 
                                   name="city" 
                                   placeholder="e.g., Marrakech"
                                   class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all duration-300 text-lg"
                                   required>
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-gray-800 font-semibold mb-3 text-lg">
                                <i class="fas fa-map-marker-alt text-rose-500 mr-2"></i>Address
                            </label>
                            <input type="text" 
                                   name="address" 
                                   placeholder="Full address (shown after booking)"
                                   class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all duration-300 text-lg"
                                   required>
                        </div>
                    </div>

                    <!-- Price & Capacity -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Price -->
                        <div>
                            <label class="block text-gray-800 font-semibold mb-3 text-lg">
                                <i class="fas fa-money-bill-wave text-rose-500 mr-2"></i>Price per Night (MAD)
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-500 font-bold">MAD</span>
                                <input type="number" 
                                       step="0.01" 
                                       name="price_per_night" 
                                       placeholder="0.00"
                                       class="w-full pl-20 pr-5 py-4 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all duration-300 text-lg"
                                       required>
                            </div>
                            <p class="text-gray-500 text-sm mt-2">Set a competitive price for your area.</p>
                        </div>

                        <!-- Max Guests -->
                        <div>
                            <label class="block text-gray-800 font-semibold mb-3 text-lg">
                                <i class="fas fa-user-friends text-rose-500 mr-2"></i>Maximum Guests
                            </label>
                            <input type="number" 
                                   name="max_guests" 
                                   placeholder="e.g., 4"
                                   min="1"
                                   class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all duration-300 text-lg"
                                   required>
                            <p class="text-gray-500 text-sm mt-2">How many guests can your property accommodate?</p>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-gray-800 font-semibold mb-3 text-lg">
                            <i class="fas fa-camera text-rose-500 mr-2"></i>Property Image
                        </label>
                        
                        <!-- Upload Area -->
                        <div class="file-upload mt-4">
                            <div class="border-3 border-dashed border-gray-300 rounded-2xl p-10 text-center bg-gray-50 hover:bg-gray-100 transition-all duration-300 cursor-pointer relative">
                                <input type="file" 
                                       name="image" 
                                       id="image-upload"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                       accept="image/*">
                                
                                <div class="pointer-events-none">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                    <h4 class="text-xl font-semibold text-gray-700 mb-2">Upload Property Image</h4>
                                    <p class="text-gray-500 mb-4">Drag & drop or click to browse</p>
                                    <p class="text-sm text-gray-400">Recommended: High-quality JPEG or PNG (Max 5MB)</p>
                                </div>
                            </div>
                            
                            <!-- Image Preview -->
                            <div id="image-preview" class="mt-6 hidden">
                                <p class="text-gray-700 font-semibold mb-3">Preview:</p>
                                <div class="relative inline-block">
                                    <img id="preview-image" 
                                         src="" 
                                         alt="Preview" 
                                         class="w-64 h-48 object-cover rounded-xl shadow-lg">
                                    <button type="button" 
                                            id="remove-image" 
                                            class="absolute -top-2 -right-2 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-center pt-8 border-t">
                        <a href="dashboard_host.php" 
                           class="flex items-center text-gray-700 hover:text-rose-600 font-semibold mb-6 sm:mb-0 transition-colors duration-200 text-lg">
                            <i class="fas fa-arrow-left mr-3"></i> Back to Dashboard
                        </a>
                        
                        <button type="submit" 
                                class="bg-gradient-to-r from-rose-600 to-pink-600 text-white px-10 py-4 rounded-xl font-bold text-lg hover:from-rose-700 hover:to-pink-700 transition-all duration-200 transform hover:-translate-y-1 shadow-lg hover:shadow-xl flex items-center">
                            <i class="fas fa-plus-circle mr-3"></i> Add Rental Property
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tips Section -->
            <div class="mt-12 bg-blue-50 rounded-2xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-lightbulb text-yellow-500 mr-3"></i> Tips for a Great Listing
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-camera text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">High-Quality Photos</h4>
                        <p class="text-gray-600 text-sm">Use clear, well-lit photos that showcase your property's best features.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-edit text-green-600 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Detailed Description</h4>
                        <p class="text-gray-600 text-sm">Be specific about amenities, nearby attractions, and house rules.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-tag text-purple-600 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Competitive Pricing</h4>
                        <p class="text-gray-600 text-sm">Research similar properties in your area to set a fair price.</p>
                    </div>
                </div>
            </div>
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

    <!-- Image Upload Preview Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageUpload = document.getElementById('image-upload');
            const imagePreview = document.getElementById('image-preview');
            const previewImage = document.getElementById('preview-image');
            const removeButton = document.getElementById('remove-image');
            
            if (imageUpload) {
                imageUpload.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        const file = e.target.files[0];
                        const reader = new FileReader();
                        
                        reader.onload = function(event) {
                            previewImage.src = event.target.result;
                            imagePreview.classList.remove('hidden');
                        }
                        
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    imagePreview.classList.add('hidden');
                    previewImage.src = '';
                    imageUpload.value = '';
                });
            }
            
            // Drag and drop functionality
            const uploadArea = document.querySelector('.file-upload .border-dashed');
            
            if (uploadArea) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, preventDefaults, false);
                });
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, unhighlight, false);
                });
                
                function highlight() {
                    uploadArea.classList.add('bg-rose-50', 'border-rose-300');
                }
                
                function unhighlight() {
                    uploadArea.classList.remove('bg-rose-50', 'border-rose-300');
                }
                
                uploadArea.addEventListener('drop', handleDrop, false);
                
                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    
                    if (files.length > 0) {
                        imageUpload.files = files;
                        const event = new Event('change');
                        imageUpload.dispatchEvent(event);
                    }
                }
            }
        });
    </script>
</body>
</html>