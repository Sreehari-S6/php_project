<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href='../Adminstyle/admin_style.css'>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Admin Panel</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_view_users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Users
                    </a>
                </li>
                   <li class="nav-item">
                    <a href="admin_view_feedback.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        View Feedbacks
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item menu-item">
                    <div class="menu-toggle nav-link">
                        <span>
                            <i class="fas fa-box-open"></i>
                            Manage Products
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul class="submenu">
                        <li class="nav-item">
                            <a href="ManageCategory.php" class="nav-link">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a href="ManageBrand.php" class="nav-link">Brands</a>
                        </li>
                        <li class="nav-item">
                            <a href="admin_add_product.php" class="nav-link">Products</a>
                        </li>
                        <li class="nav-item">
                            <a href="admin_stock_updation.php" class="nav-link">Stock Updates</a>
                        </li>
                        
                    </ul>
                </li>
                <li class="nav-item menu-item">
                    <div class="menu-toggle nav-link">
                        <span>
                            <i class="fas fa-shopping-cart"></i>
                            Manage Orders
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul class="submenu">
                        <li class="nav-item">
                            <a href="admin_view_build_orders.php" class="nav-link">Build Orders</a>
                        </li>
                        <li class="nav-item">
                            <a href="admin_view_parts_order.php" class="nav-link">Normal Orders</a>
                        </li>
                    <li class="nav-item">
                            <a href="admin_view_return_orders.php" class="nav-link">Return Orders</a>
                        </li></ul>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard Overview</h1>
                <div class="user-info">
                    <img src="Mainstyle/img/admin.png" alt="Admin">
                    <span>  Admin   </span>
                    <button class="logout-btn">Logout</button>
                </div>
            </div>
            
            <!-- Stats Cards -->
          
             
    <script>
        // Toggle submenus
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const submenu = this.nextElementSibling;
                const icon = this.querySelector('.fa-chevron-down');
                
                submenu.classList.toggle('show');
                icon.classList.toggle('rotate');
            });
        });
        
        // Logout functionality

    </script>