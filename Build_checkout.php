<?php
include("connection.php");
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['login_id']);

// If build is not complete, redirect to builder
if (!isset($_SESSION['pc_build']) || count(array_filter($_SESSION['pc_build'])) < 5) {
    header("Location: pc_builder.php");
    exit();
}

// Get user information for navbar
if ($isLoggedIn) {
    $login_id = $_SESSION['login_id'];
    $sql = "SELECT * FROM tbl_user WHERE login_id='$login_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
}

// Calculate total cost and building fee
$total = 0;
foreach ($_SESSION['pc_build'] as $component) {
    if ($component) {
        $total += $component['price'];
    }
}

// Calculate building fee based on total cost
if ($total > 70000) {
    $buildingFee = 5500;
} else {
    $buildingFee = 3800;
}

$tax = $total * 0.18;
$grandTotal = $total + $tax + $buildingFee;

// Calculate rounded total and round off amount
$roundedTotal = round($grandTotal);
$roundOff = $roundedTotal - $grandTotal;

// Handle checkout process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_checkout'])) {
    if (!$isLoggedIn) {
        // Redirect to login page if not logged in
        header("Location: login.php?redirect=checkout.php");
        exit();
    }
    
    // Redirect to Build_order_payment.php
    header("Location: Build_order_payment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BYD PC BUILDER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #c7d9ecff;
            padding-top: 80px;
            color: #333;
        }
        .checkout-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .component-card {
            border: 1px solid #ce2e2eff;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s;
            overflow: hidden;
        }
        .component-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .component-img {
            height: 100px;
            width: 100%;
            object-fit: contain;
            padding: 10px;
            background: #ffffffff;
        }
        .component-title {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 5px;
            color: #14304c;
        }
        .component-price {
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        .summary-card {
            background: linear-gradient(to bottom, #5b90c6ff, #e9ecef);
            border-radius: 8px;
            padding: 20px;
            position: sticky;
            top: 100px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .checkout-btn {
            padding: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            background: #28a745;
            border: none;
            transition: all 0.3s;
        }
        .checkout-btn:hover {
            background: #218838;
            transform: scale(1.02);
        }
        .login-prompt {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .navbar {
            background-color: #14304c;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .cart-icon {
            position: relative;
            font-size: 1.2rem;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .back-button {
            margin-bottom: 20px;
            background: #6c757d;
            border: none;
            padding: 8px 16px;
            transition: all 0.3s;
        }
        .back-button:hover {
            background: #5a6268;
            transform: translateX(-3px);
        }
        .component-specs p {
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
            color: #6c757d;
        }
        .card-body {
            padding: 15px;
        }
        h1, h2, h3, h4, h5, h6 {
            margin-bottom: 0.8rem;
            color: #14304c;
        }
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #ffffffff;
        }
        .building-fee {
            color: #28a745;
            font-weight: 600;
        }
        .round-off {
            color: #17a2b8;
            font-weight: 500;
        }
        .total-row {
            border-top: 2px solid #14304c;
            padding-top: 10px;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .alert-success {
            background: #5df481ff;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 8px;
        }
        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #14304c;
            border-radius: 3px;
        }
        @media (max-width: 768px) {
            .summary-card {
                position: relative;
                top: 0;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="user_home.php">
                <i class="fas fa-desktop me-2"></i>BYD PC BUILDER
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <a href="Cart.php" class="nav-link position-relative">
                            <i class="fas fa-shopping-cart cart-icon">
                                <span class="cart-count"><?php echo isset($_SESSION['pc_build']) ? count($_SESSION['pc_build']) : 0; ?></span>
                            </i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <?php if ($isLoggedIn): ?>
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="mainstyle/img/user_avatar.jpg" alt="User" class="user-avatar me-2">
                                <span><?php echo $row['name']; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="user_edit_profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="user_view_orders.php"><i class="fas fa-clipboard-list me-2"></i>View Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        <?php else: ?>
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-user me-2"></i>Login
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Back button -->
        <a href="Build.php" class="btn btn-secondary back-button">
            <i class="fas fa-arrow-left me-2"></i> Back to Builder
        </a>
        
        <div class="checkout-container">
            <h1 class="text-center mb-4"><i class="fas fa-shopping-cart me-2"></i>Checkout</h1>
            
            <div class="row">
                <!-- Selected Components -->
                <div class="col-md-8">
                    <h3 class="section-title">Your PC Build</h3>
                    
                    <!-- Processor -->
                    <?php if ($_SESSION['pc_build']['processor']): ?>
                    <div class="component-card card">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="uploads/<?= htmlspecialchars($_SESSION['pc_build']['processor']['image']) ?>" class="component-img img-fluid rounded-start" alt="Processor">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5 class="component-title card-title"><?= htmlspecialchars($_SESSION['pc_build']['processor']['model']) ?></h5>
                                    <div class="component-specs mb-2">
                                        <p class="card-text"><strong>Processor:</strong> <?= htmlspecialchars($_SESSION['pc_build']['processor']['number_of_cores']) ?> cores / <?= htmlspecialchars($_SESSION['pc_build']['processor']['number_of_threads']) ?> threads</p>
                                        <p class="card-text"><strong>Clock Speed:</strong> <?= htmlspecialchars($_SESSION['pc_build']['processor']['base_clock_speed']) ?> - <?= htmlspecialchars($_SESSION['pc_build']['processor']['max_boost_clock_speed']) ?> GHz</p>
                                    </div>
                                    <p class="component-price">₹<?= number_format($_SESSION['pc_build']['processor']['price'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Motherboard -->
                    <?php if ($_SESSION['pc_build']['motherboard']): ?>
                    <div class="component-card card">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="uploads/<?= htmlspecialchars($_SESSION['pc_build']['motherboard']['image']) ?>" class="component-img img-fluid rounded-start" alt="Motherboard">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5 class="component-title card-title"><?= htmlspecialchars($_SESSION['pc_build']['motherboard']['model']) ?></h5>
                                    <div class="component-specs mb-2">
                                        <p class="card-text"><strong>RAM Support:</strong> <?= htmlspecialchars($_SESSION['pc_build']['motherboard']['supported_ram_type']) ?></p>
                                        <p class="card-text"><strong>Storage Interfaces:</strong> <?= htmlspecialchars($_SESSION['pc_build']['motherboard']['storage_interface']) ?></p>
                                    </div>
                                    <p class="component-price">₹<?= number_format($_SESSION['pc_build']['motherboard']['price'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- RAM -->
                    <?php if ($_SESSION['pc_build']['ram']): ?>
                    <div class="component-card card">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="uploads/<?= htmlspecialchars($_SESSION['pc_build']['ram']['image']) ?>" class="component-img img-fluid rounded-start" alt="RAM">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5 class="component-title card-title"><?= htmlspecialchars($_SESSION['pc_build']['ram']['model']) ?></h5>
                                    <div class="component-specs mb-2">
                                        <p class="card-text"><strong>Type:</strong> <?= htmlspecialchars($_SESSION['pc_build']['ram']['type']) ?></p>
                                        <p class="card-text"><strong>Speed:</strong> <?= htmlspecialchars($_SESSION['pc_build']['ram']['speed']) ?> MHz</p>
                                    </div>
                                    <p class="component-price">₹<?= number_format($_SESSION['pc_build']['ram']['price'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Case -->
                    <?php if ($_SESSION['pc_build']['case']): ?>
                    <div class="component-card card">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="uploads/<?= htmlspecialchars($_SESSION['pc_build']['case']['image']) ?>" class="component-img img-fluid rounded-start" alt="Case">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5 class="component-title card-title"><?= htmlspecialchars($_SESSION['pc_build']['case']['model']) ?></h5>
                                    <div class="component-specs mb-2">
                                        <p class="card-text"><strong>Form Factor:</strong> <?= htmlspecialchars($_SESSION['pc_build']['case']['form_factor']) ?></p>
                                    </div>
                                    <p class="component-price">₹<?= number_format($_SESSION['pc_build']['case']['price'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Storage -->
                    <?php if ($_SESSION['pc_build']['storage']): ?>
                    <div class="component-card card">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="uploads/<?= htmlspecialchars($_SESSION['pc_build']['storage']['image']) ?>" class="component-img img-fluid rounded-start" alt="Storage">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5 class="component-title card-title"><?= htmlspecialchars($_SESSION['pc_build']['storage']['model']) ?></h5>
                                    <div class="component-specs mb-2">
                                        <p class="card-text"><strong>Interface:</strong> <?= htmlspecialchars($_SESSION['pc_build']['storage']['interface']) ?></p>
                                    </div>
                                    <p class="component-price">₹<?= number_format($_SESSION['pc_build']['storage']['price'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Order Summary -->
                <div class="col-md-4">
                    <div class="summary-card">
                        <h3 class="section-title">Order Summary</h3>
                        
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span>₹<?= number_format($total, 2) ?></span>
                        </div>
                        
                        <div class="summary-item building-fee">
                            <span>Building Fee:</span>
                            <span>₹<?= number_format($buildingFee, 2) ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Shipping:</span>
                            <span>FREE</span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Tax (18%):</span>
                            <span>₹<?= number_format($tax, 2) ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Original Total:</span>
                            <span>₹<?= number_format($grandTotal, 2) ?></span>
                        </div>
                        
                        <div class="summary-item round-off">
                            <span>Round Off:</span>
                            <span>₹<?= number_format($roundOff, 2) ?></span>
                        </div>
                        
                        <div class="summary-item total-row">
                            <strong>Final Total:</strong>
                            <strong>₹<?= number_format($roundedTotal, 2) ?></strong>
                        </div>
                        
                        <?php if (!$isLoggedIn): ?>
                        <div class="login-prompt">
                            <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Please log in to proceed with checkout.</p>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="Build_order_payment.php">
                            <?php if ($isLoggedIn): ?>
                                <button type="submit" name="proceed_checkout" class="btn btn-success btn-lg w-100 checkout-btn">
                                    <i class="fas fa-shopping-cart me-2"></i> Confirm Order
                                </button>
                            <?php else: ?>
                                <a href="login.php?redirect=checkout.php" class="btn btn-primary btn-lg w-100 checkout-btn">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login to Checkout
                                </a>
                            <?php endif; ?>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i> Your payment information is secure and encrypted
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>