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

// Function to get product price and model from tbl_products and tbl_productspecifications
function getProductDetails($conn, $product_id) {
    $details_stmt = $conn->prepare("
        SELECT p.price, ps.model 
        FROM tbl_products p 
        LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id 
        WHERE p.product_id = ?
    ");
    $details_stmt->bind_param("i", $product_id);
    $details_stmt->execute();
    $details_result = $details_stmt->get_result();
    if ($details_row = $details_result->fetch_assoc()) {
        return [
            'price' => $details_row['price'],
            'model' => $details_row['model'] ?? 'Product'
        ];
    }
    $details_stmt->close();
    return ['price' => 0, 'model' => 'Product'];
}

// Fetch all build orders for the current user
$builds = [];
$build_stmt = $conn->prepare("
    SELECT 
        b.build_id, 
        b.build_date, 
        b.status, 
        b.payment_status, 
        b.total_amount,
        b.ram_id,
        b.case_id,
        b.storage_id,
        b.motherboard_id,
        b.processor_id
    FROM tbl_build b
    WHERE b.user_id = ?
    ORDER BY b.build_date DESC
");
$build_stmt->bind_param("i", $user_id);
$build_stmt->execute();
$build_result = $build_stmt->get_result();

while ($build_row = $build_result->fetch_assoc()) {
    $build_id = $build_row['build_id'];
    
    // Initialize build array
    $builds[$build_id] = [
        'build_date' => $build_row['build_date'],
        'status' => $build_row['status'],
        'payment_status' => $build_row['payment_status'],
        'total_amount' => $build_row['total_amount'],
        'components' => []
    ];
    
    // Fetch RAM details
    if (!empty($build_row['ram_id'])) {
        $ram_stmt = $conn->prepare("
            SELECT r.*, p.image, p.product_id 
            FROM tbl_ram r 
            JOIN tbl_products p ON r.product_id = p.product_id 
            WHERE r.ram_id = ?
        ");
        $ram_stmt->bind_param("i", $build_row['ram_id']);
        $ram_stmt->execute();
        $ram_result = $ram_stmt->get_result();
        if ($ram_row = $ram_result->fetch_assoc()) {
            $product_details = getProductDetails($conn, $ram_row['product_id']);
            $builds[$build_id]['components']['RAM'] = [
                'name' => $product_details['model'],
                'specs' => $ram_row['speed'] . 'MHz',
                'price' => $product_details['price'],
                'image' => $ram_row['image']
            ];
        }
        $ram_stmt->close();
    }
    
    // Fetch Case details
    if (!empty($build_row['case_id'])) {
        $case_stmt = $conn->prepare("
            SELECT c.*, p.image, p.product_id 
            FROM tbl_case_table c 
            JOIN tbl_products p ON c.product_id = p.product_id 
            WHERE c.case_id = ?
        ");
        $case_stmt->bind_param("i", $build_row['case_id']);
        $case_stmt->execute();
        $case_result = $case_stmt->get_result();
        if ($case_row = $case_result->fetch_assoc()) {
            $product_details = getProductDetails($conn, $case_row['product_id']);
            $builds[$build_id]['components']['Case'] = [
                'name' => $product_details['model'],
                'specs' => $case_row['form_factor'],
                'price' => $product_details['price'],
                'image' => $case_row['image']
            ];
        }
        $case_stmt->close();
    }
    
    // Fetch Storage details
    if (!empty($build_row['storage_id'])) {
        $storage_stmt = $conn->prepare("
            SELECT s.*, p.image, p.product_id 
            FROM tbl_storage s 
            JOIN tbl_products p ON s.product_id = p.product_id 
            WHERE s.storage_id = ?
        ");
        $storage_stmt->bind_param("i", $build_row['storage_id']);
        $storage_stmt->execute();
        $storage_result = $storage_stmt->get_result();
        if ($storage_row = $storage_result->fetch_assoc()) {
            $product_details = getProductDetails($conn, $storage_row['product_id']);
            $builds[$build_id]['components']['Storage'] = [
                'name' => $product_details['model'],
                'specs' => $storage_row['interface'],
                'price' => $product_details['price'],
                'image' => $storage_row['image']
            ];
        }
        $storage_stmt->close();
    }
    
    // Fetch Motherboard details
    if (!empty($build_row['motherboard_id'])) {
        $mb_stmt = $conn->prepare("
            SELECT m.*, p.image, p.product_id 
            FROM tbl_motherboard m 
            JOIN tbl_products p ON m.product_id = p.product_id 
            WHERE m.motherboard_id = ?
        ");
        $mb_stmt->bind_param("i", $build_row['motherboard_id']);
        $mb_stmt->execute();
        $mb_result = $mb_stmt->get_result();
        if ($mb_row = $mb_result->fetch_assoc()) {
            $product_details = getProductDetails($conn, $mb_row['product_id']);
            $builds[$build_id]['components']['Motherboard'] = [
                'name' => $product_details['model'],
                'specs' => $mb_row['supported_ram_type'] . ' - ' . $mb_row['supported_speed'] . 'MHz - ' . $mb_row['storage_interface'],
                'price' => $product_details['price'],
                'image' => $mb_row['image']
            ];
        }
        $mb_stmt->close();
    }
    
    // Fetch Processor details
    if (!empty($build_row['processor_id'])) {
        $cpu_stmt = $conn->prepare("
            SELECT p.*, prod.image, prod.product_id 
            FROM tbl_processor p 
            JOIN tbl_products prod ON p.product_id = prod.product_id 
            WHERE p.processor_id = ?
        ");
        $cpu_stmt->bind_param("i", $build_row['processor_id']);
        $cpu_stmt->execute();
        $cpu_result = $cpu_stmt->get_result();
        if ($cpu_row = $cpu_result->fetch_assoc()) {
            $product_details = getProductDetails($conn, $cpu_row['product_id']);
            // Convert specs array to string for display
            $specs_string = $cpu_row['number_of_cores'] . ' cores, ' . 
                           $cpu_row['number_of_threads'] . ' threads, ' . 
                           $cpu_row['base_clock_speed'] . 'GHz base, ' . 
                           $cpu_row['max_boost_clock_speed'] . 'GHz boost';
            
            $builds[$build_id]['components']['Processor'] = [
                'name' => $product_details['model'],
                'specs' => $specs_string,
                'price' => $product_details['price'],
                'image' => $cpu_row['image']
            ];
        }
        $cpu_stmt->close();
    }
}

$build_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Build Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .builds-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .builds-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .builds-header h2 {
            color: #343a40;
            font-weight: 600;
        }
        .build-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .build-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .build-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .build-body {
            padding: 20px;
        }
        .component-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        .component-item:hover {
            background-color: #f8f9fa;
        }
        .component-item:last-child {
            border-bottom: none;
        }
        .component-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-right: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 5px;
            background-color: white;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }
        .total-price {
            font-size: 1.3rem;
            font-weight: 600;
            color: #28a745;
        }
        .component-category {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
            background-color: #e9ecef;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .component-details {
            flex-grow: 1;
        }
        .component-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .component-specs {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .component-price {
            font-weight: 600;
            color: #28a745;
            min-width: 100px;
            text-align: right;
        }
        .build-actions {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .navbar-brand {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="user_home.php">
                <i class="fas fa-desktop me-2"></i>BYD PC Builder
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
                            <li><a class="dropdown-item" href="user_view_orders.php"><i class="fas fa-clipboard-list me-2"></i>Orders</a></li>
                            <li><a class="dropdown-item" href="user_view_build_orders.php"><i class="fas fa-tools me-2"></i>Build Orders</a></li>
                            <li><a class="dropdown-item" href="user_view_parts_orders.php"><i class="fas fa-tools me-2"></i>Parts Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container builds-container">
        <div class="builds-header">
            <h2><i class="fas fa-tools me-2"></i>My PC Build Orders</h2>
            <p class="text-muted">View your custom PC builds and their components</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($builds)): ?>
            <div class="empty-state">
                <i class="fas fa-tools"></i>
                <h4>No Build Orders Yet</h4>
                <p class="text-muted">You haven't created any PC builds yet.</p>
                <a href="build_pc.php" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>Create Your First Build
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($builds as $build_id => $build): ?>
                <div class="build-card">
                    <div class="build-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h5 class="mb-1">PC Build #<?php echo $build_id; ?></h5>
                                <small>Created on <?php echo date('F j, Y', strtotime($build['build_date'])); ?></small>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <span class="badge status-badge bg-<?php 
                                    echo $build['status'] == 'Completed' ? 'success' : 
                                         ($build['status'] == 'Cancelled' ? 'danger' : 
                                         ($build['status'] == 'Processing' ? 'info' : 'warning')); 
                                ?> me-2">
                                    <?php echo $build['status']; ?>
                                </span>
                                <span class="badge status-badge bg-<?php 
                                    echo $build['payment_status'] == 'Paid' ? 'success' : 
                                         ($build['payment_status'] == 'Failed' ? 'danger' : 
                                         ($build['payment_status'] == 'Pending' ? 'warning' : 'secondary')); 
                                ?>">
                                    <?php echo $build['payment_status']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="build-body">
                        <h6 class="mb-3"><i class="fas fa-list me-2"></i>Components:</h6>
                        
                        <?php if (empty($build['components'])): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>No components found for this build.
                            </div>
                        <?php else: ?>
                            <?php foreach ($build['components'] as $category => $component): ?>
                                <div class="component-item">
                                    <span class="component-category"><?php echo htmlspecialchars($category); ?></span>
                                    <img src="Uploads/<?php echo htmlspecialchars($component['image'] ?? ''); ?>" 
                                         class="component-img" 
                                         alt="<?php echo htmlspecialchars($component['name']); ?>"
                                         onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'">
                                    <div class="component-details">
                                        <div class="component-name"><?php echo htmlspecialchars($component['name']); ?></div>
                                        <div class="component-specs"><?php echo htmlspecialchars($component['specs']); ?></div>
                                    </div>
                                    <div class="component-price">₹<?php echo number_format($component['price'] ?? 0, 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div>
                                <a href="build_details.php?id=<?php echo $build_id; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                <?php if ($build['payment_status'] == 'Pending'): ?>
                                    <a href="checkout.php?build_id=<?php echo $build_id; ?>" class="btn btn-success btn-sm ms-2">
                                        <i class="fas fa-credit-card me-1"></i>Complete Purchase
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <h5 class="total-price mb-0">Total: ₹<?php echo number_format($build['total_amount'], 2); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> BYD PC Builder. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>