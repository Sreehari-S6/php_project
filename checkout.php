<?php
include("connection.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit;
}

$login_id = $_SESSION['login_id'];

// Get user details
$user_stmt = $conn->prepare("SELECT * FROM tbl_user WHERE login_id = ?");
$user_stmt->bind_param("i", $login_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$user_id = $user['user_id'];

// Get user's delivery addresses
$address_sql = "SELECT * FROM tbl_delivery_details WHERE user_id='$user_id'";
$address_result = mysqli_query($conn, $address_sql);
$addresses = [];
while ($row = mysqli_fetch_assoc($address_result)) {
    $addresses[] = $row;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $selected_address_id = $_POST['selected_address_id'];
    $payment_method = $_POST['payment_method'];
    $grand_total = $_POST['grand_total'];
    
    // If using a new address, insert it first
    if ($selected_address_id === 'new') {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $zip_code = $_POST['zip_code'];
        $phone_number = $_POST['phone_number'];
        
        // Insert new delivery address
        $delivery_stmt = $conn->prepare("INSERT INTO tbl_delivery_details
                                        (name, address, city, zip_code, phone_number, user_id) 
                                        VALUES (?, ?, ?, ?, ?, ?)");
        $delivery_stmt->bind_param("sssssi", $name, $address, $city, $zip_code, $phone_number, $user_id);
        $delivery_stmt->execute();
        $delivery_address_id = $conn->insert_id;
    } else {
        // Use the existing address ID
        $delivery_address_id = $selected_address_id;
    }

    // Start transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        // Determine payment method for order master
        $payment_method_db = ($payment_method === 'card') ? 'CARD' : 'UPI';
        
        // Insert order into order master
        $order_stmt = $conn->prepare("INSERT INTO tbl_ordermaster 
                                     (user_id, status, date, total_amount, payment_method, delivery_address_id) 
                                     VALUES (?, 'PAID', CURDATE(), ?, ?, ?)");
        $order_stmt->bind_param("idsi", $user_id, $grand_total, $payment_method_db, $delivery_address_id);
        $order_stmt->execute();
        $ordermaster_id = $conn->insert_id;
        
        // Fetch cart items to insert into order details
        $cart_query = "SELECT product_id, quantity FROM tbl_cart WHERE customer_id = ?";
        $cart_stmt = $conn->prepare($cart_query);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        
        // Insert each cart item into order details and update stock
        while ($cart_item = $cart_result->fetch_assoc()) {
            // Get product price and current stock
            $price_stmt = $conn->prepare("SELECT price, stock FROM tbl_products WHERE product_id = ?");
            $price_stmt->bind_param("i", $cart_item['product_id']);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result();
            $product = $price_result->fetch_assoc();
            
            // Check if there's enough stock
            if ($product['stock'] < $cart_item['quantity']) {
                throw new Exception("Insufficient stock for product ID: " . $cart_item['product_id']);
            }
            
            $total_price = $product['price'] * $cart_item['quantity'];
            
            // Insert into order details
            $detail_stmt = $conn->prepare("INSERT INTO tbl_orderdetails 
                                          (ordermaster_id, product_id, quantity, price) 
                                          VALUES (?, ?, ?, ?)");
            $detail_stmt->bind_param("iiid", $ordermaster_id, $cart_item['product_id'], 
                                    $cart_item['quantity'], $total_price);
            $detail_stmt->execute();
            
            // Update product stock - reduce the quantity purchased
            $update_stock_stmt = $conn->prepare("UPDATE tbl_products SET stock = stock - ? WHERE product_id = ?");
            $update_stock_stmt->bind_param("ii", $cart_item['quantity'], $cart_item['product_id']);
            $update_stock_stmt->execute();
        }
        
        // Delete cart items for this user
        $delete_cart_stmt = $conn->prepare("DELETE FROM tbl_cart WHERE customer_id = ?");
        $delete_cart_stmt->bind_param("i", $user_id);
        $delete_cart_stmt->execute();
        
        // Commit the transaction if all operations were successful
        $conn->commit();
        
        // Redirect to thank you page
        header('Location: thank_you.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback the transaction if any error occurred
        $conn->rollback();
        
        // Handle error - redirect back with error message
        $_SESSION['error'] = $e->getMessage();
        header('Location: Cart.php');
        exit;
    }
}

// Fetch cart items with model from tbl_productspecifications
$cart_query = "SELECT p.product_id, ps.model, p.price, p.image, p.stock, c.quantity 
               FROM tbl_cart c 
               JOIN tbl_products p ON c.product_id = p.product_id 
               JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
               WHERE c.customer_id = ?";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

// Calculate totals
$subtotal = 0;
$cart_items = [];
$insufficient_stock = false;

while ($item = $cart_result->fetch_assoc()) {
    // Check if there's enough stock
    if ($item['stock'] < $item['quantity']) {
        $insufficient_stock = true;
        $item['stock_warning'] = "Only " . $item['stock'] . " available";
    }
    
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $cart_items[] = $item;
}

// Sample shipping cost calculation
$shipping_cost = ($subtotal > 50) ? 0 : 5.99; // Free shipping over $50
$tax_rate = 0.18; // 18% tax
$tax_amount = $subtotal * $tax_rate;
$grand_total = $subtotal + $shipping_cost + $tax_amount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Your E-Commerce Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .checkout-container { max-width: 1200px; margin: 0 auto; }
        .order-summary { background: #f8f9fa; padding: 20px; border-radius: 8px; }
        .product-img { width: 60px; height: 60px; object-fit: cover; }
        .payment-method { margin-bottom: 15px; }
        .trust-badges img { height: 40px; margin-right: 10px; }
        .gpay-qr { max-width: 200px; display: none; margin: 15px auto; }
        .back-to-cart-btn {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .back-to-cart-btn:hover {
            background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        .payment-modal {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .payment-modal h3 {
            margin-bottom: 20px;
            color: #333;
        }
        .payment-modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }
        .payment-modal-buttons button {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .confirm-btn {
            background: #28a745;
            color: white;
        }
        .confirm-btn:hover {
            background: #218838;
        }
        .cancel-btn {
            background: #dc3545;
            color: white;
        }
        .cancel-btn:hover {
            background: #c82333;
        }
        .modal-qr-code {
            max-width: 250px;
            margin: 20px auto;
            display: block;
            border: 1px solid #ddd;
            padding: 10px;
            background: white;
        }
        .thank-you-modal {
            text-align: center;
        }
        .thank-you-modal .bi-check-circle {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .stock-warning {
            color: #dc3545;
            font-size: 0.875rem;
            font-weight: bold;
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
        .new-address-form {
            display: none;
            margin-top: 15px;
            padding: 15px;
            border: 1px dashed #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container checkout-container my-5">
        <?php if ($insufficient_stock): ?>
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> Some items in your cart have insufficient stock. Please update quantities before proceeding.
        </div>
        <?php endif; ?>
        
        <a href="Cart.php" class="back-to-cart-btn">
            <i class="bi bi-arrow-left"></i> Back to Cart
        </a>
        
        <form method="POST" action="checkout.php" id="checkoutForm">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="grand_total" value="<?= $grand_total ?>">
            <div class="row">
                <div class="col-md-8">
                    <h2 class="mb-4">Checkout</h2>
                    
                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5>Delivery Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($addresses) > 0): ?>
                                <h6>Select Delivery Address</h6>
                                <div id="addressSelection">
                                    <?php foreach ($addresses as $index => $address): ?>
                                        <div class="address-card" onclick="selectAddress(<?= $address['delivery_id'] ?>)">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="selected_address_id" 
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
                                    
                                    <div class="address-card" onclick="selectNewAddress()">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="selected_address_id" 
                                                   id="newAddress" value="new">
                                            <label class="form-check-label" for="newAddress">
                                                <strong>Add New Address</strong>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="newAddressForm" class="new-address-form">
                                    <h6>Add New Address</h6>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Street Address</label>
                                        <input type="text" class="form-control" id="address" name="address" required>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="zip_code" class="form-label">ZIP/Postal Code</label>
                                            <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>You don't have any saved addresses. Please add a delivery address.</p>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Street Address</label>
                                    <input type="text" class="form-control" id="address" name="address" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="zip_code" class="form-label">ZIP/Postal Code</label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                </div>
                                <input type="hidden" name="selected_address_id" value="new">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5>Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="payment-method">
                                <input type="radio" class="btn-check" name="payment_method" id="creditCard" 
                                       value="card" autocomplete="off" checked>
                                <label class="btn btn-outline-primary w-100 text-start p-3" for="creditCard">
                                    <i class="bi bi-credit-card me-2"></i> Card Payment
                                </label>
                            </div>
                            
                            <div id="creditCardForm" class="mt-3">
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="cardNumber" name="card_number" placeholder="1234 5678 9012 3456">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="expiryDate" class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiryDate" name="expiry_date" placeholder="MM/YY">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cardName" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="cardName" name="card_name">
                                </div>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" class="btn-check" name="payment_method" id="gpay" 
                                       value="gpay" autocomplete="off">
                                <label class="btn btn-outline-primary w-100 text-start p-3" for="gpay">
                                    <img src="Mainstyle/img/gpay.png" alt="GPay" style="height: 20px;" class="me-2"> GPay
                                </label>
                            </div>
                            
                            <div id="gpayQr" class="gpay-qr">
                                <img src="Mainstyle/img/gpay.jpg" alt="GPay QR Code" class="img-fluid">
                                <p class="text-center mt-2">Scan this QR code to complete payment</p>
                            </div>
                            
                            <div class="trust-badges mt-4">
                                <img src="Mainstyle/img/ssl-secured.png" alt="SSL Secure">
                                <img src="Mainstyle/img/pci.png" alt="PCI Compliant">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-md-4">
                    <div class="order-summary sticky-top" style="top: 20px;">
                        <h4 class="mb-4">Order Summary</h4>
                        
                        <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="d-flex">
                                <img src="Uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['model']); ?>" class="product-img me-3">
                                <div>
                                    <h6><?php echo htmlspecialchars($item['model']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                    <?php if (isset($item['stock_warning'])): ?>
                                    <div class="stock-warning"><?php echo $item['stock_warning']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span>₹<?php echo number_format($shipping_cost, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between fw-bold mb-4">
                            <span>Total</span>
                            <span>₹<?php echo number_format($grand_total, 2); ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="couponCode" class="form-label">Coupon Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="couponCode" placeholder="Enter code">
                                <button class="btn btn-outline-secondary" type="button">Apply</button>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-primary w-100 py-3" id="placeOrderBtn" <?= $insufficient_stock ? 'disabled' : '' ?>>Place Order</button>
                        
                        <?php if ($insufficient_stock): ?>
                        <div class="text-center mt-2 stock-warning">
                            Please fix stock issues before placing order
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                I agree to the <a href="#">Terms & Conditions</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Payment Confirmation Modal -->
    <div class="modal-overlay" id="paymentModal">
        <div class="payment-modal">
            <h3 id="modalTitle">Confirm Payment</h3>
            <div id="modalContent">
                <!-- Content will be inserted here by JavaScript -->
            </div>
            <div class="payment-modal-buttons">
                <button class="confirm-btn" id="confirmPayment">Yes, Proceed</button>
                <button class="cancel-btn" id="cancelPayment">No, Go Back</button>
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
            document.getElementById('newAddressForm').style.display = 'none';
        }
        
        // Function to handle new address selection
        function selectNewAddress() {
            document.querySelectorAll('.address-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.getElementById('newAddress').closest('.address-card').classList.add('selected');
            document.getElementById('newAddressForm').style.display = 'block';
        }
        
        // Initialize page with first address selected
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.address-card')) {
                document.querySelector('.address-card').classList.add('selected');
            }
            
            // Initialize payment method display
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            togglePaymentMethodFields(paymentMethod);
        });

        // Toggle payment methods
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                togglePaymentMethodFields(this.value);
            });
        });
        
        // Function to toggle payment method fields
        function togglePaymentMethodFields(method) {
            if (method === 'gpay') {
                document.getElementById('creditCardForm').style.display = 'none';
                document.getElementById('gpayQr').style.display = 'block';
            } else {
                document.getElementById('creditCardForm').style.display = 'block';
                document.getElementById('gpayQr').style.display = 'none';
            }
        }

        // Handle place order button click
        document.getElementById('placeOrderBtn').addEventListener('click', function() {
            // Check terms and conditions
            if (!document.getElementById('termsCheck').checked) {
                alert('Please agree to the Terms & Conditions');
                return;
            }
            
            // Validate card details only if card payment is selected
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            if (paymentMethod === 'card') {
                const cardNumber = document.getElementById('cardNumber').value;
                const expiryDate = document.getElementById('expiryDate').value;
                const cvv = document.getElementById('cvv').value;
                const cardName = document.getElementById('cardName').value;
                
                if (!cardNumber || !expiryDate || !cvv || !cardName) {
                    alert('Please fill in all card details');
                    return;
                }
                
                // Simple card number validation
                if (cardNumber.replace(/\s/g, '').length < 16) {
                    alert('Please enter a valid card number');
                    return;
                }
            }
            
            // Get selected payment method
            const modal = document.getElementById('paymentModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            
            // Prepare modal content based on payment method
            if (paymentMethod === 'card') {
                const cardNumber = document.getElementById('cardNumber').value;
                const expiryDate = document.getElementById('expiryDate').value;
                const cardName = document.getElementById('cardName').value;
                
                modalTitle.textContent = 'Confirm Card Payment';
                modalContent.innerHTML = `
                    <p>You are about to pay with:</p>
                    <p><strong>Card ending in:</strong> ${cardNumber.slice(-4)}</p>
                    <p><strong>Expiry:</strong> ${expiryDate}</p>
                    <p><strong>Name on card:</strong> ${cardName}</p>
                    <p><strong>Total amount:</strong> ₹<?php echo number_format($grand_total, 2); ?></p>
                    <p>Do you want to proceed with this payment?</p>
                `;
            } else {
                modalTitle.textContent = 'Confirm GPay Payment';
                modalContent.innerHTML = `
                    <p>Please scan the QR code below to complete your payment:</p>
                    <img src="Mainstyle/img/gpay.jpg" alt="GPay QR Code" class="modal-qr-code">
                    <p><strong>Total amount:</strong> ₹<?php echo number_format($grand_total, 2); ?></p>
                    <p>Have you completed the payment?</p>
                `;
            }
            
            // Show the modal
            modal.style.display = 'flex';
        });
        
        // Handle confirm button click
        document.getElementById('confirmPayment').onclick = function() {
            document.getElementById('paymentModal').style.display = 'none';
            
            // Create and show thank you modal
            const thankYouModal = document.createElement('div');
            thankYouModal.className = 'modal-overlay';
            thankYouModal.style.display = 'flex';
            thankYouModal.innerHTML = `
                <div class="payment-modal thank-you-modal">
                    <i class="bi bi-check-circle"></i>
                    <h3>Thank You For Your Order!</h3>
                    <p>Your order has been placed successfully.</p>
                    <p><strong>Order total:</strong> ₹<?php echo number_format($grand_total, 2); ?></p>
                    <div class="payment-modal-buttons">
                        <button class="confirm-btn" onclick="window.location.href='thank_you.php'">Continue</button>
                    </div>
                </div>
            `;
            document.body.appendChild(thankYouModal);
            
            // Submit the form to process the order and clear cart
            document.getElementById('checkoutForm').submit();
        };
        
        // Handle cancel button click
        document.getElementById('cancelPayment').onclick = function() {
            document.getElementById('paymentModal').style.display = 'none';
        };
    </script>
</body>
</html>