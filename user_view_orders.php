<?php
session_start();
require_once 'Connection.php'; // Your database connection file

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit();
}

$login_id = $_SESSION['login_id'];
$error = '';
$success = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM tbl_user WHERE login_id = ?");
$stmt->bind_param("s", $login_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $error = "User not found!";
    session_destroy();
    header("Location: login.php");
    exit();
}

$user_id = $user['user_id'];

// Check which option was selected
$selected_option = isset($_GET['option']) ? $_GET['option'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .orders-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .orders-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .orders-header h2 {
            color: #343a40;
            font-weight: 600;
        }
        .option-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .option-header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        .option-body {
            padding: 30px;
            text-align: center;
        }
        .option-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #0d6efd;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .option-description {
            color: #6c757d;
            margin-bottom: 20px;
        }
        .btn-option {
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .btn-option:hover {
            background-color: #0b5ed7;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="user_home.php">
                <i class="fas fa-desktop me-2"></i>BYD
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="mainstyle/img/user_avatar.jpg" alt="User" class="user-avatar me-2">
                            <span><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="user_home.php"><i class="fas fa-home me-2"></i>Home</a></li>
                            <li><a class="dropdown-item" href="user_edit_profile.php"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
                            <li><a class="dropdown-item" href="user_orders.php"><i class="fas fa-clipboard-list me-2"></i>Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container orders-container">
        <div class="orders-header">
            <h2><i class="fas fa-clipboard-list me-2"></i>My Orders</h2>
            <p class="text-muted">Choose what type of orders you want to view</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Build Orders Option -->
            <div class="col-md-6 mb-4">
                <div class="option-card" onclick="location.href='user_view_build_orders.php'">
                    <div class="option-header">
                        <h4>Build Orders</h4>
                    </div>
                    <div class="option-body">
                        <div class="option-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <p class="option-description">
                            View your custom PC build orders, track their progress, and see the specifications of each build.
                        </p>
                        <a href="user_view_build_orders.php" class="btn-option">View Build Orders</a>
                    </div>
                </div>
            </div>
            
            <!-- Parts Purchase Orders Option -->
            <div class="col-md-6 mb-4">
                <div class="option-card" onclick="location.href='user_view_parts_order.php'">
                    <div class="option-header">
                        <h4>Parts Purchase Orders</h4>
                    </div>
                    <div class="option-body">
                        <div class="option-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <p class="option-description">
                            View your individual computer part purchases, order history, and delivery status.
                        </p>
                        <a href="user_view_parts_order.php" class="btn-option">View Parts Orders</a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($selected_option): ?>
            <div class="mt-5">
                <h3 class="mb-4">
                    <?php 
                    if ($selected_option === 'build') {
                        echo 'Build Orders';
                    } elseif ($selected_option === 'parts') {
                        echo 'Parts Purchase Orders';
                    }
                    ?>
                </h3>
                
                <?php
                // Here you would fetch and display the appropriate orders based on the selected option
                if ($selected_option === 'build') {
                    // Fetch and display build orders
                    echo '<div class="alert alert-info">Build orders will be displayed here.</div>';
                } elseif ($selected_option === 'parts') {
                    // Fetch and display parts orders
                    echo '<div class="alert alert-info">Parts purchase orders will be displayed here.</div>';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> BYD. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>