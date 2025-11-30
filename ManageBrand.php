<?php
if (!file_exists("connection.php")) {
    die("connection.php not found");
}
include("connection.php");
include("Headers/admin_nav.php");
// include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bname = $_POST['brandName'];

    $logo = $_FILES['brandLogo']['name'];
    $logo_tmp = $_FILES['brandLogo']['tmp_name'];
    $upload_dir = "Uploads/";
    move_uploaded_file($logo_tmp, $upload_dir . $logo);

    $sql = "INSERT INTO tbl_brand(brand_name,logo) VALUES ('$bname' , '$logo')";
    mysqli_query($conn, $sql);
}
?>


    <style>
        :root {
            --primary: #224abe;
            --primary-light: #3a5bcc;
            --primary-dark: #1a3a9b;
            --dark: #121212;
            --dark-light: #1e1e1e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }
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

        /* Submenu Styles */
       
        .brands-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .brands-table th, .brands-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .brands-table th {
            background-color: #224abe;
            color: white;
        }
        
        .brands-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .brand-logo {
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
    </style>
 
     
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-container">
                <div class="form-header">
                    <h2><i class="fas fa-tag"></i> Add New Brand</h2>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="brandName">Brand Name</label>
                        <input type="text" id="brandName" name="brandName" class="form-control" placeholder="Enter brand name" required>
                    </div>

                    <div class="form-group">
                        <label for="brandLogo">Brand Logo</label>
                        <div class="file-upload">
                            <input type="file" id="brandLogo" name="brandLogo" class="file-upload-input" accept="image/*" required>
                            <label for="brandLogo" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <span class="file-upload-text">
                                    <strong>Click to upload</strong> or drag and drop<br>
                                    (PNG, JPG, SVG - Max 2MB)
                                </span>
                                <img id="logoPreview" class="file-upload-preview" alt="Logo preview">
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Save Brand
                        </button>
                    </div>
                </form>

                <div class="form-footer">
                    <a href="brands.html"><i class="fas fa-arrow-left"></i> Back to Brands List</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Brand List</h3>
                </div>
                <div class="card-body">
                    <table class="brands-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Logo</th>
                                <th>Brand Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tbl_brand ORDER BY brand_id DESC";
                            $result = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                        <td>{$row['brand_id']}</td>
                                        <td><img src='Uploads/{$row['logo']}' class='brand-logo' alt='Brand Logo'></td>
                                        <td>{$row['brand_name']}</td>
                                        <td class='action-buttons'>
                                            <a href='edit_brand.php?id={$row['brand_id']}' class='action-btn edit-btn'><i class='fas fa-edit'></i> Edit</a>
                                            <a href='?delete_id={$row['brand_id']}' class='action-btn delete-btn' onclick='return confirm(\"Are you sure you want to delete this brand?\")'>
                                                <i class='fas fa-trash'></i> Delete
                                            </a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No brands found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview uploaded logo
        document.getElementById('brandLogo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('logoPreview');
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
        document.getElementById('brandForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Here you would normally handle the form submission via AJAX or let it submit normally
            alert('Brand submitted successfully!');
            // this.submit(); // Uncomment to allow normal form submission
        });
    </script>             

</body>
</html>