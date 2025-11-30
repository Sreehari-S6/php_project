<?php
include('connection.php');
include('Headers/admin_nav.php');

// Handle status update
if (isset($_POST['update_status'])) {
    $ordermaster_id = $_POST['ordermaster_id'];
    $new_status = $_POST['status'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update status in tbl_ordermaster
        $update_ordermaster_sql = "UPDATE tbl_ordermaster SET status = ? WHERE ordermaster_id = ?";
        $update_ordermaster_stmt = $conn->prepare($update_ordermaster_sql);
        $update_ordermaster_stmt->bind_param("si", $new_status, $ordermaster_id);
        $update_ordermaster_stmt->execute();
        $update_ordermaster_stmt->close();
        
        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = "Order status updated successfully!";
        
    } catch (Exception $e) {
        // Rollback transaction if any error occurred
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating order status: " . $e->getMessage();
    }
}

// Fetch parts purchasing orders with related information
$sql = "
    SELECT 
        om.ordermaster_id,
        om.user_id,
        om.status,
        om.date as order_date,
        om.total_amount,
        om.payment_method,
        om.delivery_address_id,
        u.name as user_name,
        u.email as user_email,
        -- Delivery address details
        dd.name as delivery_name,
        dd.address as delivery_address,
        dd.city as delivery_city,
        dd.zip_code as delivery_zip_code,
        dd.phone_number as delivery_phone,
        -- Order items details (aggregated)
        GROUP_CONCAT(CONCAT(od.quantity, 'x ', ps.model, ' (₹', od.price, ')') SEPARATOR '; ') as order_items,
        COUNT(od.product_id) as item_count
    FROM tbl_ordermaster om
    LEFT JOIN tbl_user u ON om.user_id = u.user_id
    LEFT JOIN tbl_delivery_details dd ON om.delivery_address_id = dd.delivery_id
    LEFT JOIN tbl_orderdetails od ON om.ordermaster_id = od.ordermaster_id
    LEFT JOIN tbl_productspecifications ps ON od.product_id = ps.product_id
    GROUP BY om.ordermaster_id, om.user_id, om.status, om.date, om.total_amount, 
             om.payment_method, om.delivery_address_id, u.name, u.email,
             dd.name, dd.address, dd.city, dd.zip_code, dd.phone_number
    ORDER BY om.date DESC
";

$result = $conn->query($sql);

// Fetch detailed order items for each order (for the detailed view)
$order_items_details = array();
if ($result->num_rows > 0) {
    $order_ids = array();
    while ($row = $result->fetch_assoc()) {
        $order_ids[] = $row['ordermaster_id'];
    }
    
    // Reset pointer to beginning
    $result->data_seek(0);
    
    // Get detailed items for each order
    if (!empty($order_ids)) {
        $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';
        $items_sql = "
            SELECT 
                od.ordermaster_id,
                od.product_id,
                od.quantity,
                od.price,
                ps.model as product_name,
                (od.quantity * od.price) as item_total
            FROM tbl_orderdetails od
            LEFT JOIN tbl_productspecifications ps ON od.product_id = ps.product_id
            WHERE od.ordermaster_id IN ($placeholders)
            ORDER BY od.ordermaster_id
        ";
        
        $items_stmt = $conn->prepare($items_sql);
        $types = str_repeat('i', count($order_ids));
        $items_stmt->bind_param($types, ...$order_ids);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        while ($item_row = $items_result->fetch_assoc()) {
            $order_items_details[$item_row['ordermaster_id']][] = $item_row;
        }
        $items_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Parts Purchasing Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #ffffffff;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #080746ff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #ffffffff;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ff8a8aff;
            color: #ffffffff;
        }
        th {
            background-color: #1384f4ff;
            font-weight: bold;
            color: #ffffffff;
        }
        tr:hover {
            background-color: #f91a1aff;
        }
        .status-form {
            display: flex;
            align-items: center;
        }
        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }
        .update-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .update-btn:hover {
            background-color: #0056b3;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-shipped {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-delivered {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-complete {
            background-color: #28a745;
            color: #ffffff;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            color: #333;
        }
        .toggle-details {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            margin-top: 5px;
            color: #ffffffff;
        }
        .hidden {
            display: none;
        }
        .no-orders {
            color: #ffffffff;
            text-align: center;
            padding: 20px;
        }
        .delivery-address {
            margin-top: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .order-items {
            margin-top: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th, .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            color: #333;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Parts Purchasing Orders Management</h1>
        
        <?php
        // Display success/error messages
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Items Count</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ordermaster_id']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['user_name']) . ' (ID: ' . htmlspecialchars($row['user_id']) . ')'; ?><br>
                                <small><?php echo htmlspecialchars($row['user_email']); ?></small>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($row['order_date'])); ?></td>
                            <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['item_count']); ?> items</td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="status-form">
                                    <input type="hidden" name="ordermaster_id" value="<?php echo htmlspecialchars($row['ordermaster_id']); ?>">
                                    <select name="status" class="status-select">
                                        <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>    
                                        <option value="Confirmed" <?php echo $row['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="Processing" <?php echo $row['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Shipped" <?php echo $row['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo $row['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="completed" <?php echo $row['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo $row['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="update-btn">Update</button>
                                </form>
                                <button class="toggle-details" onclick="toggleDetails(<?php echo htmlspecialchars($row['ordermaster_id']); ?>)">View Details</button>
                            </td>
                        </tr>
                        <tr id="details-<?php echo htmlspecialchars($row['ordermaster_id']); ?>" class="hidden">
                            <td colspan="8">
                                <div class="order-details">
                                    <h3>Order Details - Order ID: <?php echo htmlspecialchars($row['ordermaster_id']); ?></h3>
                                    <p><strong>User:</strong> <?php echo htmlspecialchars($row['user_name']); ?> (ID: <?php echo htmlspecialchars($row['user_id']); ?>)</p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['user_email']); ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('M j, Y g:i A', strtotime($row['order_date'])); ?></p>
                                    <p><strong>Total Amount:</strong> ₹<?php echo number_format($row['total_amount'], 2); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($row['payment_method']); ?></p>
                                    <p><strong>Items Count:</strong> <?php echo htmlspecialchars($row['item_count']); ?> items</p>
                                    
                                    <p><strong>Order Items:</strong></p>
                                    <div class="order-items">
                                        <?php if (isset($order_items_details[$row['ordermaster_id']])): ?>
                                            <table class="items-table">
                                                <thead>
                                                    <tr>
                                                        <th>Product Name</th>
                                                        <th>Product ID</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order_items_details[$row['ordermaster_id']] as $item): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($item['product_id']); ?></td>
                                                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                            <td>₹<?php echo number_format($item['item_total'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <p>No items found for this order.</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p><strong>Delivery Address:</strong></p>
                                    <div class="delivery-address">
                                        <?php if ($row['delivery_address_id'] && $row['delivery_name']): ?>
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($row['delivery_name']); ?></p>
                                            <p><strong>Address:</strong> <?php echo htmlspecialchars($row['delivery_address']); ?></p>
                                            <p><strong>City:</strong> <?php echo htmlspecialchars($row['delivery_city']); ?></p>
                                            <p><strong>ZIP Code:</strong> <?php echo htmlspecialchars($row['delivery_zip_code']); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['delivery_phone']); ?></p>
                                        <?php else: ?>
                                            <p>No delivery address found for ID: <?php echo htmlspecialchars($row['delivery_address_id']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-orders">No parts purchasing orders found.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleDetails(orderId) {
            const detailsRow = document.getElementById('details-' + orderId);
            detailsRow.classList.toggle('hidden');
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>