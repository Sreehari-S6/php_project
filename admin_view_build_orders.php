<?php
include('connection.php');
include('Headers/admin_nav.php');

// Handle status update
if (isset($_POST['update_status'])) {
    $ordermaster_id = $_POST['ordermaster_id'];
    $new_status = $_POST['status'];
    
    // Start transaction to ensure both updates succeed or fail together
    $conn->begin_transaction();
    
    try {
        // Update status in tbl_build_ordermaster
        $update_ordermaster_sql = "UPDATE tbl_build_ordermaster SET status = ? WHERE ordermaster_id = ?";
        $update_ordermaster_stmt = $conn->prepare($update_ordermaster_sql);
        $update_ordermaster_stmt->bind_param("si", $new_status, $ordermaster_id);
        $update_ordermaster_stmt->execute();
        $update_ordermaster_stmt->close();
        
        // Also update status in tbl_build table
        // First, get the build_id associated with this order
        $get_build_sql = "SELECT build_id FROM tbl_build_ordermaster WHERE ordermaster_id = ?";
        $get_build_stmt = $conn->prepare($get_build_sql);
        $get_build_stmt->bind_param("i", $ordermaster_id);
        $get_build_stmt->execute();
        $get_build_result = $get_build_stmt->get_result();
        
        if ($get_build_result->num_rows > 0) {
            $build_data = $get_build_result->fetch_assoc();
            $build_id = $build_data['build_id'];
            
            // Update status in tbl_build
            $update_build_sql = "UPDATE tbl_build SET status = ? WHERE build_id = ?";
            $update_build_stmt = $conn->prepare($update_build_sql);
            $update_build_stmt->bind_param("si", $new_status, $build_id);
            $update_build_stmt->execute();
            $update_build_stmt->close();
        }
        
        $get_build_stmt->close();
        
        // Commit transaction if both updates were successful
        $conn->commit();
        $_SESSION['success_message'] = "Order status updated successfully in both tables!";
        
    } catch (Exception $e) {
        // Rollback transaction if any error occurred
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating order status: " . $e->getMessage();
    }
}

// Fetch build orders with related information including product names and delivery address
$sql = "
    SELECT 
        bo.ordermaster_id,
        bo.user_id,
        bo.status,
        bo.date as order_date,
        bo.total_amount,
        bo.payment_method,
        bo.delivery_address_id,
        bo.build_id,
        b.build_id,
        b.ram_id,
        b.case_id,
        b.storage_id,
        b.build_date,
        b.motherboard_id,
        b.processor_id,
        b.status as build_status,
        b.payment_status,
        b.total_amount as build_total_amount,
        u.name,
        -- Product names
        mb.model as motherboard_name,
        proc.model as processor_name,
        ram.model as ram_name,
        stor.model as storage_name,
        pc_case.model as case_name,
        -- Delivery address details
        dd.name as delivery_name,
        dd.address as delivery_address,
        dd.city as delivery_city,
        dd.zip_code as delivery_zip_code,
        dd.phone_number as delivery_phone
    FROM tbl_build_ordermaster bo
    LEFT JOIN tbl_build b ON bo.build_id = b.build_id
    LEFT JOIN tbl_user u ON bo.user_id = u.user_id
    -- Join with product specifications for each component
    LEFT JOIN tbl_productspecifications mb ON b.motherboard_id = mb.product_id
    LEFT JOIN tbl_productspecifications proc ON b.processor_id = proc.product_id
    LEFT JOIN tbl_productspecifications ram ON b.ram_id = ram.product_id
    LEFT JOIN tbl_productspecifications stor ON b.storage_id = stor.product_id
    LEFT JOIN tbl_productspecifications pc_case ON b.case_id = pc_case.product_id
    -- Join with delivery details
    LEFT JOIN tbl_delivery_details dd ON bo.delivery_address_id = dd.delivery_id
    ORDER BY bo.date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Build Orders</title>
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
        .status-to-be-build {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-build-started {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-build-completed {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-complete {
            background-color: #28a745;
            color: #ffffff;
        }
        .status-technical-issues {
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Build Orders Management</h1>
        
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
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ordermaster_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']) . ' (ID: ' . htmlspecialchars($row['user_id']) . ')'; ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($row['order_date'])); ?></td>
                            <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
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
                                        <option value="To Be Build" <?php echo $row['status'] == 'To Be Build' ? 'selected' : ''; ?>>To Be Build</option>    
                                        <option value="Confirmed" <?php echo $row['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="Build Started" <?php echo $row['status'] == 'Build Started' ? 'selected' : ''; ?>>Build Started</option>
                                        
                                        <option value="Complete" <?php echo $row['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Technical Issues" <?php echo $row['status'] == 'Technical Issues' ? 'selected' : ''; ?>>Technical Issues</option>
                                    </select>
                                    <button type="submit" name="update_status" class="update-btn">Update</button>
                                </form>
                                <button class="toggle-details" onclick="toggleDetails(<?php echo htmlspecialchars($row['ordermaster_id']); ?>)">View Details</button>
                            </td>
                        </tr>
                        <tr id="details-<?php echo htmlspecialchars($row['ordermaster_id']); ?>" class="hidden">
                            <td colspan="7">
                                <div class="order-details">
                                    <h3>Order Details - Build ID: <?php echo htmlspecialchars($row['build_id']); ?></h3>
                                    <p><strong>Build Status:</strong> <?php echo htmlspecialchars($row['build_status']); ?></p>
                                    <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($row['payment_status']); ?></p>
                                    <p><strong>Build Date:</strong> <?php echo date('M j, Y g:i A', strtotime($row['build_date'])); ?></p>
                                    <p><strong>Components:</strong></p>
                                    <ul>
                                        <li>Motherboard: <?php echo htmlspecialchars($row['motherboard_name'] ?: 'N/A'); ?> (ID: <?php echo htmlspecialchars($row['motherboard_id']); ?>)</li>
                                        <li>Processor: <?php echo htmlspecialchars($row['processor_name'] ?: 'N/A'); ?> (ID: <?php echo htmlspecialchars($row['processor_id']); ?>)</li>
                                        <li>RAM: <?php echo htmlspecialchars($row['ram_name'] ?: 'N/A'); ?> (ID: <?php echo htmlspecialchars($row['ram_id']); ?>)</li>
                                        <li>Storage: <?php echo htmlspecialchars($row['storage_name'] ?: 'N/A'); ?> (ID: <?php echo htmlspecialchars($row['storage_id']); ?>)</li>
                                        <li>Case: <?php echo htmlspecialchars($row['case_name'] ?: 'N/A'); ?> (ID: <?php echo htmlspecialchars($row['case_id']); ?>)</li>
                                    </ul>
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
            <p class="no-orders">No build orders found.</p>
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