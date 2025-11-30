<?php
include('Headers/admin_nav.php');
include('connection.php');

// Handle stock update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    
    // Check which operation was requested
    if (isset($_POST['update_stock'])) {
        $stock_change = $_POST['stock_change'];
        $operation = $_POST['update_stock']; // This will be either 'add' or 'remove'
        
        // Validate inputs
        if (is_numeric($stock_change) && $stock_change > 0) {
            if ($operation === 'add') {
                $query = "UPDATE tbl_products SET stock = stock + ? WHERE product_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $stock_change, $product_id);
                
                if ($stmt->execute()) {
                    $success_message = "Stock increased successfully!";
                } else {
                    $error_message = "Error updating stock: " . $conn->error;
                }
            } else {
                // Prevent negative stock for remove operation
                $check_stock = "SELECT stock FROM tbl_products WHERE product_id = ?";
                $stmt = $conn->prepare($check_stock);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $current_stock = $stmt->get_result()->fetch_assoc()['stock'];
                
                if ($current_stock < $stock_change) {
                    $error_message = "Cannot reduce stock below zero. Current stock: " . $current_stock;
                } else {
                    $query = "UPDATE tbl_products SET stock = stock - ? WHERE product_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ii", $stock_change, $product_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Stock reduced successfully!";
                    } else {
                        $error_message = "Error updating stock: " . $conn->error;
                    }
                }
            }
        } else {
            $error_message = "Please enter a valid positive number for stock change.";
        }
    } 
    // Handle direct stock update
    elseif (isset($_POST['set_stock'])) {
        $new_stock = $_POST['new_stock'];
        
        if (is_numeric($new_stock) && $new_stock >= 0) {
            $query = "UPDATE tbl_products SET stock = ? WHERE product_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $new_stock, $product_id);
            
            if ($stmt->execute()) {
                $success_message = "Stock set to " . $new_stock . " successfully!";
            } else {
                $error_message = "Error updating stock: " . $conn->error;
            }
        } else {
            $error_message = "Please enter a valid non-negative number for stock.";
        }
    }
}

// Fetch product data from the database
$query = "SELECT p.product_id, ps.model, p.price, p.stock, p.image 
          FROM tbl_products p 
          LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id 
          ORDER BY p.product_id DESC";
$result = mysqli_query($conn, $query);
$total_products = mysqli_num_rows($result);

// Get low stock products (less than 10 items)
$low_stock_query = "SELECT p.product_id, ps.model, p.stock FROM tbl_products p  
                    LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id 
                    WHERE stock < 10 ORDER BY stock ASC";
$low_stock_result = mysqli_query($conn, $low_stock_query);
$low_stock_count = mysqli_num_rows($low_stock_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Stock Management - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #2d2d55;
            margin-bottom: 10px;
        }
        
        .stat-card .change {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .stat-card .change.positive {
            background-color: #e6f4ea;
            color: #0f5132;
        }
        
        .stat-card .change.negative {
            background-color: #f8d7da;
            color: #842029;
        }
        
        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .section-header h2 {
            color: #2d2d55;
            font-size: 20px;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2d2d55 0%, #3d3d7a 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #3d3d7a 0%, #2d2d55 100%);
            transform: translateY(-2px);
        }
        
        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f8f9fa;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            font-weight: 600;
            color: #495057;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Action Buttons */
        .action-btn {
            background: #e9ecef;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 14px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .action-btn:hover {
            background: #dee2e6;
        }
        
        .btn-edit {
            background: #e7f1ff;
            color: #0d6efd;
        }
        
        .btn-edit:hover {
            background: #d0e2ff;
        }
        
        .btn-view {
            background: #e8f5e8;
            color: #198754;
        }
        
        .btn-view:hover {
            background: #d1e7dd;
        }
        
        /* Stock Status */
        .stock-high {
            color: #198754;
            font-weight: 600;
        }
        
        .stock-medium {
            color: #fd7e14;
            font-weight: 600;
        }
        
        .stock-low {
            color: #dc3545;
            font-weight: 600;
        }
        
        /* Stock Update Form */
        .stock-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }
        
        .stock-input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 80px;
        }
        
        .stock-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-add {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .btn-remove {
            background: #f8d7da;
            color: #842029;
        }
        
        .btn-set {
            background: #cfe2ff;
            color: #084298;
        }
        
        /* Messages */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        
        /* Product Image */
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            width: 400px;
            max-width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d2d55;
        }
        
        .close {
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }
        
        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .form-input {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            gap: 15px;
            padding: 15px 25px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .search-box {
            flex: 1;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ced4da;
            border-radius: 6px;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: white;
            min-width: 150px;
        }
        
        /* Badge */
        .badge {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
<div class="container">

<!-- Messages -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="stats-container">
    <div class="stat-card">
        <h3>Total Products</h3>
        <div class="value"><?php echo $total_products; ?></div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> Manage all products
        </div>
    </div>
    
    <div class="stat-card">
        <h3>Low Stock Items</h3>
        <div class="value"><?php echo $low_stock_count; ?></div>
        <div class="change <?php echo $low_stock_count > 0 ? 'negative' : 'positive'; ?>">
            <i class="fas <?php echo $low_stock_count > 0 ? 'fa-arrow-down' : 'fa-check'; ?>"></i>
            <?php echo $low_stock_count > 0 ? 'Needs attention' : 'All good'; ?>
        </div>
    </div>
    
    <div class="stat-card">
        <h3>Stock Management</h3>
        <div class="value">Update</div>
        <div class="change positive">
            <i class="fas fa-edit"></i> Modify product stock
        </div>
    </div>
</div>

<!-- Low Stock Alert Section -->
<?php if ($low_stock_count > 0): ?>
<div class="content-section">
    <div class="section-header">
        <h2>Low Stock Alert</h2>
        <span class="badge">
            <?php echo $low_stock_count; ?> items
        </span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Model</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($row = mysqli_fetch_assoc($low_stock_result)) {
                    echo '<tr>';
                    echo '<td>#' . $row['product_id'] . '</td>';
                    echo '<td>' . htmlspecialchars($row['model']) . '</td>';
                    echo '<td><span class="stock-low">' . $row['stock'] . '</span></td>';
                    echo '<td><span class="stock-low">Low Stock</span></td>';
                    echo '<td>';
                    echo '<button class="action-btn btn-edit" onclick="openStockModal(' . $row['product_id'] . ')">';
                    echo '<i class="fas fa-edit"></i> Update Stock';
                    echo '</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Product Stock Management Section -->
<div class="content-section">
    <div class="section-header">
        <h2>Product Stock Management</h2>
        <button class="btn-primary" onclick="window.location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
    
    <!-- Search and Filter -->
    <div class="search-filter">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search by product ID or model..." onkeyup="filterProducts()" id="searchInput">
        </div>
        <select class="filter-select" onchange="filterProducts()" id="stockFilter">
            <option value="all">All Stock Status</option>
            <option value="low">Low Stock</option>
            <option value="medium">Limited Stock</option>
            <option value="high">In Stock</option>
            <option value="out">Out of Stock</option>
        </select>
    </div>
    
    <div class="table-responsive">
        <table id="productsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Model</th>
                    <th>Price</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset result pointer
                mysqli_data_seek($result, 0);
                
                while($row = mysqli_fetch_assoc($result)) {
                    // Determine stock status
                    $stock_class = 'stock-high';
                    $stock_status = 'In Stock';
                    $stock_value = 'high';
                    
                    if ($row['stock'] == 0) {
                        $stock_class = 'stock-low';
                        $stock_status = 'Out of Stock';
                        $stock_value = 'out';
                    } elseif ($row['stock'] < 10) {
                        $stock_class = 'stock-low';
                        $stock_status = 'Low Stock';
                        $stock_value = 'low';
                    } elseif ($row['stock'] < 20) {
                        $stock_class = 'stock-medium';
                        $stock_status = 'Limited';
                        $stock_value = 'medium';
                    }
                    
                    echo '<tr data-stock="' . $stock_value . '" data-id="' . $row['product_id'] . '" data-name="' . htmlspecialchars($row['model']) . '">';
                    echo '<td>#' . $row['product_id'] . '</td>';
                    echo '<td><img src="Uploads/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['model']) . '" class="product-img"></td>';
                    echo '<td>' . htmlspecialchars($row['model']) . '</td>';
                    echo '<td>₹' . number_format($row['price'], 2) . '</td>';
                    echo '<td><span class="' . $stock_class . '">' . $row['stock'] . '</span></td>';
                    echo '<td><span class="' . $stock_class . '">' . $stock_status . '</span></td>';
                    echo '<td>';
                    echo '<button class="action-btn btn-edit" onclick="openStockModal(' . $row['product_id'] . ')">';
                    echo '<i class="fas fa-edit"></i> Update Stock';
                    echo '</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<!-- Stock Update Modal -->
<div id="stockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Stock</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" class="modal-form">
            <input type="hidden" id="modalProductId" name="product_id">
            
            <div class="form-group">
                <label class="form-label">Product ID</label>
                <input type="text" id="modalProductIdDisplay" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Product Model</label>
                <input type="text" id="modalProductName" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Current Stock</label>
                <input type="text" id="modalCurrentStock" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Add/Remove Stock</label>
                <input type="number" name="stock_change" class="form-input" min="1" required placeholder="Enter quantity">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="update_stock" value="add" class="stock-btn btn-add">
                    <i class="fas fa-plus"></i> Add Stock
                </button>
                <button type="submit" name="update_stock" value="remove" class="stock-btn btn-remove">
                    <i class="fas fa-minus"></i> Remove Stock
                </button>
            </div>
            
            <hr>
            
            <div class="form-group">
                <label class="form-label">Or Set Stock Directly</label>
                <input type="number" name="new_stock" class="form-input" min="0" placeholder="Enter new stock quantity">
            </div>
            
            <div class="modal-footer">
                <button type="button" class="action-btn" onclick="closeModal()">Cancel</button>
                <button type="submit" name="set_stock" class="stock-btn btn-set">
                    <i class="fas fa-save"></i> Set Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to open stock modal with product details
    function openStockModal(productId) {
        // Find the table row with this product ID
        const row = document.querySelector(`tr[data-id="${productId}"]`);
        if (!row) return;
        
        // Get product details from data attributes
        const productName = row.getAttribute('data-name');
        const stockElement = row.querySelector('.stock-high, .stock-medium, .stock-low');
        const currentStock = stockElement ? stockElement.textContent : '0';
        
        // Set values in the modal
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalProductIdDisplay').value = '#' + productId;
        document.getElementById('modalProductName').value = productName;
        document.getElementById('modalCurrentStock').value = currentStock;
        
        // Show the modal
        document.getElementById('stockModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('stockModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('stockModal');
        if (event.target === modal) {
            closeModal();
        }
    }
    
    // Function to filter products based on search and filter criteria
    function filterProducts() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const stockFilter = document.getElementById('stockFilter').value;
        const rows = document.querySelectorAll('#productsTable tbody tr');
        
        rows.forEach(row => {
            const productId = row.getAttribute('data-id');
            const productName = row.getAttribute('data-name').toLowerCase();
            const stockStatus = row.getAttribute('data-stock');
            
            const matchesSearch = productId.includes(searchText) || productName.includes(searchText);
            const matchesFilter = stockFilter === 'all' || stockStatus === stockFilter;
            
            if (matchesSearch && matchesFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

</body>
</html>