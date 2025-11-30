<?php
include("connection.php");

// Get category and product_id from URL if specified
$category = isset($_GET['category']) ? $_GET['category'] : '';
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';

// If a specific product is requested
if ($product_id) {
    // Fetch base product information
    $product_sql = "SELECT 
                p.product_id,
                p.price,
                p.stock,
                p.image,
                p.description,
                c.name as category_name,
                b.brand_name
            FROM tbl_products AS p
            LEFT JOIN tbl_category c ON p.category_id = c.category_id
            LEFT JOIN tbl_brand b ON p.brand_id = b.brand_id
            WHERE p.product_id = $product_id";
    $product_result = $conn->query($product_sql);
    $product = $product_result->fetch_assoc();
    
    // Fetch specifications based on category
    $specs = [];
    $spec_table = '';
    
    switch($product['category_name']) {
        case 'Processor':
            $spec_table = 'tbl_processor';
            break;
        case 'Motherboard':
            $spec_table = 'tbl_motherboard';
            break;
        case 'RAM':
            $spec_table = 'tbl_ram';
            break;
        case 'Storage':
            $spec_table = 'tbl_storage';
            break;
        case 'Case':
            $spec_table = 'tbl_case_table';
            break;
    }
    
    if ($spec_table) {
        $spec_sql = "SELECT * FROM $spec_table WHERE product_id = $product_id";
        $spec_result = $conn->query($spec_sql);
        $specs = $spec_result->fetch_assoc();
    }
    
    // Fetch model and additional specifications from tbl_productspecifications
    $specs_sql = "SELECT * FROM tbl_productspecifications WHERE product_id = $product_id";
    $specs_result = $conn->query($specs_sql);
    $additional_specs = [];
    $product_model = '';
    
    while($row = $specs_result->fetch_assoc()) {
        if (strtolower($row['model']) == 'model') {
            $product_model = $row['spec_value'];
        } else {
            $additional_specs[] = $row;
        }
    }
} 
// If no specific product is requested, show product listing
else {
    // Fetch products with joined data
    $sql = "SELECT 
                p.product_id,
                p.price,
                p.stock,
                p.image,
                p.description,
                c.name as category_name,
                b.brand_name,
                ps.model
            FROM tbl_products AS p
            LEFT JOIN tbl_category c ON p.category_id = c.category_id
            LEFT JOIN tbl_brand b ON p.brand_id = b.brand_id
            LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id";
    
    if ($category) {
        $sql .= " WHERE c.name = '$category'";
    }
    
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog - BYD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
     body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #b0a4c7ff;
        }
        .navbar {
            background-color: #260579ff;
        }
     .navbar-brand {
            font-weight: bold;
            font-size: 1.8rem;
            color: #fff !important;
        }
        .search-box {
            width: 400px;
        }
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-img-container {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background: #f8f9fa;
        }
        .product-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            width: auto;
            height: auto;
        }
        .product-title {
            font-weight: 600;
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .product-description {
            font-size: 0.9rem;
            font-weight: bold;
            color: #6c757d;
            height: 60px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            margin-bottom: 0.5rem;
        }
        .price {
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .stock-status {
            font-size: 0.8rem;
            margin-top: 5px;
            margin-bottom: 0.5rem;
        }
        .in-stock {
            color: #28a745;
        }
        .out-of-stock {
            color: #dc3545;
        }
        .brand-badge {
            background-color: #f8f9fa;
            color: #343a40;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 5px;
        }
        .category-badge {
            background-color: #2f00ffff;
            color: #ffffffff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 5px;
        }
        .category-header {
            background-color: #5c00d3ff;
            color: white;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .btn-cart {
            margin-top: auto;
        }
        .specs-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .specs-table th {
            background-color: #f8f9fa;
            width: 30%;
        }
        .back-button {
            margin-bottom: 20px;
        }
        .category-nav {
            margin-bottom: 20px;
        }
        .category-nav .nav-link {
            color: #ffffffff;
            font-weight: 500;
        }
        .category-nav .nav-link.active {
            color: #ffffffff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include('Headers/user_parts_nav.php'); ?>

    <div class="container">
        <!-- Category Navigation -->
        <ul class="nav category-nav">
            <li class="nav-item">
                <a class="nav-link <?= $category == 'Processor' ? 'active' : '' ?>" href="?category=Processor">Processors</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $category == 'Motherboard' ? 'active' : '' ?>" href="?category=Motherboard">Motherboards</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $category == 'RAM' ? 'active' : '' ?>" href="?category=RAM">RAM</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $category == 'Storage' ? 'active' : '' ?>" href="?category=Storage">Storage</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $category == 'Case' ? 'active' : '' ?>" href="?category=Case">Cases</a>
            </li>
        </ul>

        <?php if ($product_id && $product): ?>
            <!-- Product Detail View -->
            <div class="row">
                <div class="col-md-12">
                    <a href="?category=<?= $product['category_name'] ?>" class="btn btn-secondary back-button">
                        <i class="fas fa-arrow-left"></i> Back to <?= $product['category_name'] ?>
                    </a>
                </div>
                
                <div class="col-md-6">
                    <div class="product-img-container">
                        <img src="Uploads/<?= htmlspecialchars($product['image']) ?>" class="product-img" alt="Product Image">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <!-- Brand name in smaller text above model -->
                    <div class="brand-name" style="font-size: 2.6rem; color: #FFFFFF; margin-bottom: 0.25rem;">
                        <?= htmlspecialchars($product['brand_name']) ?>
                    </div>
                    
                    <!-- Main heading shows the model -->
                    <h2 style="margin-top: 0;"><?= htmlspecialchars($product_model) ?></h2>
                    
                    <span class="category-badge"><?= htmlspecialchars($product['category_name']) ?></span>
                    
                    <div class="price-container mt-3">
                        <span class="price">₹<?= number_format($product['price'], 2) ?></span>
                    </div>
                    
                    <div class="stock-status <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?> mt-2">
                        <?= $product['stock'] > 0 ? 'In Stock ('.htmlspecialchars($product['stock']).')' : 'Out of Stock' ?>
                    </div>
                    
               <form method="post" action="add_to_cart.php">
    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
    <input type="hidden" name="return_url" value="<?= urlencode($_SERVER['REQUEST_URI']) ?>">
    <div class="input-group mb-3" style="width: 150px;">
        <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?= $product['stock'] ?>">
    </div>
    <button type="submit" name="add_to_cart" class="btn btn-primary btn-cart mt-3" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
        <?= $product['stock'] > 0 ? 'Add to Cart' : 'Notify Me' ?>
    </button>
</form>
                    
                    <!-- Display the description -->
                    <?php if (!empty($product['description'])): ?>
                        <div class="mt-3" style="color: white;">
                            <h4 style="color: #aaf19cff;">Product Description</h4>
                            <p style="color: white;"><?= htmlspecialchars($product['description']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
                    
            <div class="col-md-12 mt-4">
                <h4>Specifications</h4>
                
                <?php if ($specs): ?>
                    <table class="table specs-table">
                        <tbody>
                            <?php foreach ($specs as $key => $value): ?>
                                <?php if ($key != 'product_id' && $value !== null): ?>
                                    <tr>
                                        <th><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></th>
                                        <td><?= htmlspecialchars($value) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <?php if (!empty($additional_specs)): ?>
                    <h5>Additional Specifications</h5>
                    <table class="table specs-table">
                        <tbody>
                            <?php foreach ($additional_specs as $spec): ?>
                                <tr>
                                    <th><?= htmlspecialchars($spec['model']) ?></th>
                                    <td><?= htmlspecialchars($product['description']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Product List View -->
            <div class="category-header">
                <h2 class="mb-0"><?= $category ? htmlspecialchars($category) : 'All Products' ?></h2>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <?php
                        if (isset($result) && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex align-items-stretch">';
                                echo '<div class="product-card w-100 d-flex flex-column">';
                                
                                // Product image
                                echo '<div class="product-img-container">';
                                echo '<a href="?product_id='.$row['product_id'].($category ? '&category='.$category : '').'">';
                                echo '<img src="Uploads/'.htmlspecialchars($row['image']).'" class="product-img" alt="Product Image">';
                                echo '</a>';
                                echo '</div>';
                                
                                // Product details
                                echo '<div class="p-3 flex-grow-1 d-flex flex-column">';
                                
                                // Brand and Category badges
                                if (!empty($row['brand_name'])) {
                                    echo '<span class="brand-badge">'.htmlspecialchars($row['brand_name']).'</span>';
                                }
                                if (!empty($row['category_name'])) {
                                    echo '<span class="category-badge">'.htmlspecialchars($row['category_name']).'</span>';
                                }
                                
                                // Model as title
                                echo '<a href="?product_id='.$row['product_id'].($category ? '&category='.$category : '').'" class="product-title">';
                                echo htmlspecialchars($row['model'] ? $row['model'] : $row['description']);
                                echo '</a>';
                                
                                // Price
                                echo '<div class="price-container">';
                                echo '<span class="price">₹'.number_format($row['price'], 2).'</span>';
                                echo '</div>';
                                
                                // Stock status
                                echo '<div class="stock-status '.($row['stock'] > 0 ? 'in-stock' : 'out-of-stock').'">';
                                echo $row['stock'] > 0 ? 'In Stock ('.htmlspecialchars($row['stock']).')' : 'Out of Stock';
                                echo '</div>';
                                
                                // Add to cart button
                               echo '<button onclick="addToCart(' . $row['product_id'] . ')" class="btn btn-primary btn-cart mt-auto" ' . ($row['stock'] <= 0 ? 'disabled' : '') . '>';
echo $row['stock'] > 0 ? 'Add to Cart' : 'Notify Me';
echo '</button>';
                                
                                echo '</div>'; // End of product details
                                echo '</div>'; // End of product-card
                                echo '</div>'; // End of col
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">No products found.</div></div>';
                        }
                        ?>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
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
    <script>
        function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&quantity=1&add_to_cart=1`
    })
    .then(response => response.text())
    .then(data => {
        alert("ITEM ADDED TO CART");
        // Optional: Update cart counter dynamically
    })
    .catch(error => console.error('Error:', error));
}
</script>
</body>
</html>