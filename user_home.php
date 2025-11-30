<?php
if (!file_exists("connection.php")) {
    die("connection.php not found");
}
include("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - BYD</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #5b84ffff;
            --primary-light: #3a5bcc;
            --primary-dark: #1a3a9b;
            --dark: #121212;
            --light: #1e4e7eff;
            --gray: #6c757d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e2e2e2ff;
            color: #333;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item:hover {
            background-color: var(--primary);
            color: white;
        }

        .cart-icon {
            position: relative;
            font-size: 1.5rem;
            color: var(--primary);
        }

        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('mainstyle/img/gmpc7.jpg');
            background-size: cover;
            background-position: center;
            min-height: 60vh;
            display: flex;
            align-items: center;
            color: white;
        }

        .option-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .option-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .option-card .card-body {
            padding: 2rem;
        }

        .option-card .btn {
            width: 100%;
        }

        .section-title {
            position: relative;
            margin-bottom: 3rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }

        .welcome-message {
            background-color: var(--primary);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 3rem;
            
        }
    </style>
</head>
<body>
    <?php include("Headers/user_nav.php"); ?>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-4">Welcome to BYD</h1>
            <p class="lead mb-5">Build Your Dream PC with our premium components and expert guidance</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="welcome-message">
                <h2>Hello,<span><?php echo $row['name'];?></span>!</h2>
                <p class="mb-0">Ready to build your dream PC? Choose from our options below.</p>
            </div>

            <h2 class="section-title">What would you like to do today?</h2>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="option-card">
                        <img src="mainstyle/img/build_pc.jpg" class="card-img-top" alt="Build PC">
                        <div class="card-body text-center">
                            <h3 class="card-title">Build Your PC</h3>
                            <p class="card-text">Customize every component and let us handle the assembly</p>
                            <a href="Build.php" class="btn btn-primary btn-lg">Start Building</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="option-card">
                        <img src="mainstyle/img/parts_store.jpg" class="card-img-top" alt="Parts Store">
                        <div class="card-body text-center">
                            <h3 class="card-title">Parts Store</h3>
                            <p class="card-text">Browse and purchase individual components for your build</p>
                            <a href="Parts.php" class="btn btn-primary btn-lg">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
<!-- Centered Feedback Button with Icon -->
<div style="text-align: center; margin: 30px 0;">
    <a href="feedback.php" class="btn" 
       style="background-color: #28a745; color: white; padding: 16px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 18px; display: inline-block; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
       onmouseover="this.style.backgroundColor='#218838'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 8px rgba(0,0,0,0.2)';"
       onmouseout="this.style.backgroundColor='#28a745'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">
        <i class="fas fa-comment" style="margin-right: 8px;"></i>Give Feedback
    </a>
</div>


    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>BYD - Build Your Dream</h5>
                    <p>The best place to build your custom PC with expert guidance and premium components.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="user_home.php" class="text-white">Home</a></li>
                        <li><a href="Build.php" class="text-white">Build PC</a></li>
                        <li><a href="Parts.php" class="text-white">Parts Store</a></li>
                        <li><a href="user_edit_profile.php" class="text-white">My Account</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i> +91 9207927165</li>
                        <li><i class="fas fa-envelope me-2"></i> support@byd.com</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2023 BYD. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update cart count (example)
        function updateCartCount(count) {
            document.querySelector('.cart-count').textContent = count;
        }
        
        // You would typically get this from your backend
        updateCartCount(0);
    </script>
</body>
</html>