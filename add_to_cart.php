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
    // Handle case where user doesn't exist
    session_destroy();
    header('Location: login.php');
    exit;
}

$user_id = $user['user_id'];

// Handle Add to Cart action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    // Validate and sanitize input
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
    
    if ($product_id && $quantity) {
        // Check if product already exists in cart
        $check_stmt = $conn->prepare("SELECT * FROM tbl_cart WHERE customer_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $existing_item = $check_stmt->get_result()->fetch_assoc();

        if ($existing_item) {
            // Update quantity if product exists
            $new_quantity = $existing_item['quantity'] + $quantity;
            $update_stmt = $conn->prepare("UPDATE tbl_cart SET quantity = ?, created_at = NOW() WHERE cart_id = ?");
            $update_stmt->bind_param("ii", $new_quantity, $existing_item['cart_id']);
            $update_stmt->execute();
        } else {
            // Add new item to cart
            $insert_stmt = $conn->prepare("INSERT INTO tbl_cart (customer_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $insert_stmt->execute();
        }

        // Show JavaScript alert and redirect
        echo '<script>alert("ITEM ADDED TO CART"); window.history.back();</script>';
        exit();
    } else {
        $error = "Invalid product or quantity";
    }
}

// Handle Remove from Cart action
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $delete_stmt = $conn->prepare("DELETE FROM tbl_cart WHERE cart_id = ? AND user_id = ?");
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
        $update_stmt = $conn->prepare("UPDATE tbl_cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $update_stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
        $update_stmt->execute();
    }
    
    header("Location: Cart.php");
    exit();
}
?>