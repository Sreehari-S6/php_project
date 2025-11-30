<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>BYD - About Us</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="Mainstyle/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="Mainstyle/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="Mainstyle/lib/animate/animate.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="Mainstyle/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="Mainstyle/css/style.css" rel="stylesheet">
    
    <style>
        .hero-section {
            position: relative;
            min-height: 100vh;
            background: url('Mainstyle/img/gmpc7.jpg') no-repeat center center;
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
            background-color: rgba(0, 0, 0, 0.6);
        }
        
        .about-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1000px;
            padding: 2rem;
        }
        
        .about-box {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .about-header h2 {
            color: #4e73df;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .about-header p.subtitle {
            color: #6c757d;
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .about-content {
            margin-bottom: 3rem;
        }
        
        .about-content p {
            color: #495057;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .features-section {
            margin: 3rem 0;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
        }
        
        .feature-icon {
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
        
        .feature-content h4 {
            color: #4e73df;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .feature-content p {
            color: #495057;
        }
        
        .team-section {
            margin-top: 4rem;
        }
        
        .team-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .team-header h3 {
            color: #4e73df;
            font-weight: 700;
        }
        
        .team-members {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
        }
        
        .team-member {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            width: 200px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .team-member:hover {
            transform: translateY(-5px);
        }
        
        .member-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 3px solid #4e73df;
        }
        
        .member-name {
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 0.25rem;
        }
        
        .member-position {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .cta-section {
            text-align: center;
            margin-top: 4rem;
            padding: 2rem;
            background: rgba(78, 115, 223, 0.1);
            border-radius: 10px;
        }
        
        .cta-section h3 {
            color: #4e73df;
            margin-bottom: 1.5rem;
        }
        
        .btn-about {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 12px 30px;
            color: white;
            transition: all 0.3s;
        }
        
        .btn-about:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
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

    <!-- About Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="about-container wow fadeInUp" data-wow-delay="0.3s">
            <div class="about-box">
                <div class="about-header">
                    <h2>About BYD</h2>
                    <p class="subtitle">Build Your Dream - Redefining the PC Building Experience</p>
                </div>
                
                <div class="about-content">
                    <p>At BYD (Build Your Dream), we're redefining the PC building experience by making it simple, smart, and stress-free. Whether you're a passionate gamer chasing peak performance, a content creator in need of powerful rendering capabilities, or a professional looking for a reliable workstation, our intuitive platform is designed with you in mind.</p>
                    
                    <p>Start by selecting the components you want—from CPUs and GPUs to cooling systems, cases, and more. As you build, our intelligent compatibility checker works in real time, flagging any mismatched parts and suggesting optimal alternatives. This ensures your custom setup not only works seamlessly but performs at its best—without the hassle of trial and error.</p>
                    
                    <p>Once your build is finalized, you can purchase all the components directly through our trusted, competitively priced store. Prefer a hands-off approach? No problem. Our expert technicians can assemble your system with precision, test it thoroughly, and deliver it ready to use—so you can focus on what you do best.</p>
                    
                    <p>At BYD, we're more than just a PC parts store. We're your partners in performance, helping you plan, build, and bring your dream machine to life—confidently and effortlessly.</p>
                </div>
                
                <div class="features-section">
                    <h3 class="text-center mb-4" style="color: #4e73df;">Why Choose BYD?</h3>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Intelligent Compatibility</h4>
                            <p>Our real-time system checks ensure all your components work together perfectly.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Expert Assembly</h4>
                            <p>Let our certified technicians build and test your system for flawless performance.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Quality Guarantee</h4>
                            <p>We only work with trusted manufacturers and stand behind every build.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Dedicated Support</h4>
                            <p>Our team is here to help at every step of your building journey.</p>
                        </div>
                    </div>
                </div>
               
                <div class="cta-section">
                    <h3>Ready to Build Your Dream PC?</h3>
                    <p>Start your journey today with our easy-to-use platform and expert guidance.</p>
                    <a href="index.php" class="btn btn-about">Get Started Now</a>
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
    <script src="Mainstyle/js/main.js"></script>
</body>

</html>