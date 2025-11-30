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

// Get cart items for the logged-in user with model from tbl_productspecifications and image from tbl_products
$cart_query = "SELECT c.cart_id, c.quantity, p.product_id, ps.model, p.price, p.image 
               FROM tbl_cart c 
               JOIN tbl_products p ON c.product_id = p.product_id 
               JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
               WHERE c.customer_id = ?";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result();

// Handle Remove from Cart action
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $delete_stmt = $conn->prepare("DELETE FROM tbl_cart WHERE cart_id = ? AND customer_id = ?");
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    $delete_stmt->execute();
    
    header("Location: Cart.php");
    exit();
}

// Handle Update Quantity action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
    $new_quantity = max(1, intval($_POST['quantity']));
    
    if ($cart_id) {
        $update_stmt = $conn->prepare("UPDATE tbl_cart SET quantity = ? WHERE cart_id = ? AND customer_id = ?");
        $update_stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
        $update_stmt->execute();
    }
    
    header("Location: Cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('Headers/user_parts_nav.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BYD-Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #777599ff;
        }
        .cart-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        .cart-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        .item-total {
            font-weight: bold;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
        }
        .reset-btn {
            margin-left: 5px;
            cursor: pointer;
            color: #6c757d;
        }
        .reset-btn:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container cart-container">
        <h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>BYD - Your Shopping Cart</h2>
        
        <?php if ($cart_items->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        // Reset pointer to beginning of result set
                        $cart_items->data_seek(0);
                        while ($item = $cart_items->fetch_assoc()): 
                            $item_total = $item['price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="Uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['model']); ?>" 
                                             class="cart-item-image me-3">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['model']); ?></h6>
                                            <small class="text-muted">Product ID: <?php echo $item['product_id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <form method="POST" action="" class="quantity-controls">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" class="form-control quantity-input me-2">
                                        <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-secondary">
                                            <i class="far fa-hand-pointer"></i>
                                        </button>
                                        <span class="reset-btn" onclick="resetQuantity(this)">
                                            <i class="fas fa-undo"></i>
                                        </span>
                                    </form>
                                </td>
                                <td class="item-total">₹<?php echo number_format($item_total, 2); ?></td>
                                <td>
                                    <a href="?remove=<?php echo $item['cart_id']; ?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-5 offset-md-7">
                    <div class="cart-summary">
                        <h5 class="mb-3">Cart Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <a href="checkout.php" class="btn btn-primary">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                            <a href="Parts.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Browse our products and add items to your cart</p>
                <a href="Parts.php" class="btn btn-primary mt-3">
                    <i class="fas fa-store me-2"></i>Shop Now
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to reset quantity to 1
        function resetQuantity(element) {
            const form = element.closest('form');
            const quantityInput = form.querySelector('input[name="quantity"]');
            quantityInput.value = 1;
            
            // Trigger the update
            updateItemTotal(quantityInput);
        }
        
        // Function to update item total when quantity changes
        function updateItemTotal(input) {
            // Get the row containing this input
            const row = input.closest('tr');
            // Get the price from the second column (index 1)
            const priceText = row.cells[1].textContent;
            const price = parseFloat(priceText.replace('₹', '').replace(',', ''));
            // Calculate new total
            const quantity = parseInt(input.value);
            const total = price * quantity;
            // Update the total cell (index 3)
            row.cells[3].textContent = '₹' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            
            // Recalculate subtotal
            updateCartSummary();
        }
        
        // Function to update cart summary
        function updateCartSummary() {
            let newSubtotal = 0;
            document.querySelectorAll('tbody tr').forEach(row => {
                const itemTotalText = row.cells[3].textContent;
                const itemTotal = parseFloat(itemTotalText.replace('₹', '').replace(',', ''));
                newSubtotal += itemTotal;
            });
            
            // Update subtotal and total in summary
            document.querySelectorAll('.cart-summary span:nth-child(2)').forEach(span => {
                if (span.textContent !== 'Free') {
                    span.textContent = '₹' + newSubtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
            });
        }
        
        // Add event listeners for quantity changes
        document.querySelectorAll('input[name="quantity"]').forEach(input => {
            input.addEventListener('change', function() {
                updateItemTotal(this);
            });
        });
    </script>
</body>
</html>