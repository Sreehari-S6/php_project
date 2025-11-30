<?php
if (!file_exists("connection.php")) {
    die("connection.php not found");
}
include("connection.php");
// include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $pwd = $_POST['password'];
    $cpwd = $_POST['confirm_password'];

    if ($pwd !== $cpwd) {
        echo "Passwords do not match!";
        exit;
    }


    // 1. Insert into tbl_login
    #$hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
    $role = "user";
    $login_sql = "INSERT INTO tbl_login (email, password, user_type) VALUES ('$email', '$pwd', '$role')";
    if (mysqli_query($conn, $login_sql)) {
        $login_id = mysqli_insert_id($conn); // get last inserted login_id

        // 2. Insert into tbl_user
        $insert_user = "INSERT INTO tbl_user (login_id, name, email, phone_no)
                             VALUES ('$login_id', '$name', '$email', '$phone')";

         if (mysqli_query($conn, $insert_user)) {
            echo '
            <!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    window.onload = function() {
                        Swal.fire({
                            icon: "success",
                            title: "Registered!",
                            text: "Registration successful",
                            confirmButtonText: "Wait for approval"
                        }).then(() => {
                            window.location.href = "index.php";
                        });
                    };
                </script>
            </body>
            </html>';
            exit;
        } else {
            echo "Error in user insert: " . mysqli_error($conn);
        }
    } else {
        echo "Error in login insert: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>BYD - Registration</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

   
</head><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>BYD - Registration</title>
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
            background: url('mainstyle/img/gmpc8.jpg') no-repeat center center;
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

    <!-- Hero Section with Registration Form -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="registration-container wow fadeInUp" data-wow-delay="0.3s">
            <div class="registration-form">
                <div class="form-header">
                    <h2>Create Your Account</h2>
                    <p>Join BYD community to build your dream PC</p>
                </div>
                <form action="" method="POST" onsubmit="return validateForm()" >
                    <div class="mb-3">
                        <label for="name" class="form-label">FULL NAME</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" onblur="validateName(this)" required>
                                        <span class="error" id="nameError"></span>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">PHONE NUMBER</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Exclude +91 " onblur="validatephone(this)" required>
                                        <span class="error" id="phoneError"></span>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">EMAIL ADDRESS</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"  onblur="validateEmail(this)" required>
                  
                                      <span class="error" id="emailError"></span>  </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">PASSWORD</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password"  onblur="validatePassword(this)" required>
                                      <span class="error" id="passwordFieldError"></span>
                    </div>
                    <div class="mb-4">

                        <label for="confirmPassword" class="form-label">CONFIRM PASSWORD</label>
                        <input type="password" class="form-control" id="confirm password" name="confirm password" placeholder="Re-enter your password"  onblur="validateConfirmPassword(this)" required>
                  
                                      <span class="error" id="passwordError"></span>  </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-register py-3">Register Now</button>
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted">Already have an account? <a href="Login.php" class="text-primary">Login here</a></p>
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
<script src="Mainstyle/js/registration_validation.js"></script>
    <!-- Template Javascript -->
    <script src="mainstyle/js/main.js"></script>
</body>

</html>