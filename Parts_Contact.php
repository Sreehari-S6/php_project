<?php
include("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BYD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0c4c8dff;
        }
        .navbar {
            background-color: #11003cff;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.8rem;
            color: #fff !important;
        }
        .search-box {
            width: 400px;
        }
        .hero-section {
            position: relative;
            padding: 80px 0;
            background: linear-gradient(rgba(12, 76, 141, 0.8), rgba(12, 76, 141, 0.8)), 
                        url('https://via.placeholder.com/1920x1080') no-repeat center center;
            background-size: cover;
            color: white;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .contact-container {
            position: relative;
            z-index: 1;
        }
        .contact-box {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        .contact-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .contact-header h2 {
            color: #11003cff;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .contact-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .contact-info {
            margin-bottom: 30px;
        }
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        .contact-icon {
            background-color: #11003cff;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .contact-icon i {
            font-size: 1.2rem;
        }
        .contact-details h4 {
            color: #11003cff;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .contact-details p {
            margin-bottom: 5px;
            color: #6c757d;
        }
        .contact-details a {
            color: #0c4c8dff;
            text-decoration: none;
        }
        .contact-details a:hover {
            text-decoration: underline;
        }
        .social-links {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .social-links h5 {
            color: #11003cff;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .btn-square {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
        }
        .nav-link.active {
            font-weight: bold;
            color: #0d6efd !important;
        }
    </style>
</head>
<body>
     <?php include('Headers/user_parts_nav.php'); ?>

    <!-- Contact Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container contact-container">
            <div class="contact-box">
                <div class="contact-header">
                    <h2>Contact Us</h2>
                    <p>Get in touch with us through any of these methods</p>
                </div>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Phone</h4>
                            <p><a href="tel:+919207927165">+91 9207927165</a></p>
                            <p>Monday - Friday, 9am - 6pm</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p><a href="mailto:dreampc@byd.com">dreampc@byd.com</a></p>
                            <p>We typically respond within 24 hours</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Address</h4>
                            <p>BYD (BUILD YOUR DREAM)<br>
                               T.B Road Kottayam<br>
                               Pin 686001
                            </p>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About BYD</h5>
                    <p>Your one-stop shop for quality products at competitive prices.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Home</a></li>
                        <li><a href="#" class="text-white">Products</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address>
                        Email: info@byd.com<br>
                        Phone: +1 234 567 890
                    </address>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>