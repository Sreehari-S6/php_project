<?php
include("connection.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php?redirect=Build_order_payment.php");
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

// Get user's delivery addresses from tbl_delivery_details
$address_sql = "SELECT * FROM tbl_delivery_details WHERE user_id='$user_id'";
$address_result = mysqli_query($conn, $address_sql);
$addresses = [];
while ($row = mysqli_fetch_assoc($address_result)) {
    $addresses[] = $row;
}

// Calculate amounts (same as checkout page)
$total = 0;
foreach ($_SESSION['pc_build'] as $component) {
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
$advancePayment = $roundedTotal * 0.6; // 60% of total

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_payment'])) {
    $selected_address_id = $_POST['address_id'];
    $payment_method = $_POST['payment_method'];
    
    // Store order details in session for confirmation page
    $_SESSION['order_details'] = [
        'total' => $roundedTotal,
        'advance' => $advancePayment,
        'address_id' => $selected_address_id,
        'payment_method' => $payment_method,
        'building_fee' => $buildingFee,
        'tax' => $tax
    ];
    
    // Redirect to payment processing page
    header("Location: process_payment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - BYD PC BUILDER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #c7d9ecff;
            padding-top: 80px;
            color: #333;
        }
        .payment-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-top: 20px;
            margin-bottom: 20px;
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
        .payment-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .address-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .address-card:hover, .address-card.selected {
            border-color: #14304c;
            background-color: #e9f0f7;
        }
        .address-card.selected {
            border-width: 2px;
        }
        .payment-option {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover, .payment-option.selected {
            border-color: #14304c;
            background-color: #e9f0f7;
        }
        .payment-option.selected {
            border-width: 2px;
        }
        .payment-btn {
            padding: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            background: #28a745;
            border: none;
            transition: all 0.3s;
        }
        .payment-btn:hover {
            background: #218838;
            transform: scale(1.02);
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
        .amount-highlight {
            font-size: 1.5rem;
            font-weight: bold;
            color: #d32f2f;
        }
        .no-address {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        /* Modal Styles */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        }
        .modal-header {
            background-color: #14304c;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .modal-body {
            padding: 25px;
        }
        .form-label {
            font-weight: 600;
        }
        .form-control:focus {
            border-color: #14304c;
            box-shadow: 0 0 0 0.25rem rgba(20, 48, 76, 0.25);
        }
        .qr-code-container {
            text-align: center;
            padding: 20px;
        }
        .qr-code {
            max-width: 250px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: white;
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="mainstyle/img/user_avatar.jpg" alt="User" class="user-avatar me-2">
                            <span><?php echo $user['name']; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="user_edit_profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="user_view_orders.php"><i class="fas fa-clipboard-list me-2"></i>View Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Back button -->
        <a href="Build_checkout.php" class="btn btn-secondary back-button">
            <i class="fas fa-arrow-left me-2"></i> Back to Checkout
        </a>
        
        <div class="payment-container">
            <h1 class="text-center mb-4"><i class="fas fa-credit-card me-2"></i>Payment</h1>
            
            <div class="row">
                <!-- Payment Details -->
                <div class="col-md-8">
                    <div class="payment-card">
                        <h3 class="section-title">Payment Details</h3>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong></p>
                                <p><strong>Advance Payment (60%):</strong></p>
                                <p><strong>Balance Payable:</strong></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p>₹<?= number_format($roundedTotal, 2) ?></p>
                                <p class="amount-highlight">₹<?= number_format($advancePayment, 2) ?></p>
                                <p>₹<?= number_format($roundedTotal - $advancePayment, 2) ?></p>
                            </div>
                        </div>
                        
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            You need to pay 60% of the total amount as advance. The remaining amount will be collected After Build.
                        </p>
                    </div>
                    
                    <!-- Delivery Address Selection -->
                    <h3 class="section-title">Select Delivery Address</h3>
                    
                    <?php if (count($addresses) > 0): ?>
                        <form id="addressForm">
                            <?php foreach ($addresses as $index => $address): ?>
                                <div class="address-card" onclick="selectAddress(<?= $address['delivery_id'] ?>)">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="address_id" 
                                               id="address<?= $address['delivery_id'] ?>" value="<?= $address['delivery_id'] ?>" 
                                               <?= $index === 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="address<?= $address['delivery_id'] ?>">
                                            <strong><?= htmlspecialchars($address['name']) ?></strong><br>
                                            <?= htmlspecialchars($address['address']) ?><br>
                                            <?= htmlspecialchars($address['city']) ?> - 
                                            <?= htmlspecialchars($address['zip_code']) ?><br>
                                            Phone: <?= htmlspecialchars($address['phone_number']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </form>
                        
                        <div class="text-end mb-4">
                            <a href="add_address.php?redirect=Build_order_payment.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Add New Address
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="no-address">
                            <p class="mb-3"><i class="fas fa-exclamation-circle me-2"></i>You don't have any saved addresses. Please add a delivery address to continue.</p>
                            <a href="add_address.php?redirect=Build_order_payment.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Address
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Payment Method Selection -->
                    <h3 class="section-title">Select Payment Method</h3>
                    
                    <div class="payment-option selected" onclick="selectPayment('card')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cardPayment" value="card" checked>
                            <label class="form-check-label" for="cardPayment">
                                <i class="fas fa-credit-card me-2"></i> Credit/Debit Card
                            </label>
                        </div>
                    </div>
                    
                    <div class="payment-option" onclick="selectPayment('upi')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="upiPayment" value="upi">
                            <label class="form-check-label" for="upiPayment">
                                <i class="fas fa-mobile-alt me-2"></i> UPI
                            </label>
                        </div>
                    </div>
                    
                    <div class="payment-option" onclick="selectPayment('netbanking')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="netbankingPayment" value="netbanking">
                            <label class="form-check-label" for="netbankingPayment">
                                <i class="fas fa-university me-2"></i> Net Banking
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-md-4">
                    <div class="payment-card">
                        <h3 class="section-title">Order Summary</h3>
                        
                        <?php foreach ($_SESSION['pc_build'] as $type => $component): ?>
                            <?php if ($component): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= ucfirst($type) ?>:</span>
                                    <span>₹<?= number_format($component['price'], 2) ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₹<?= number_format($total, 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Building Fee:</span>
                            <span>₹<?= number_format($buildingFee, 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (18%):</span>
                            <span>₹<?= number_format($tax, 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Round Off:</span>
                            <span>₹<?= number_format($roundedTotal - $grandTotal, 2) ?></span>
                        </div>
                        
                        <hr>
                        <div class="d-flex justify-content-between total-row">
                            <strong>Total:</strong>
                            <strong>₹<?= number_format($roundedTotal, 2) ?></strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <strong>Advance (60%):</strong>
                            <strong class="amount-highlight">₹<?= number_format($advancePayment, 2) ?></strong>
                        </div>
                        
                        <?php if (count($addresses) > 0): ?>
                            <div class="mt-4">
                                <button type="button" onclick="showPaymentModal()" class="btn btn-success btn-lg w-100 payment-btn">
                                    <i class="fas fa-lock me-2"></i> Pay Now
                                </button>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg w-100 mt-4" disabled>
                                Add Address to Continue
                            </button>
                        <?php endif; ?>
                        
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

    <!-- Card Payment Modal -->
    <div class="modal fade" id="cardPaymentModal" tabindex="-1" aria-labelledby="cardPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cardPaymentModalLabel"><i class="fas fa-credit-card me-2"></i>Enter Card Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="cardPaymentForm">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="cardNumber" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" required maxlength="19">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expiryDate" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY" required maxlength="5">
                            </div>
                            <div class="col-md-6">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" placeholder="123" required maxlength="3">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="cardName" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="cardName" placeholder="Your Name Exactly As In The Card" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="button" onclick="processCardPayment()" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i> Pay ₹<?= number_format($advancePayment, 2) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- UPI Payment Modal -->
    <div class="modal fade" id="upiPaymentModal" tabindex="-1" aria-labelledby="upiPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="upiPaymentModalLabel"><i class="fas fa-mobile-alt me-2"></i>UPI Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="qr-code-container">
                        <p>Scan the QR code below using your UPI app to complete the payment</p>
                        <img src="Mainstyle/img/gpay.jpg" alt="UPI QR Code" class="qr-code img-fluid">
                        <p class="mt-3">Amount to pay: <strong>₹<?= number_format($advancePayment, 2) ?></strong></p>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="button" onclick="processUPIPayment()" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle me-2"></i> I have made the payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Banking Modal -->
    <div class="modal fade" id="netbankingPaymentModal" tabindex="-1" aria-labelledby="netbankingPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="netbankingPaymentModalLabel"><i class="fas fa-university me-2"></i>Net Banking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <p>You will be redirected to your bank's secure payment gateway</p>
                        <div class="mt-4">
                            <button type="button" onclick="processNetBankingPayment()" class="btn btn-success btn-lg">
                                <i class="fas fa-external-link-alt me-2"></i> Continue to Net Banking
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to handle address selection
        function selectAddress(addressId) {
            document.querySelectorAll('.address-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`#address${addressId}`).closest('.address-card').classList.add('selected');
            document.getElementById('selectedAddress').value = addressId;
        }
        
        // Function to handle payment method selection
        function selectPayment(method) {
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`#${method}Payment`).closest('.payment-option').classList.add('selected');
            document.getElementById('selectedPayment').value = method;
        }
        
        // Function to show appropriate payment modal
        function showPaymentModal() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'card') {
                const cardModal = new bootstrap.Modal(document.getElementById('cardPaymentModal'));
                cardModal.show();
            } else if (paymentMethod === 'upi') {
                const upiModal = new bootstrap.Modal(document.getElementById('upiPaymentModal'));
                upiModal.show();
            } else if (paymentMethod === 'netbanking') {
                const netbankingModal = new bootstrap.Modal(document.getElementById('netbankingPaymentModal'));
                netbankingModal.show();
            }
        }
        
        // Function to process card payment
        function processCardPayment() {
            // Validate card details
            const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
            const expiryDate = document.getElementById('expiryDate').value;
            const cvv = document.getElementById('cvv').value;
            const cardName = document.getElementById('cardName').value;
            
            if (!cardNumber || !expiryDate || !cvv || !cardName) {
                alert('Please fill in all card details');
                return;
            }
            
            if (cardNumber.length !== 16) {
                alert('Please enter a valid 16-digit card number');
                return;
            }
            
            // Store card last 4 digits in hidden field
            document.getElementById('cardLast4').value = cardNumber.slice(-4);
            
            // Simulate payment processing
            simulatePaymentProcessing(() => {
                document.getElementById('paymentForm').submit();
            });
        }
        
        // Function to process UPI payment
        function processUPIPayment() {
            simulatePaymentProcessing(() => {
                document.getElementById('paymentForm').submit();
            });
        }
        
        // Function to process net banking payment
        function processNetBankingPayment() {
            simulatePaymentProcessing(() => {
                document.getElementById('paymentForm').submit();
            });
        }
        
        // Simulate payment processing with loading
        function simulatePaymentProcessing(callback) {
            const btn = event.target;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
            btn.disabled = true;
            
            // Simulate API call delay
            setTimeout(() => {
                callback();
            }, 2000);
        }
        
        // Format card number with spaces
        document.getElementById('cardNumber')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });
        
        // Format expiry date
        document.getElementById('expiryDate')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
        
        // Initialize page with first address selected
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.address-card')) {
                document.querySelector('.address-card').classList.add('selected');
            }
        });
    </script>
    
    <!-- Hidden form for actual submission -->
    <form method="POST" id="paymentForm" style="display: none;">
        <input type="hidden" name="address_id" id="selectedAddress" value="<?= $addresses[0]['delivery_id'] ?? '' ?>">
        <input type="hidden" name="payment_method" id="selectedPayment" value="card">
        <input type="hidden" name="card_last4" id="cardLast4" value="">
        <input type="hidden" name="make_payment" value="1">
    </form>
</body>
</html>