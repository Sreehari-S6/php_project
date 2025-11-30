<?php
include("connection.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit();
}

$login_id = $_SESSION['login_id'];

// Get user information and user_id using login_id
$sql = "SELECT u.* FROM tbl_user u 
        JOIN tbl_login l ON u.login_id = l.login_id 
        WHERE l.login_id='$login_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_array($result);
$user_id = $user['user_id'];

// Get the selected address ID from session
$selected_address_id = $_SESSION['order_details']['address_id'];

// Get the PC build components from session
$pc_build = $_SESSION['pc_build'];

// Get payment method from session
$payment_method = $_SESSION['order_details']['payment_method'];
// Convert payment method to required format
if ($payment_method === 'UPI') {
    $payment_method_db = 'UPI';
} else if ($payment_method === 'Credit/Debit Card') {
    $payment_method_db = 'CARD';
} else {
    $payment_method_db = 'OTHER'; // Fallback for other payment methods
}

// Calculate total amount
$total = 0;
foreach ($pc_build as $component) {
    if ($component) {
        $total += $component['price'];
    }
}

if ($total > 70000) {
    $buildingFee = 5500;
} else {
    $buildingFee = 3800;
}

$tax = $total * 0.18;
$grandTotal = $total + $tax + $buildingFee;
$roundedTotal = round($grandTotal);

// Get current date
$current_date = date('Y-m-d H:i:s');

// Insert into tbl_build
$insert_sql = "INSERT INTO tbl_build (
                user_id, 
                ram_id, 
                case_id, 
                storage_id, 
                build_date, 
                motherboard_id, 
                processor_id, 
                status, 
                date, 
                payment_status, 
                total_amount
              ) VALUES (
                '$user_id',
                '" . $pc_build['ram']['ram_id'] . "',
                '" . $pc_build['case']['case_id'] . "',
                '" . $pc_build['storage']['storage_id'] . "',
                '$current_date',
                '" . $pc_build['motherboard']['motherboard_id'] . "',
                '" . $pc_build['processor']['processor_id'] . "',
                'Order Placed',
                '$current_date',
                'PAID',
                '$roundedTotal'
              )";

if (mysqli_query($conn, $insert_sql)) {
    $build_id = mysqli_insert_id($conn);
    
    // Insert into tbl_build_ordermaster
    $insert_order_sql = "INSERT INTO tbl_build_ordermaster (
                        user_id, 
                        status, 
                        date, 
                        total_amount, 
                        payment_method, 
                        delivery_address_id, 
                        build_id
                    ) VALUES (
                        '$user_id',
                        'TO BE BUILD',
                        '$current_date',
                        '$roundedTotal',
                        '$payment_method_db',
                        '$selected_address_id',
                        '$build_id'
                    )";
    
    if (mysqli_query($conn, $insert_order_sql)) {
        // Clear the PC build session
        unset($_SESSION['pc_build']);
        unset($_SESSION['order_details']);
        
        // Display success page instead of redirecting
        // header("Location: payment_success.php?order_id=$build_id");
        // exit();
    } else {
        // Handle error
        echo "Error inserting into tbl_build_ordermaster: " . mysqli_error($conn);
        exit();
    }
} else {
    // Handle error
    echo "Error: " . $insert_sql . "<br>" . mysqli_error($conn);
    // You might want to redirect to an error page
    // header("Location: payment_error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build Order Submitted Successfully</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .success-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 40px;
            text-align: center;
            max-width: 600px;
            width: 90%;
            animation: fadeIn 0.8s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success-icon {
            color: #4CAF50;
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .message {
            color: #34495e;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .builder-name {
            color: #e74c3c;
            font-weight: bold;
            font-size: 20px;
        }
        
        .home-button {
            background: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .home-button:hover {
            background: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        .home-button:active {
            transform: translateY(0);
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #f39c12;
            opacity: 0.7;
            border-radius: 50%;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Build Order Submitted Successfully!</h1>
        <p class="message">
            Your build order has been successfully submitted. Builder <span class="builder-name">ANDY OC</span> will accept it after scrutiny. 
            Go to your profile and check for further updates.
        </p>
        <button class="home-button" onclick="window.location.href='user_home.php'">BACK TO HOME</button>
    </div>

    <script>
        // Simple confetti effect
        document.addEventListener('DOMContentLoaded', function() {
            const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6'];
            const container = document.body;
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = Math.random() * 100 + 'vh';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.transform = `scale(${Math.random() * 0.5 + 0.5})`;
                container.appendChild(confetti);
            }
        });
    </script>
</body>
</html>