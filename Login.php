<?php
session_start();
include("connection.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $pwd = trim($_POST["password"]);

    $sql = "SELECT * FROM tbl_login WHERE email='$email' AND password='$pwd'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['login_id'] = $row['login_id'];
        $_SESSION['user_type'] = $row['user_type'];

        switch ($row['user_type']) {
            case "admin":
                header("Location: admin_dashboard.php");
                break;
         
            case "user":
                header("Location: user_home.php");
                break;
            default:
                $error = "Unknown role.";
        }
        exit;
    } else {
        $error = "Invalid email or password.";
        echo '<script> alert("Invalid Email Or Password"); </script>';

    }
    
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>BYD - Login</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

   
</head><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>BYD - Login</title>
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
            height: 100vh;
            background: url('mainstyle/img/gmpc2.jpg') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }
        
        .registration-container {
            margin-top:100px;
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 600px;
            padding: 2rem;
        }
        
        .registration-form {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .form-label {
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 0.5rem;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-header h2 {
            color: #4e73df;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .form-header p {
            color: #6c757d;
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

    <!-- Hero Section with Login Form -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="registration-container wow fadeInUp" data-wow-delay="0.3s">
            <div class="registration-form">
                <div class="form-header">
                    <h2>Login</h2>
                    <p>Join BYD community to build your dream PC</p>
                </div>
                <form action="" method="POST">
                  
                    <div class="mb-3">
                        <label for="email" class="form-label">EMAIL ADDRESS</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"  required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">PASSWORD</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="password" required>
                    </div>
                   
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-register py-3">Login</button>
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted">Don't Have An Account ? <a href="Registration.php" class="text-primary">Create One</a></p>
                    </div>
                           <div class="text-center mt-3">
                        <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                    </div>           
                </form>
                 

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