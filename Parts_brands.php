<?php
include("connection.php");

// Fetch all brands
$brands_sql = "SELECT * FROM tbl_brand";
$brands_result = $conn->query($brands_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brands - BYD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0c4c8dff;
        }
        .navbar {
            background-color: #11003cff;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.8rem;
            color: #fff !important;
        }
        .search-box {
            width: 400px;
        }
        .brand-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .brand-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .brand-img-container {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .brand-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .brand-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .category-header {
            background-color: #343a40;
            color: white;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .nav-link.active {
            font-weight: bold;
            color: #0d6efd !important;
        }
    </style>
</head>
<body>
  <?php include('Headers/user_parts_nav.php'); ?>

    <div class="container">
        <!-- Category Header -->
        <div class="category-header">
            <h2 class="mb-0">Our Brands</h2>
        </div>

        <div class="row">
            <?php
            if ($brands_result->num_rows > 0) {
                while($brand = $brands_result->fetch_assoc()) {
                    echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-4">';
                    echo '<div class="brand-card">';
                    
                    // Brand logo
                    echo '<div class="brand-img-container">';
                    if (!empty($brand['logo'])) {
                        echo '<img src="Uploads/'.htmlspecialchars($brand['logo']).'" class="brand-img" alt="'.htmlspecialchars($brand['brand_name']).' Logo">';
                    } else {
                        echo '<div class="text-muted">No Logo</div>';
                    }
                    echo '</div>';
                    
                    // Brand name
                    echo '<h3 class="brand-title">'.htmlspecialchars($brand['brand_name']).'</h3>';
                    
                    echo '</div>'; // End of brand-card
                    echo '</div>'; // End of col
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info">No brands found.</div></div>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About BYD</h5>
                    <p>Your one-stop shop for quality products at competitive prices.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Home</a></li>
                        <li><a href="#" class="text-white">Products</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address>
                        Email: info@byd.com<br>
                        Phone: +1 234 567 890
                    </address>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>