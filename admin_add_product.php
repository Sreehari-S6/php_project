<?php
include('connection.php');
   include('Headers/admin_nav.php');

// Fetch categories
$category_sql = "SELECT category_id, name FROM tbl_category";
$category_result = $conn->query($category_sql);

// Fetch brands
$brand_sql = "SELECT brand_id, brand_name FROM tbl_brand";
$brand_result = $conn->query($brand_sql);

if (isset($_POST['submit'])) {
    // Get form data
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_FILES['image']['name'];
    $description = $_POST['description'];

    // Check if the product already exists (including image check)
    $check_sql = "SELECT * FROM tbl_products WHERE category_id = ? AND brand_id = ? AND price = ? AND stock = ? AND description = ? AND image = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ssisss", $category, $brand, $price, $stock, $description, $image);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "This product already exists!";
    } else {
        // Handle file upload (check if file upload is successful)
        if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image)) {
            // Insert into products table
            $stmt = $conn->prepare("INSERT INTO tbl_products (category_id, brand_id, price, stock, image, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisss", $category, $brand, $price, $stock, $image, $description);

            if ($stmt->execute()) {
                $product_id = $stmt->insert_id;

                // Handle category-specific specifications
                if (in_array($category, [7,8,9,10,11])) {
                    $model = $_POST['model'] ?? '';
                    $socket_type = $_POST['socket_type'] ?? '';
                    $chipset = $_POST['chipset'] ?? '';
                    $form_factor = $_POST['form_factor'] ?? '';

                    $stmt = $conn->prepare("INSERT INTO tbl_productspecifications (product_id, model, socket_type, chipset, form_factor) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issss", $product_id, $model, $socket_type, $chipset, $form_factor);
                    $stmt->execute();
                }

                // Insert category-specific details
                if ($category == "7") { // Motherboard
                    $supported_ram_type = $_POST['supported_ram_type'];
                    $supported_speed = $_POST['supported_speed'];
                    $storage_interface = $_POST['storage_interface'];

                    $stmt = $conn->prepare("INSERT INTO tbl_motherboard (product_id, supported_ram_type, supported_speed, storage_interface) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $product_id, $supported_ram_type, $supported_speed, $storage_interface);
                    $stmt->execute();
                } elseif ($category == "9") { // Processor
                    $base_clock_speed = $_POST['base_clock_speed'];
                    $max_boost_clock_speed = $_POST['max_boost_clock_speed'];
                    $number_of_cores = $_POST['number_of_cores'];
                    $number_of_threads = $_POST['number_of_threads'];
                    $tdp = $_POST['tdp'];
                    $cache = $_POST['cache'];
                    $integrated_graphics = $_POST['integrated_graphics'];
                    $architecture = $_POST['architecture'];
                    $supported_memory_types = $_POST['supported_memory_types'];
                    $max_memory_size = $_POST['max_memory_size'];
                    $max_memory_speed = $_POST['max_memory_speed'];

                    $stmt = $conn->prepare("INSERT INTO tbl_processor (product_id, base_clock_speed, max_boost_clock_speed, number_of_cores, number_of_threads, tdp, cache, integrated_graphics, architecture, supported_memory_types, max_memory_size, max_memory_speed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("dddddddssssd", $product_id, $base_clock_speed, $max_boost_clock_speed, $number_of_cores, $number_of_threads, $tdp, $cache, $integrated_graphics, $architecture, $supported_memory_types, $max_memory_size, $max_memory_speed);
                    $stmt->execute();
                } elseif ($category == "8") { // RAM
                    $speed = $_POST['speed'];
                    $type = $_POST['type'];

                    $stmt = $conn->prepare("INSERT INTO tbl_ram (product_id, speed, type) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $product_id, $speed, $type);
                    $stmt->execute();
                } elseif ($category == "11") { // Storage
                    $interface = $_POST['interface'];

                    $stmt = $conn->prepare("INSERT INTO tbl_storage (product_id, interface) VALUES (?, ?)");
                    $stmt->bind_param("is", $product_id, $interface);
                    $stmt->execute();
                } elseif ($category == "10") { // Case
                    $form_factor = $_POST['form_factor'];

                    $stmt = $conn->prepare("INSERT INTO tbl_case_table (product_id, form_factor) VALUES (?, ?)");
                    $stmt->bind_param("is", $product_id, $form_factor);
                    $stmt->execute();
                }

                $_SESSION['success_message'] = "Product added successfully!";
                header("Location:admin_add_product.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Error: " . $stmt->error;
            }
        } else {
            $_SESSION['error_message'] = "Error: Failed to upload image.";
        }
    }

    $check_stmt->close();
    $stmt->close();
    $conn->close();
}
?>
  <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            resize: vertical;
        }
        .hidden {
            display: none;
        }
        button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
    <div class="container">
        <h1>Add Product</h1>
        <form action="admin_add_product.php" method="post" enctype="multipart/form-data">
            <label for="category">Category:</label>
            <select id="category" name="category" onchange="showFields()" required>
                <option value="">Select Category</option>
                <?php
                if ($category_result->num_rows > 0) {
                    while ($row = $category_result->fetch_assoc()) {
                        echo "<option value='" . $row['category_id'] . "'>" . $row['name'] . "</option>";
                    }
                }
                ?>
            </select><br><br>

            <label for="brand">Brand:</label>
            <select id="brand" name="brand" required>
                <option value="">Select Brand</option>
                <?php
                if ($brand_result->num_rows > 0) {
                    while ($row = $brand_result->fetch_assoc()) {
                        echo "<option value='" . $row['brand_id'] . "'>" . $row['brand_name'] . "</option>";
                    }
                }
                ?>
            </select><br><br>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" required><br><br>

            <label for="stock">Stock:</label>
            <input type="number" id="stock" name="stock" required><br><br>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image" required><br><br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea><br><br>

            <div id="category-fields" class="hidden">
                <label for="model">Model:</label>
                <input type="text" id="model" name="model"><br><br>

                <label for="socket_type">Socket Type:</label>
                <input type="text" id="socket_type" name="socket_type"><br><br>

                <label for="chipset">Chipset:</label>
                <input type="text" id="chipset" name="chipset"><br><br>

                <label for="form_factor">Form Factor:</label>
                <input type="text" id="form_factor" name="form_factor"><br><br>
            </div>

            <div id="motherboard-fields" class="hidden">
                <label for="model">Supported RAM:</label>
                <input type="text" id="supported_ram_type" name="supported_ram_type"><br><br>

                <label for="socket_type">Storage Interface:</label>
                <input type="text" id="storage_interface" name="storage_interface"><br><br>

                <label for="chipset">Supported Speed:</label>
                <input type="text" id="supported_speed" name="supported_speed"><br><br>

                
            </div>

            <div id="processor-fields" class="hidden">
                <label for="base_clock_speed">Base Clock Speed (GHz):</label>
                <input type="number" step="0.1" id="base_clock_speed" name="base_clock_speed"><br><br>

                <label for="max_boost_clock_speed">Max Boost Clock Speed (GHz):</label>
                <input type="number" step="0.1" id="max_boost_clock_speed" name="max_boost_clock_speed"><br><br>

                <label for="number_of_cores">Number of Cores:</label>
                <input type="number" id="number_of_cores" name="number_of_cores"><br><br>

                <label for="number_of_threads">Number of Threads:</label>
                <input type="number" id="number_of_threads" name="number_of_threads"><br><br>

                <label for="tdp">TDP (W):</label>
                <input type="number" id="tdp" name="tdp"><br><br>

                <label for="cache">Cache (MB):</label>
                <input type="number" id="cache" name="cache"><br><br>

                <label for="integrated_graphics">Integrated Graphics:</label>
                <input type="text" id="integrated_graphics" name="integrated_graphics"><br><br>

                <label for="architecture">Architecture:</label>
                <input type="text" id="architecture" name="architecture"><br><br>

                <label for="supported_memory_types">Supported Memory Types:</label>
                <input type="text" id="supported_memory_types" name="supported_memory_types"><br><br>

                <label for="max_memory_size">Max Memory Size (GB):</label>
                <input type="number" id="max_memory_size" name="max_memory_size"><br><br>

                <label for="max_memory_speed">Max Memory Speed (MHz):</label>
                <input type="number" id="max_memory_speed" name="max_memory_speed"><br><br>
            </div>

            <div id="ram-fields" class="hidden">
                <label for="speed">Speed (MHz):</label>
                <input type="number" id="speed" name="speed"><br><br>

                <label for="type">Type:</label>
                <input type="text" id="type" name="type"><br><br>
            </div>

            <div id="storage-fields" class="hidden">
                <label for="interface">Interface:</label>
                <input type="text" id="interface" name="interface"><br><br>
            </div>

            <div id="case-fields" class="hidden">
                <label for="form_factor">Form Factor:</label>
                <input type="text" id="form_factor" name="form_factor"><br><br>
            </div>

            <button type="submit" name="submit">Add Product</button>
        </form>
    </div>

    <script>
        function showFields() {
            let category = document.getElementById('category').value;
            
            // Hide all category-specific fields initially
            document.getElementById('motherboard-fields').classList.add('hidden');
            document.getElementById('processor-fields').classList.add('hidden');
            document.getElementById('ram-fields').classList.add('hidden');
            document.getElementById('storage-fields').classList.add('hidden');
            document.getElementById('case-fields').classList.add('hidden');

            // Show fields based on selected category
           if (category == '7' || category == '8' || category == '9' || category == '10' || category == '11') {
    document.getElementById('category-fields').classList.remove('hidden');
}
            if (category == "7") { // Motherboard
                document.getElementById('motherboard-fields').classList.remove('hidden');
            } else if (category == "9") { // Processor
                document.getElementById('processor-fields').classList.remove('hidden');
            } else if (category == "8") { // RAM
                document.getElementById('ram-fields').classList.remove('hidden');
            } else if (category == "11") { // Storage
                document.getElementById('storage-fields').classList.remove('hidden');
            } else if (category == "10") { // Case
                document.getElementById('case-fields').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>