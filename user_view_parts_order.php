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

// Handle cancel order request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    // Verify that the order belongs to the current user
    $verify_stmt = $conn->prepare("SELECT user_id FROM tbl_ordermaster WHERE ordermaster_id = ?");
    $verify_stmt->bind_param("i", $order_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $order_owner = $verify_result->fetch_assoc();
    
    if ($order_owner && $order_owner['user_id'] == $user_id) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete from order details first (due to foreign key constraints)
            $delete_details_stmt = $conn->prepare("DELETE FROM tbl_orderdetails WHERE ordermaster_id = ?");
            $delete_details_stmt->bind_param("i", $order_id);
            $delete_details_stmt->execute();
            
            // Delete from order master
            $delete_master_stmt = $conn->prepare("DELETE FROM tbl_ordermaster WHERE ordermaster_id = ?");
            $delete_master_stmt->bind_param("i", $order_id);
            $delete_master_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            $success = "Order #" . $order_id . " has been cancelled successfully.";
            
            // Refresh the page to show updated orders
            header("Location: user_view_parts_order.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "Error cancelling order: " . $e->getMessage();
        }
    } else {
        $error = "You are not authorized to cancel this order.";
    }
}

// Handle return product request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_product'])) {
    $order_id = $_POST['order_id'];
    $return_reason = $_POST['return_reason'];
    $additional_notes = $_POST['additional_notes'] ?? '';
    
    // Verify that the order belongs to the current user
    $verify_stmt = $conn->prepare("SELECT user_id FROM tbl_ordermaster WHERE ordermaster_id = ?");
    $verify_stmt->bind_param("i", $order_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $order_owner = $verify_result->fetch_assoc();
    
    if ($order_owner && $order_owner['user_id'] == $user_id) {
        // Insert return request into database
        $return_stmt = $conn->prepare("INSERT INTO tbl_return_requests (ordermaster_id, user_id, return_reason, additional_notes, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $return_stmt->bind_param("iiss", $order_id, $user_id, $return_reason, $additional_notes);
        
        if ($return_stmt->execute()) {
            $success = "Return request for order #" . $order_id . " has been submitted successfully.";
        } else {
            $error = "Error submitting return request: " . $conn->error;
        }
    } else {
        $error = "You are not authorized to return this order.";
    }
}

// Fetch all orders for the current user
$orders = [];
$order_stmt = $conn->prepare("
    SELECT om.ordermaster_id, om.date as order_date, om.total_amount, om.status, 
           od.product_id, od.quantity, od.price, p.image, ps.model
    FROM tbl_ordermaster om
    JOIN tbl_orderdetails od ON om.ordermaster_id = od.ordermaster_id
    JOIN tbl_products p ON od.product_id = p.product_id
    JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
    WHERE om.user_id = ?
    ORDER BY om.date DESC
");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

while ($row = $order_result->fetch_assoc()) {
    $orders[$row['ordermaster_id']]['order_date'] = $row['order_date'];
    $orders[$row['ordermaster_id']]['total_amount'] = $row['total_amount'];
    $orders[$row['ordermaster_id']]['status'] = $row['status'];
    $orders[$row['ordermaster_id']]['items'][] = [
        'product_id' => $row['product_id'],
        'model' => $row['model'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'image' => $row['image']
    ];
}

// Fetch return requests for the current user
$return_requests = [];
$return_stmt = $conn->prepare("
    SELECT rr.return_id, rr.ordermaster_id, rr.return_reason, rr.additional_notes, 
           rr.status, rr.created_at, rr.updated_at, om.total_amount
    FROM tbl_return_requests rr
    JOIN tbl_ordermaster om ON rr.ordermaster_id = om.ordermaster_id
    WHERE rr.user_id = ?
    ORDER BY rr.created_at DESC
");
$return_stmt->bind_param("i", $user_id);
$return_stmt->execute();
$return_result = $return_stmt->get_result();

while ($row = $return_result->fetch_assoc()) {
    $return_requests[$row['ordermaster_id']] = $row;
}
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
        .order-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .order-body {
            padding: 15px;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-right: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .status-pending {
            color: #ffc107;
        }
        .status-completed {
            color: #28a745;
        }
        .status-cancelled {
            color: #dc3545;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .cancel-btn {
            margin-left: 10px;
        }
        .return-btn {
            background-color: #17a2b8;
            border-color: #17a2b8;
            margin-left: 10px;
        }
        .return-btn:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .return-status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }
        .return-status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .return-status-approved {
            background-color: #d1edff;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .return-status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .return-status-completed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .refund-notification {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            animation: fadeIn 0.5s ease-in;
        }
        .refund-notification h6 {
            color: #155724;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .refund-notification p {
            color: #0f5132;
            margin-bottom: 5px;
            font-size: 14px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 10px 10px 0 0;
        }
        .reason-option {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .reason-option:hover {
            border-color: #17a2b8;
            background-color: #f8f9fa;
        }
        .reason-option.selected {
            border-color: #17a2b8;
            background-color: #e3f2fd;
        }
        .reason-radio {
            display: none;
        }
        .return-info {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 10px 15px;
            margin-top: 10px;
            border-left: 4px solid #17a2b8;
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
            <p class="text-muted">View your order history and return status</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <div class="alert alert-info text-center">
                You haven't placed any orders yet.
                <a href="user_home.php" class="alert-link">Start shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order_id => $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Order #<?php echo $order_id; ?></h5>
                                <small class="text-muted">Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?php 
                                    echo $order['status'] == 'Completed' ? 'success' : 
                                         ($order['status'] == 'Cancelled' ? 'danger' : 'warning'); 
                                ?> me-2">
                                    <?php echo $order['status']; ?>
                                </span>
                                
                                <!-- Show return status if there's a return request for this order -->
                                <?php if (isset($return_requests[$order_id])): 
                                    $return_request = $return_requests[$order_id];
                                ?>
                                    <span class="return-status-badge return-status-<?php echo $return_request['status']; ?>">
                                        <i class="fas fa-undo me-1"></i>
                                        Return <?php echo ucfirst($return_request['status']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] != 'Completed' && $order['status'] != 'Cancelled'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel order #<?php echo $order_id; ?>? This action cannot be undone.');">
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <button type="submit" name="cancel_order" class="btn btn-outline-danger btn-sm cancel-btn">
                                            <i class="fas fa-times me-1"></i>Cancel Order
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] == 'Completed' && !isset($return_requests[$order_id])): ?>
                                    <button type="button" class="btn btn-info btn-sm return-btn" data-bs-toggle="modal" data-bs-target="#returnModal" data-order-id="<?php echo $order_id; ?>">
                                        <i class="fas fa-undo me-1"></i>Return Product
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="order-body">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <img src="Uploads/<?php echo htmlspecialchars($item['image']); ?>" class="order-item-img" alt="<?php echo htmlspecialchars($item['model']); ?>">
                                <div class="flex-grow-1">
                                    <h6><?php echo htmlspecialchars($item['model']); ?></h6>
                                    <div class="text-muted">Quantity: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="text-end">
                                    <div>₹<?php echo number_format($item['price'], 2); ?></div>
                                    <div class="text-muted small">Subtotal: ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Show return request details if exists -->
                        <?php if (isset($return_requests[$order_id])): 
                            $return_request = $return_requests[$order_id];
                        ?>
                            <div class="return-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Return Request Details</h6>
                                <p class="mb-1"><strong>Reason:</strong> <?php echo htmlspecialchars($return_request['return_reason']); ?></p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    <span class="return-status-<?php echo $return_request['status']; ?>" style="padding: 2px 8px; border-radius: 12px;">
                                        <?php echo ucfirst($return_request['status']); ?>
                                    </span>
                                </p>
                                <p class="mb-1"><strong>Requested on:</strong> <?php echo date('F j, Y g:i A', strtotime($return_request['created_at'])); ?></p>
                                
                                <?php if (!empty($return_request['additional_notes'])): ?>
                                    <p class="mb-1"><strong>Additional Notes:</strong> <?php echo htmlspecialchars($return_request['additional_notes']); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($return_request['status'] == 'completed'): ?>
                                    <div class="refund-notification">
                                        <h6><i class="fas fa-check-circle me-2"></i>Return Completed Successfully!</h6>
                                        <p><strong>Refund Amount:</strong> ₹<?php echo number_format($return_request['total_amount'], 2); ?></p>
                                        <p class="mb-0"><i class="fas fa-info-circle me-1"></i>The refund amount has been processed and will be credited to your original payment method within 5-7 business days.</p>
                                    </div>
                                <?php elseif ($return_request['status'] == 'approved'): ?>
                                    <div class="alert alert-info mt-2">
                                        <i class="fas fa-clock me-2"></i>
                                        Your return has been approved. The refund process will be completed shortly.
                                    </div>
                                <?php elseif ($return_request['status'] == 'rejected'): ?>
                                    <div class="alert alert-warning mt-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Your return request has been rejected. Please contact customer support for more details.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <?php if ($order['status'] != 'Completed' && $order['status'] != 'Cancelled'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel order #<?php echo $order_id; ?>? This action cannot be undone.');">
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <button type="submit" name="cancel_order" class="btn btn-danger">
                                            <i class="fas fa-times me-1"></i>Cancel Order
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] == 'Completed' && !isset($return_requests[$order_id])): ?>
                                    <button type="button" class="btn btn-info return-btn" data-bs-toggle="modal" data-bs-target="#returnModal" data-order-id="<?php echo $order_id; ?>">
                                        <i class="fas fa-undo me-1"></i>Return Product
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <h5>Total: ₹<?php echo number_format($order['total_amount'], 2); ?></h5>
                                <h6>[Including Tax And Delivery Charges]</h6>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Return Product Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Return Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="returnForm">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="returnOrderId">
                        <input type="hidden" name="return_product" value="1">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Return Policy:</strong> Once your return is approved and completed, the refund amount will be credited to your original payment method within 5-7 business days.
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Reason for Return:</label>
                            
                            <div class="reason-option" onclick="selectReason('dont_want')">
                                <input type="radio" class="reason-radio" name="return_reason" value="I Don't Want It Anymore" id="reason1">
                                <label class="form-check-label w-100" for="reason1">
                                    <strong>I Don't Want It Anymore</strong>
                                    <div class="text-muted small">Changed my mind about the purchase</div>
                                </label>
                            </div>
                            
                            <div class="reason-option" onclick="selectReason('defective')">
                                <input type="radio" class="reason-radio" name="return_reason" value="Defective Product" id="reason2">
                                <label class="form-check-label w-100" for="reason2">
                                    <strong>Defective Product</strong>
                                    <div class="text-muted small">Product is damaged or not working properly</div>
                                </label>
                            </div>
                            
                            <div class="reason-option" onclick="selectReason('other_way')">
                                <input type="radio" class="reason-radio" name="return_reason" value="Got In Other Way" id="reason3">
                                <label class="form-check-label w-100" for="reason3">
                                    <strong>Got In Other Way</strong>
                                    <div class="text-muted small">Purchased from another source</div>
                                </label>
                            </div>
                            
                            <div class="reason-option" onclick="selectReason('others')">
                                <input type="radio" class="reason-radio" name="return_reason" value="Others" id="reason4">
                                <label class="form-check-label w-100" for="reason4">
                                    <strong>Others</strong>
                                    <div class="text-muted small">Other reasons not listed above</div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="additional_notes" class="form-label">Additional Notes (Optional):</label>
                            <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3" placeholder="Please provide any additional details about your return..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitReturnBtn" disabled>Submit Return Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> BYD. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle return modal
        var returnModal = document.getElementById('returnModal');
        returnModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var orderId = button.getAttribute('data-order-id');
            var modal = this;
            modal.querySelector('#returnOrderId').value = orderId;
            
            // Reset form
            document.getElementById('returnForm').reset();
            document.getElementById('submitReturnBtn').disabled = true;
            
            // Remove selected class from all options
            var reasonOptions = document.querySelectorAll('.reason-option');
            reasonOptions.forEach(function(option) {
                option.classList.remove('selected');
            });
        });

        // Function to select reason
        function selectReason(reasonType) {
            // Remove selected class from all options
            var reasonOptions = document.querySelectorAll('.reason-option');
            reasonOptions.forEach(function(option) {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            var clickedOption = event.currentTarget;
            clickedOption.classList.add('selected');
            
            // Check the radio button
            var radio = clickedOption.querySelector('.reason-radio');
            radio.checked = true;
            
            // Enable submit button
            document.getElementById('submitReturnBtn').disabled = false;
        }

        // Form validation
        document.getElementById('returnForm').addEventListener('submit', function(e) {
            var selectedReason = document.querySelector('input[name="return_reason"]:checked');
            if (!selectedReason) {
                e.preventDefault();
                alert('Please select a reason for return.');
                return false;
            }
            return true;
        });
    </script>
</body>
</html>