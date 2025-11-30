<?php
include('connection.php');
include('Headers/admin_nav.php');

// Handle feedback deletion
if (isset($_POST['delete_feedback'])) {
    $feedback_id = $_POST['feedback_id'];
    
    try {
        $delete_feedback_sql = "DELETE FROM tbl_feedbacks WHERE id = ?";
        $delete_feedback_stmt = $conn->prepare($delete_feedback_sql);
        $delete_feedback_stmt->bind_param("i", $feedback_id);
        $delete_feedback_stmt->execute();
        $delete_feedback_stmt->close();
        
        $_SESSION['success_message'] = "Feedback deleted successfully!";
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error deleting feedback: " . $e->getMessage();
    }
}

// Fetch feedbacks from the database
$sql = "
    SELECT 
        id,
        first_name,
        last_name,
        email,
        phone_number,
        message,
        created_at
    FROM tbl_feedbacks
    ORDER BY created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Feedbacks</title>
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
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .no-feedbacks {
            color: #ffffffff;
            text-align: center;
            padding: 20px;
        }
        .feedback-details {
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
        .message-content {
            margin-top: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        .contact-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Feedbacks Management</h1>
        
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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date Submitted</th>
                        <th>What They Said</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                            <td>
                                <button class="toggle-details" onclick="toggleDetails(<?php echo htmlspecialchars($row['id']); ?>)">View Details</button>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button type="submit" name="delete_feedback" class="delete-btn" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="details-<?php echo htmlspecialchars($row['id']); ?>" class="hidden">
                            <td colspan="6">
                                <div class="feedback-details">
                                    <h3>Feedback Details - ID: <?php echo htmlspecialchars($row['id']); ?></h3>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                                    
                                    <p><strong>Contact Information:</strong></p>
                                    <div class="contact-info">
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone_number']); ?></p>
                                    </div>
                                    
                                    <p><strong>Message:</strong></p>
                                    <div class="message-content">
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </div>
                                    
                                    <p><strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-feedbacks">No user feedbacks found.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleDetails(feedbackId) {
            const detailsRow = document.getElementById('details-' + feedbackId);
            detailsRow.classList.toggle('hidden');
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>