<!-- Navbar -->
 <?php 
 include("connection.php");
 session_start();
 $login_id=$_SESSION['login_id'];
     $sql = "SELECT * FROM tbl_user where login_id='$login_id'";
    $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_array($result);
       // $_SESSION['login_id'] = $row['login_id'];
     // $user_name="SELECT name FROM tbl_user WHERE login_id=$row['login_id']";
      ?>
    
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-desktop me-2"></i>BYD
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <a href="Cart.php" class="nav-link position-relative">
                            <i class="fas fa-shopping-cart cart-icon">
                                <span class="cart-count"></span>
                            </i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="mainstyle/img/user_avatar.jpg" alt="User" class="user-avatar me-2">
                            <span><?php echo $row['name'];?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="user_edit_profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="user_view_orders.php"><i class="fas fa-clipboard-list me-2"></i>View Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
