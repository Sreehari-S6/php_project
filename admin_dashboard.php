<?php
include('Headers/admin_nav.php');
include('connection.php'); // Database connection file

// Fetch all users from the database
$user_query = "SELECT * FROM tbl_user ";
$user_result = mysqli_query($conn, $user_query);
$total_users = mysqli_num_rows($user_result);

// Fetch recent 3 users for the recent users section
$recent_query = "SELECT * FROM tbl_user ORDER BY user_id DESC LIMIT 3";
$recent_result = mysqli_query($conn, $recent_query);

// Calculate total revenue from tbl_ordermaster
$revenue_query = "SELECT SUM(total_amount) as total_revenue FROM tbl_ordermaster";
$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_data = mysqli_fetch_assoc($revenue_result);
$total_revenue = $revenue_data['total_revenue'] ?? 0;

// Fetch total products count
$products_query = "SELECT COUNT(*) as total_products FROM tbl_products";
$products_result = mysqli_query($conn, $products_query);
$products_data = mysqli_fetch_assoc($products_result);
$total_products = $products_data['total_products'] ?? 0;

// Fetch pending orders count
$pending_orders_query = "SELECT COUNT(*) as pending_orders FROM tbl_ordermaster WHERE status = 'pending'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
$pending_orders_data = mysqli_fetch_assoc($pending_orders_result);
$pending_orders = $pending_orders_data['pending_orders'] ?? 0;

// Fetch today's revenue
$today_revenue_query = "SELECT SUM(total_amount) as today_revenue FROM tbl_ordermaster WHERE DATE(date) = CURDATE()";
$today_revenue_result = mysqli_query($conn, $today_revenue_query);
$today_revenue_data = mysqli_fetch_assoc($today_revenue_result);
$today_revenue = $today_revenue_data['today_revenue'] ?? 0;

// Fetch total orders count
$total_orders_query = "SELECT COUNT(*) as total_orders FROM tbl_ordermaster";
$total_orders_result = mysqli_query($conn, $total_orders_query);
$total_orders_data = mysqli_fetch_assoc($total_orders_result);
$total_orders = $total_orders_data['total_orders'] ?? 0;

// Fetch completed orders count
$completed_orders_query = "SELECT COUNT(*) as completed_orders FROM tbl_ordermaster WHERE status = 'completed'";
$completed_orders_result = mysqli_query($conn, $completed_orders_query);
$completed_orders_data = mysqli_fetch_assoc($completed_orders_result);
$completed_orders = $completed_orders_data['completed_orders'] ?? 0;

// Calculate average order value
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Fetch data for charts
// Revenue data for the last 7 days for line chart
$revenue_trend_query = "
    SELECT DATE(date) as order_date, SUM(total_amount) as daily_revenue 
    FROM tbl_ordermaster 
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date)
    ORDER BY order_date ASC
";
$revenue_trend_result = mysqli_query($conn, $revenue_trend_query);
$revenue_labels = [];
$revenue_data = [];
while($row = mysqli_fetch_assoc($revenue_trend_result)) {
    $revenue_labels[] = date('M j', strtotime($row['order_date']));
    $revenue_data[] = $row['daily_revenue'];
}

// Order status distribution for doughnut chart
$order_status_query = "
    SELECT status, COUNT(*) as count 
    FROM tbl_ordermaster 
    GROUP BY status
";
$order_status_result = mysqli_query($conn, $order_status_query);
$status_labels = [];
$status_data = [];
$status_colors = [
    'pending' => '#FF6384',
    'completed' => '#36A2EB',
    'processing' => '#FFCE56',
    'shipped' => '#4BC0C0',
    'cancelled' => '#9966FF'
];
while($row = mysqli_fetch_assoc($order_status_result)) {
    $status_labels[] = ucfirst($row['status']);
    $status_data[] = $row['count'];
}
?>

<!-- Stats Cards -->
<div class="stats-container">
    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="value"><?php echo $total_users; ?></div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> 12% from last month
        </div>
    </div>
    <div class="stat-card">
        <h3>Total Products</h3>
        <div class="value"><?php echo $total_products; ?></div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> 5% from last month
        </div>
    </div>
    <div class="stat-card">
        <h3>Pending Orders</h3>
        <div class="value"><?php echo $pending_orders; ?></div>
        <div class="change negative">
            <i class="fas fa-arrow-down"></i> 8% from last month
        </div>
    </div>
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <div class="value">₹<?php echo number_format($total_revenue, 2); ?></div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> 22% from last month
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <div class="chart-container">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Revenue Trend (Last 7 Days)</h3>
            </div>
            <div class="chart-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="chart-container">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Order Status Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Panel -->
<div class="quick-stats-panel">
    <div class="section-header">
        <h2>Quick Statistics</h2>
    </div>
    <div class="quick-stats-grid">
        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: #e3f2fd;">
                <i class="fas fa-money-bill-wave" style="color: #1976d2;"></i>
            </div>
            <div class="quick-stat-content">
                <span class="quick-stat-label">Today's Revenue</span>
                <span class="quick-stat-value">₹<?php echo number_format($today_revenue, 2); ?></span>
            </div>
        </div>
        
        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: #e8f5e8;">
                <i class="fas fa-shopping-cart" style="color: #388e3c;"></i>
            </div>
            <div class="quick-stat-content">
                <span class="quick-stat-label">Total Orders</span>
                <span class="quick-stat-value"><?php echo $total_orders; ?></span>
            </div>
        </div>
        
        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: #fff3e0;">
                <i class="fas fa-check-circle" style="color: #f57c00;"></i>
            </div>
            <div class="quick-stat-content">
                <span class="quick-stat-label">Completed Orders</span>
                <span class="quick-stat-value"><?php echo $completed_orders; ?></span>
            </div>
        </div>
        
        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: #fce4ec;">
                <i class="fas fa-chart-line" style="color: #c2185b;"></i>
            </div>
            <div class="quick-stat-content">
                <span class="quick-stat-label">Avg. Order Value</span>
                <span class="quick-stat-value">₹<?php echo number_format($avg_order_value, 2); ?></span>
            </div>
        </div>
        
        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: #f3e5f5;">
                <i class="fas fa-percentage" style="color: #7b1fa2;"></i>
            </div>
            <div class="quick-stat-content">
                <span class="quick-stat-label">Completion Rate</span>
                <span class="quick-stat-value"><?php echo $total_orders > 0 ? round(($completed_orders / $total_orders) * 100, 2) : '0'; ?>%</span>
            </div>
        </div>
        
        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: #e0f2f1;">
                <i class="fas fa-user-check" style="color: #00796b;"></i>
            </div>
            <div class="quick-stat-content">
                <span class="quick-stat-label">Active Users</span>
                <span class="quick-stat-value"><?php echo $total_users; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- All Users Section -->
<div class="content-section">
    <div class="section-header">
        <h2>All Users</h2>
        <button class="btn-primary">Refresh</button>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(mysqli_num_rows($user_result) > 0) {
                    while($user = mysqli_fetch_assoc($user_result)) {
                        echo '<tr>';
                        echo '<td>#' . htmlspecialchars($user['user_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['phone_no']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['created_at'] ?? 'N/A') . '</td>';
                        echo '<td><span style="color: #2d2d55ff;">Active</span></td>';
                        echo '<td>';
                        echo '<button class="action-btn">Edit</button>';
                        echo '<button class="action-btn">View</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">No users found</td></tr>';
                }
                ?>
            </tbody>
        </table>
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
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(mysqli_num_rows($recent_result) > 0) {
                    while($recent_user = mysqli_fetch_assoc($recent_result)) {
                        echo '<tr>';
                        echo '<td>#' . htmlspecialchars($recent_user['user_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($recent_user['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($recent_user['email']) . '</td>';
                        echo '<td>' . htmlspecialchars($recent_user['phone_no']) . '</td>';
                        echo '<td>' . htmlspecialchars($recent_user['created_at'] ?? 'N/A') . '</td>';
                        echo '<td><span style="color: #64ffda;">Active</span></td>';
                        echo '<td>';
                        echo '<button class="action-btn">Edit</button>';
                        echo '<button class="action-btn">View</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">No recent users</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Initialize charts when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trend Line Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($revenue_labels); ?>,
            datasets: [{
                label: 'Daily Revenue (₹)',
                data: <?php echo json_encode($revenue_data); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Revenue Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value;
                        }
                    }
                }
            }
        }
    });

    // Order Status Doughnut Chart
    const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const orderStatusChart = new Chart(orderStatusCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($status_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($status_data); ?>,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Order Status Distribution'
                }
            }
        }
    });
});
</script>

<style>
.quick-stats-panel {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quick-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.quick-stat-card {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #2196F3;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.quick-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.quick-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.2rem;
}

.quick-stat-content {
    display: flex;
    flex-direction: column;
}

.quick-stat-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
    margin-bottom: 5px;
}

.quick-stat-value {
    font-size: 1.3rem;
    font-weight: bold;
    color: #333;
}

/* Charts Section Styles */
.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.chart-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chart-card {
    padding: 20px;
}

.chart-header {
    margin-bottom: 15px;
}

.chart-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.2rem;
}

.chart-body {
    height: 300px;
    position: relative;
}

@media (max-width: 768px) {
    .quick-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-stat-card {
        padding: 12px;
    }
    
    .quick-stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
        margin-right: 12px;
    }
    
    .quick-stat-value {
        font-size: 1.1rem;
    }
    
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .chart-body {
        height: 250px;
    }
}
</style>

</div>
</div>

</body>
</html>