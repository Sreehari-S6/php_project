<?php
include('Headers/admin_nav.php');
include('connection.php'); // Assuming you have a database connection file

// Fetch user data from the database
$query = "SELECT name, email, phone_no FROM tbl_user";
$result = mysqli_query($conn, $query);
$total_users = mysqli_num_rows($result);

// Get recent users (limit to 3 for the recent users section)
$recent_query = "SELECT name, email, phone_no FROM tbl_user";
$recent_result = mysqli_query($conn, $recent_query);
?>

<!-- Stats Cards -->
<div class="stats-container">
    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="value"><?php echo $total_users; ?></div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> 9% from last month
        </div>
    </div>
</div>

<!-- Recent Users Section -->
<div class="content-section">
    <div class="section-header">
        <h2>Recent Users</h2>
        <button class="btn-primary">View All</button>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = $total_users; // Start counting from the total users for IDs
                while($row = mysqli_fetch_assoc($recent_result)) {
                    echo '<tr>';
                    echo '<td>#' . $counter . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['phone_no']) . '</td>';
                    
                    echo '<td><span style="color: #2d2d55ff;">Active</span></td>';
                    echo '<td>';
                
                    echo '</td>';
                    echo '</tr>';
                    $counter--;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>