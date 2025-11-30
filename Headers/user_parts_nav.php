
<!-- Navigation -->
 
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">BYD Products</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="user_home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="Parts.php">All Products</a></li>
                <li class="nav-item"><a class="nav-link" href="Parts_category.php">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="Parts_brands.php">Brands</a></li>
                <li class="nav-item"><a class="nav-link" href="Parts_Contact.php">Contact</a></li>
            </ul>
            <form class="d-flex">
                <input class="form-control me-2 search-box" type="search" placeholder="Search products...">
                <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <ul class="navbar-nav ms-3">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="Cart.php"><i class="fas fa-shopping-cart"></i></a></li>
            </ul>
        </div>
    </div>
</nav>