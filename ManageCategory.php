<?php
if (!file_exists("connection.php")) {
    die("connection.php not found");
}
include("connection.php");
include("Headers/admin_nav.php");

// Handle form submission for adding a new category
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Name = $_POST['categoryName'];
   
    // Handle image upload
    $image = $_FILES['categoryImage']['name'];
    $image_tmp = $_FILES['categoryImage']['tmp_name'];
    $upload_dir = "Uploads/";
    
    // Validate and move uploaded file
    if (!empty($image)) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            move_uploaded_file($image_tmp, $upload_dir . $image);
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }
    
    // Insert into database if no errors
    if (!isset($error)) {
        $sql = "INSERT INTO tbl_category(name, image) VALUES ('$Name', '$image')";
        if (mysqli_query($conn, $sql)) {
            $success = "Category added successfully!";
        } else {
            $error = "Error adding category: " . mysqli_error($conn);
        }
    }
}

// Handle category deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get the image path to delete it from server
    $sql = "SELECT image FROM tbl_category WHERE category_id = $delete_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    

    // Delete the category from database
    $sql = "DELETE FROM tbl_category WHERE category_id = $delete_id";
    if (mysqli_query($conn, $sql)) {
        $success = "Category deleted successfully!";
    } else {
        $error = "Error deleting category: " . mysqli_error($conn);
    }
    
    // Redirect to avoid resubmission
    header("Location: ManageCategory.php");
    exit();
}
?>
 <style>
    
   body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }

      
        .admin-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary);
        }

        .form-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(34, 74, 190, 0.2);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            border: 2px dashed var(--gray-light);
            border-radius: 4px;
            background-color: var(--light);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            background-color: rgba(34, 74, 190, 0.05);
        }

        .file-upload-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .file-upload-text {
            font-size: 1rem;
            color: var(--gray);
        }

        .file-upload-text strong {
            color: var(--primary);
        }

        .file-upload-preview {
            margin-top: 1rem;
            max-width: 200px;
            max-height: 200px;
            display: none;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        /* Dropdown Menu Styles */
        .nav-item.has-dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            width: 220px;
            background-color: var(--dark-light);
            border-radius: 0 4px 4px 4px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
            padding: 5px 0;
            z-index: 100;
        }

        .nav-item.has-dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu li {
            padding: 0;
        }

        .dropdown-menu a {
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            display: block;
            transition: all 0.3s;
        }

        .dropdown-menu a:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            padding-left: 20px;
        }

        .dropdown-menu i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
        }

       /* Submenu Styles - Fixed Version */

        .categories-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .categories-table th, .categories-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .categories-table th {
            background-color: #224abe;
            color: white;
        }
        
        .categories-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .category-image {
            max-width: 80px;
            max-height: 50px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        
        .edit-btn {
            background-color: #28a745;
        }
        
        .edit-btn:hover {
            background-color: #218838;
        }
        
        .delete-btn {
            background-color: #dc3545;
        }
        
        .delete-btn:hover {
            background-color: #c82333;
        }
        
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            padding: 20px;
        }
        
        .card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .card-header h3 {
            margin: 0;
            color: var(--primary);
        }
    </style>
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-container">
                <div class="form-header">
                    <h2><i class="fas fa-list"></i> Add New Category</h2>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="categoryName">Category Name</label>
                        <input type="text" id="categoryName" name="categoryName" class="form-control" placeholder="Enter category name" required>
                    </div>


                    <div class="form-group">
                        <label for="categoryImage">Category Image</label>
                        <div class="file-upload">
                            <input type="file" id="categoryImage" name="categoryImage" class="file-upload-input" accept="image/*">
                            <label for="categoryImage" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <span class="file-upload-text">
                                    <strong>Click to upload</strong> or drag and drop<br>
                                    (PNG, JPG, GIF - Max 2MB)
                                </span>
                                <img id="imagePreview" class="file-upload-preview" alt="Image preview">
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Save Category
                        </button>
                    </div>
                </form>

                <div class="form-footer">
                    <a href="ManageCategory.php"><i class="fas fa-arrow-left"></i> Back to Categories List</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Category List</h3>
                </div>
                <div class="card-body">
                    <table class="categories-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tbl_category ORDER BY category_id DESC";
                            $result = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                        <td>{$row['category_id']}</td>
                                        <td>";
                                        
                                    if (!empty($row['image'])) {
                                        echo "<img src='Uploads/{$row['image']}' class='category-image' alt='Category Image'>";
                                    } else {
                                        echo "No Image";
                                    }
                                    
                                    echo "</td>
                                        <td>{$row['name']}</td>
                                        
                                        <td class='action-buttons'>
                                            <a href='edit_category.php?id={$row['category_id']}' class='action-btn edit-btn'><i class='fas fa-edit'></i> Edit</a>
                                            <a href='?delete_id={$row['category_id']}' class='action-btn delete-btn' onclick='return confirm(\"Are you sure you want to delete this category?\")'>
                                                <i class='fas fa-trash'></i> Delete
                                            </a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No categories found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview uploaded image
        document.getElementById('categoryImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                    
                    // Update upload label
                    document.querySelector('.file-upload-text').innerHTML = 
                        `<strong>${file.name}</strong><br>${(file.size/1024/1024).toFixed(2)} MB`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            // You can add additional validation here if needed
            // e.preventDefault(); // Uncomment to prevent normal form submission for AJAX
        });
    </script>             

</body>
</html>