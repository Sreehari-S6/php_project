<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>BYD - Contact Us</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="mainstyle/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="mainstyle/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="mainstyle/lib/animate/animate.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="mainstyle/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="mainstyle/css/style.css" rel="stylesheet">
    
    <style>
        .hero-section {
            position: relative;
            min-height: 100vh;
            background: url('mainstyle/img/gmpc5.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 0;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }
        
        .contact-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 800px;
            padding: 2rem;
        }
        
        .contact-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        
        .contact-header {
            margin-bottom: 3rem;
        }
        
        .contact-header h2 {
            color: #4e73df;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .contact-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .contact-info {
            margin-bottom: 3rem;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .contact-icon {
            background: #4e73df;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .contact-details h4 {
            color: #4e73df;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .contact-details p, .contact-details a {
            color: #495057;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .contact-details a:hover {
            color: #4e73df;
        }
        
        .social-links {
            margin-top: 2rem;
        }
        
        .social-links h5 {
            margin-bottom: 1.5rem;
            color: #4e73df;
        }
        
        .btn-square {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            margin: 0 5px;
        }
        
        .map-container {
            margin-top: 3rem;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .map-container iframe {
            width: 100%;
            height: 300px;
            border: none;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner"></div>
    </div>
    <!-- Spinner End -->

    <?php include('Headers/indexnavbar.php'); ?>

    <!-- Contact Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="contact-container wow fadeInUp" data-wow-delay="0.3s">
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
               
                <div class="social-links">
                    <h5>Connect With Us</h5>
                    <a href="#" class="btn btn-outline-primary btn-square"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-square"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-square"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-square"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </section>

    <?php include('Headers/indexfooter.php'); ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="mainstyle/js/main.js"></script>
</body>

</html>