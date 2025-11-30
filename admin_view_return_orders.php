<?php
include('Headers/admin_nav.php');
include('connection.php');

// Handle return request actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_return_status'])) {
        $return_id = $_POST['return_id'];
        $new_status = $_POST['status'];
        
        if ($new_status == 'approved') {
            // Get return details
            $return_query = "SELECT rr.ordermaster_id, rr.return_reason, od.product_id, od.quantity 
                           FROM tbl_return_requests rr 
                           JOIN tbl_orderdetails od ON rr.ordermaster_id = od.ordermaster_id 
                           WHERE rr.return_id = ?";
            $stmt = $conn->prepare($return_query);
            $stmt->bind_param("i", $return_id);
            $stmt->execute();
            $return_result = $stmt->get_result();
            $return_details = $return_result->fetch_assoc();
            
            if ($return_details) {
                // Increase stock quantity
                $update_stock = "UPDATE tbl_products SET stock = stock + ? WHERE product_id = ?";
                $stock_stmt = $conn->prepare($update_stock);
                $stock_stmt->bind_param("ii", $return_details['quantity'], $return_details['product_id']);
                $stock_stmt->execute();
                
                // Update return status
                $update_status = "UPDATE tbl_return_requests SET status = 'approved', updated_at = NOW() WHERE return_id = ?";
                $status_stmt = $conn->prepare($update_status);
                $status_stmt->bind_param("i", $return_id);
                $status_stmt->execute();
                
                $success_message = "Return request approved and stock updated successfully!";
            }
        } else {
            // Just update status for other actions
            $update_status = "UPDATE tbl_return_requests SET status = ?, updated_at = NOW() WHERE return_id = ?";
            $status_stmt = $conn->prepare($update_status);
            $status_stmt->bind_param("si", $new_status, $return_id);
            $status_stmt->execute();
            
            $success_message = "Return request status updated successfully!";
        }
    }
}

// Fetch all return requests with filters
$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';

$returns_query = "
    SELECT 
        rr.return_id,
        rr.ordermaster_id,
        rr.return_reason,
        rr.additional_notes,
        rr.status,
        rr.created_at,
        rr.updated_at,
        u.name as user_name,
        u.email,
        u.phone_no,
        p.model as product_name,
        od.quantity,
        od.price,
        pr.image
    FROM tbl_return_requests rr
    JOIN tbl_user u ON rr.user_id = u.user_id
    JOIN tbl_ordermaster om ON rr.ordermaster_id = om.ordermaster_id
    JOIN tbl_orderdetails od ON om.ordermaster_id = od.ordermaster_id
    JOIN tbl_products pr ON od.product_id = pr.product_id
    JOIN tbl_productspecifications p ON pr.product_id = p.product_id
    WHERE 1=1
";

if ($status_filter != 'all') {
    $returns_query .= " AND rr.status = '$status_filter'";
}

if (!empty($search_term)) {
    $returns_query .= " AND (u.name LIKE '%$search_term%' OR u.email LIKE '%$search_term%' OR p.model LIKE '%$search_term%')";
}

$returns_query .= " ORDER BY rr.created_at DESC";

$returns_result = mysqli_query($conn, $returns_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
        }
        .section-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 20px;
        }
        .filters {
            display: flex;
            gap: 15px;
            padding: 20px 25px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .filters select, .filters input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .filters button {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1edff; color: #2d2d55; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d4edda; color: #155724; }
        .return-actions {
            display: flex;
            gap: 5px;
        }
        .return-actions form {
            margin: 0;
        }
        .return-actions button {
            padding: 4px 8px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-complete { background: #17a2b8; color: white; }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="padding: 20px;">

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-undo me-2"></i>Return Requests Management</h2>
            </div>

            <!-- Filters -->
            <div class="filters">
                <form method="GET" class="d-flex gap-3 align-items-center">
                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    
                    <input type="text" name="search" placeholder="Search by customer or product..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" 
                           style="min-width: 250px;">
                    
                    <button type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                    
                    <a href="admin_returns_management.php" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Order ID</th>
                            <th>Reason</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($returns_result) > 0): ?>
                            <?php while($return = mysqli_fetch_assoc($returns_result)): ?>
                                <tr>
                                    <td>#<?php echo $return['return_id']; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <img src="../Uploads/<?php echo htmlspecialchars($return['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($return['product_name']); ?>" 
                                                 class="product-image">
                                            <div><?php echo htmlspecialchars($return['product_name']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><strong><?php echo htmlspecialchars($return['user_name']); ?></strong></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($return['email']); ?></small>
                                        <br><small><?php echo htmlspecialchars($return['phone_no']); ?></small>
                                    </td>
                                    <td>#<?php echo $return['ordermaster_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($return['return_reason']); ?></strong>
                                        <?php if (!empty($return['additional_notes'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($return['additional_notes']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $return['quantity']; ?></td>
                                    <td>₹<?php echo number_format($return['price'] * $return['quantity'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $return['status']; ?>">
                                            <?php echo ucfirst($return['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($return['created_at'])); ?>
                                        <br><small class="text-muted"><?php echo date('g:i A', strtotime($return['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="return-actions">
                                            <?php if ($return['status'] == 'pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="return_id" value="<?php echo $return['return_id']; ?>">
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" name="update_return_status" class="btn-approve" 
                                                            onclick="return confirm('Approve this return? Stock quantity will be increased by <?php echo $return['quantity']; ?> units.')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="return_id" value="<?php echo $return['return_id']; ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" name="update_return_status" class="btn-reject"
                                                            onclick="return confirm('Reject this return request?')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            <?php elseif ($return['status'] == 'approved'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="return_id" value="<?php echo $return['return_id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" name="update_return_status" class="btn-complete"
                                                            onclick="return confirm('Mark this return as completed?')">
                                                        <i class="fas fa-check-double"></i> Complete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.5;"></i>
                                    <br>No return requests found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>