<?php
// Start the session
session_start();

// Include your database connection
include("connection.php");

// Check if user is logged in (optional)
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit;
}

// You can fetch order details if needed
$order_id = $_GET['order_id'] ?? null; // Get order ID from URL if passed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .thank-you-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .thank-you-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        .btn-home {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-home:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .order-number {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="thank-you-container">
            <div class="thank-you-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Thank You for Your Order!</h1>
            <p class="lead">Your order has been placed successfully.</p>
            
            <?php if ($order_id): ?>
                <div class="order-details">
                    <h4><i class="fas fa-receipt"></i> Order Details</h4>
                    <p><strong>Order Number:</strong> <span class="order-number">#<?php echo htmlspecialchars($order_id); ?></span></p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Processing</span></p>
                    <p>We've sent a confirmation email to your registered email address.</p>
                </div>
            <?php endif; ?>
            
            <p>You can check your order status anytime in your account dashboard.</p>
            
            <div class="mt-4">
                <a href="user_home.php" class="btn btn-home">
                    <i class="fas fa-home"></i> Return To Home
                </a>
            </div>
            
            <div class="mt-4">
                <p>Need help? <a href="contact.php">Contact our support team</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>