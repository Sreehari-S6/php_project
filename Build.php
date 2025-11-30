<?php
include("connection.php");
session_start();

// Initialize session for PC build if not exists
if (!isset($_SESSION['pc_build'])) {
    $_SESSION['pc_build'] = [
        'processor' => null,
        'motherboard' => null,
        'case' => null,
        'ram' => null,
        'storage' => null
    ];
}

// Handle component selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['component_type'])) {
    $componentType = $_POST['component_type'];
    $productId = $_POST['product_id'];
    
    // Fetch base product info
    $baseSql = "SELECT p.*, ps.model, ps.socket_type FROM tbl_products p 
                LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
                WHERE p.product_id = ?";
    $baseStmt = $conn->prepare($baseSql);
    $baseStmt->bind_param("i", $productId);
    $baseStmt->execute();
    $baseResult = $baseStmt->get_result();
    
    if ($baseResult->num_rows > 0) {
        $baseData = $baseResult->fetch_assoc();
        
        // Fetch detailed specs from specific component table
        switch($componentType) {
            case 'processor':
                $detailSql = "SELECT * FROM tbl_processor WHERE product_id = ?";
                break;
            case 'motherboard':
                $detailSql = "SELECT * FROM tbl_motherboard WHERE product_id = ?";
                break;
            case 'case':
                $detailSql = "SELECT * FROM tbl_case_table WHERE product_id = ?";
                break;
            case 'ram':
                $detailSql = "SELECT * FROM tbl_ram WHERE product_id = ?";
                break;
            case 'storage':
                $detailSql = "SELECT * FROM tbl_storage WHERE product_id = ?";
                break;
        }
        
        $detailStmt = $conn->prepare($detailSql);
        $detailStmt->bind_param("i", $productId);
        $detailStmt->execute();
        $detailResult = $detailStmt->get_result();
        
        if ($detailResult->num_rows > 0) {
            $detailData = $detailResult->fetch_assoc();
            $_SESSION['pc_build'][$componentType] = array_merge($baseData, $detailData);
            
            // Clear dependent components if needed
            if ($componentType === 'processor') {
                $_SESSION['pc_build']['motherboard'] = null;
                $_SESSION['pc_build']['ram'] = null;
            } elseif ($componentType === 'motherboard') {
                $_SESSION['pc_build']['ram'] = null;
            }
        }
    }
}

// Handle reset
if (isset($_GET['reset'])) {
    $_SESSION['pc_build'] = [
        'processor' => null,
        'motherboard' => null,
        'case' => null,
        'ram' => null,
        'storage' => null
    ];
}

// Fetch all processors with their detailed specs, model, and socket type
$processors = $conn->query("
    SELECT p.*, pr.*, ps.model, ps.socket_type 
    FROM tbl_products p
    JOIN tbl_processor pr ON p.product_id = pr.product_id
    LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
    WHERE p.category_id= '9'
");

// Fetch compatible motherboards based on selected processor (memory type AND socket type)
$motherboards = null;
if ($_SESSION['pc_build']['processor']) {
    $memoryType = $_SESSION['pc_build']['processor']['supported_memory_types'];
    $processorSocketType = $_SESSION['pc_build']['processor']['socket_type'];
    
    $motherboards = $conn->query("
        SELECT p.*, mb.*, ps.model, ps.socket_type 
        FROM tbl_products p
        JOIN tbl_motherboard mb ON p.product_id = mb.product_id
        LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
        WHERE p.category_id= '7' 
        AND mb.supported_ram_type = '$memoryType'
        AND ps.socket_type = '$processorSocketType'
    ");
}

// Fetch all cases with their detailed specs and model
$cases = $conn->query("
    SELECT p.*, c.*, ps.model 
    FROM tbl_products p
    JOIN tbl_case_table c ON p.product_id = c.product_id
    LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
    WHERE p.category_id= '10'
");

// Fetch compatible RAM based on selected motherboard
$rams = null;
if ($_SESSION['pc_build']['motherboard']) {
    $ramType = $_SESSION['pc_build']['motherboard']['supported_ram_type'];
    $rams = $conn->query("
        SELECT p.*, r.*, ps.model 
        FROM tbl_products p
        JOIN tbl_ram r ON p.product_id = r.product_id
        LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
        WHERE p.category_id= '8' AND r.type like '%$ramType%'
    ");
}

// Fetch all storage devices with their detailed specs and model
$storages = $conn->query("
    SELECT p.*, s.*, ps.model 
    FROM tbl_products p
    JOIN tbl_storage s ON p.product_id = s.product_id
    LEFT JOIN tbl_productspecifications ps ON p.product_id = ps.product_id
    WHERE p.category_id= '11'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BYD PC BUILDER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #14304cff;
        }
        .builder-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(164, 86, 86, 0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .component-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.3s;
            margin-bottom: 20px;
            height: 100%;
        }
        .component-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .component-card.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9ff;
        }
        .component-img {
            height: 150px;
            object-fit: contain;
            padding: 15px;
        }
        .component-title {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .component-price {
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.4rem;
        }
        .build-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        .progress-bar {
            height: 10px;
            margin-bottom: 30px;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }
        .specs {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .specs p {
            margin-bottom: 0.3rem;
        }
    </style>
</head>
<body>
    
<?php
// This could be part of your PHP file that generates the button
echo '<a href="user_home.php"><button style="background-color: #adaebfff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Back To Home</button></a>';
?>    
<div class="container builder-container">
        <h1 class="text-center mb-4">BYD PC BUILDER</h1>
     
        <!-- Progress Bar -->
        <div class="progress progress-bar">
            <?php
            $selectedCount = count(array_filter($_SESSION['pc_build']));
            $progress = ($selectedCount / 5) * 100;
            ?>
            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress ?>%" 
                 aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        
        <div class="row">
            <!-- Component Selection -->
            <div class="col-md-8">
                <ul class="nav nav-tabs mb-4" id="componentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= !$_SESSION['pc_build']['processor'] ? 'active' : '' ?>" 
                                id="processor-tab" data-bs-toggle="tab" data-bs-target="#processor" type="button">
                            Processor <?= $_SESSION['pc_build']['processor'] ? '<i class="fas fa-check text-success"></i>' : '' ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $_SESSION['pc_build']['processor'] && !$_SESSION['pc_build']['motherboard'] ? 'active' : '' ?> 
                                <?= !$_SESSION['pc_build']['processor'] ? 'disabled' : '' ?>" 
                                id="motherboard-tab" data-bs-toggle="tab" data-bs-target="#motherboard" type="button">
                            Motherboard <?= $_SESSION['pc_build']['motherboard'] ? '<i class="fas fa-check text-success"></i>' : '' ?>
                        </button>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $_SESSION['pc_build']['motherboard'] && !$_SESSION['pc_build']['ram'] ? 'active' : '' ?> 
                                <?= !$_SESSION['pc_build']['motherboard'] ? 'disabled' : '' ?>" 
                                id="ram-tab" data-bs-toggle="tab" data-bs-target="#ram" type="button">
                            RAM <?= $_SESSION['pc_build']['ram'] ? '<i class="fas fa-check text-success"></i>' : '' ?>
                        </button>
                    </li>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="case-tab" data-bs-toggle="tab" data-bs-target="#case" type="button">
                            Case <?= $_SESSION['pc_build']['case'] ? '<i class="fas fa-check text-success"></i>' : '' ?>
                        </button>
                    </li>
                    
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="storage-tab" data-bs-toggle="tab" data-bs-target="#storage" type="button">
                            Storage <?= $_SESSION['pc_build']['storage'] ? '<i class="fas fa-check text-success"></i>' : '' ?>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="componentTabsContent">
                    <!-- Processor Tab -->
                    <div class="tab-pane fade <?= !$_SESSION['pc_build']['processor'] ? 'show active' : '' ?>" id="processor" role="tabpanel">
                        <h3>Select Processor</h3>
                        <div class="row">
                            <?php while($processor = $processors->fetch_assoc()): ?>
                            <div class="col-md-6">
                                <div class="component-card card p-3 <?= $_SESSION['pc_build']['processor'] && $_SESSION['pc_build']['processor']['product_id'] == $processor['product_id'] ? 'selected' : '' ?>">
                                    <img src="uploads/<?= htmlspecialchars($processor['image']) ?>" class="component-img card-img-top" >
                                    <div class="card-body">
                                        <h5 class="component-title card-title"><?= htmlspecialchars($processor['model']) ?></h5>
                                        <div class="specs mb-2">
                                            <p><strong>Socket:</strong> <?= htmlspecialchars($processor['socket_type']) ?></p>
                                            <p><strong>Cores/Threads:</strong> <?= htmlspecialchars($processor['number_of_cores']) ?>/<?= htmlspecialchars($processor['number_of_threads']) ?></p>
                                            <p><strong>Clock Speed:</strong> <?= htmlspecialchars($processor['base_clock_speed']) ?> GHz (Boost up to <?= htmlspecialchars($processor['max_boost_clock_speed']) ?> GHz)</p>
                                            <p><strong>Cache:</strong> <?= htmlspecialchars($processor['cache']) ?></p>
                                            <p><strong>TDP:</strong> <?= htmlspecialchars($processor['TDP']) ?>W</p>
                                            <p><strong>Memory Support:</strong> <?= htmlspecialchars($processor['supported_memory_types']) ?> up to <?= htmlspecialchars($processor['max_memory_speed']) ?> MHz</p>
                                        </div>
                                        <p class="component-price">₹<?= number_format($processor['price'], 2) ?></p>
                                        <?php if (!$_SESSION['pc_build']['processor'] || $_SESSION['pc_build']['processor']['product_id'] != $processor['product_id']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="component_type" value="processor">
                                            <input type="hidden" name="product_id" value="<?= $processor['product_id'] ?>">
                                            <button type="submit" class="btn btn-primary">Select</button>
                                        </form>
                                        <?php else: ?>
                                        <button class="btn btn-success" disabled>Selected</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <!-- Motherboard Tab -->
                    <div class="tab-pane fade <?= $_SESSION['pc_build']['processor'] && !$_SESSION['pc_build']['motherboard'] ? 'show active' : '' ?>" id="motherboard" role="tabpanel">
                        <h3>Select Motherboard</h3>
                        <?php if ($_SESSION['pc_build']['processor']): ?>
                            <?php if ($motherboards && $motherboards->num_rows > 0): ?>
                                <p>Compatible with <?= htmlspecialchars($_SESSION['pc_build']['processor']['model']) ?> 
                                (<?= htmlspecialchars($_SESSION['pc_build']['processor']['socket_type']) ?> Socket, 
                                Supports <?= htmlspecialchars($_SESSION['pc_build']['processor']['supported_memory_types']) ?>)</p>
                                <div class="row">
                                    <?php while($motherboard = $motherboards->fetch_assoc()): ?>
                                    <div class="col-md-6">
                                        <div class="component-card card p-3 <?= $_SESSION['pc_build']['motherboard'] && $_SESSION['pc_build']['motherboard']['product_id'] == $motherboard['product_id'] ? 'selected' : '' ?>">
                                            <img src="uploads/<?= htmlspecialchars($motherboard['image']) ?>" class="component-img card-img-top"  ?>">
                                            <div class="card-body">
                                                <h5 class="component-title card-title"><?= htmlspecialchars($motherboard['model']) ?></h5>
                                                <div class="specs mb-2">
                                                    <p><strong>Socket:</strong> <?= htmlspecialchars($motherboard['socket_type']) ?></p>
                                                    <p><strong>RAM Support:</strong> <?= htmlspecialchars($motherboard['supported_ram_type']) ?> up to <?= htmlspecialchars($motherboard['supported_speed']) ?> MHz</p>
                                                    <p><strong>Storage Interfaces:</strong> <?= htmlspecialchars($motherboard['storage_interface']) ?></p>
                                                </div>
                                                <p class="component-price">₹<?= number_format($motherboard['price'], 2) ?></p>
                                                <?php if (!$_SESSION['pc_build']['motherboard'] || $_SESSION['pc_build']['motherboard']['product_id'] != $motherboard['product_id']): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="component_type" value="motherboard">
                                                    <input type="hidden" name="product_id" value="<?= $motherboard['product_id'] ?>">
                                                    <button type="submit" class="btn btn-primary">Select</button>
                                                </form>
                                                <?php else: ?>
                                                <button class="btn btn-success" disabled>Selected</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">No compatible motherboards found for this processor.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Please select a processor first.</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Case Tab -->
                    <div class="tab-pane fade" id="case" role="tabpanel">
                        <h3>Select Case</h3>
                        <div class="row">
                            <?php while($case = $cases->fetch_assoc()): ?>
                            <div class="col-md-6">
                                <div class="component-card card p-3 <?= $_SESSION['pc_build']['case'] && $_SESSION['pc_build']['case']['product_id'] == $case['product_id'] ? 'selected' : '' ?>">
                                    <img src="uploads/<?= htmlspecialchars($case['image']) ?>" class="component-img card-img-top" >
                                    <div class="card-body">
                                        <h5 class="component-title card-title"><?= htmlspecialchars($case['model']) ?></h5>
                                        <div class="specs mb-2">
                                            <p><strong>Form Factor:</strong> <?= htmlspecialchars($case['form_factor']) ?></p>
                                        </div>
                                        <p class="component-price">₹<?= number_format($case['price'], 2) ?></p>
                                        <?php if (!$_SESSION['pc_build']['case'] || $_SESSION['pc_build']['case']['product_id'] != $case['product_id']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="component_type" value="case">
                                            <input type="hidden" name="product_id" value="<?= $case['product_id'] ?>">
                                            <button type="submit" class="btn btn-primary">Select</button>
                                        </form>
                                        <?php else: ?>
                                        <button class="btn btn-success" disabled>Selected</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <!-- RAM Tab -->
                    <div class="tab-pane fade <?= $_SESSION['pc_build']['motherboard'] && !$_SESSION['pc_build']['ram'] ? 'show active' : '' ?>" id="ram" role="tabpanel">
                        <h3>Select RAM</h3>
                        <?php if ($_SESSION['pc_build']['motherboard']): ?>
                            <?php if ($rams && $rams->num_rows > 0): ?>
                                <p>Compatible with <?= htmlspecialchars($_SESSION['pc_build']['motherboard']['model']) ?> (<?= htmlspecialchars($_SESSION['pc_build']['motherboard']['supported_ram_type']) ?>)</p>
                                <div class="row">
                                    <?php while($ram = $rams->fetch_assoc()): ?>
                                    <div class="col-md-6">
                                        <div class="component-card card p-3 <?= $_SESSION['pc_build']['ram'] && $_SESSION['pc_build']['ram']['product_id'] == $ram['product_id'] ? 'selected' : '' ?>">
                                            <img src="uploads/<?= htmlspecialchars($ram['image']) ?>" class="component-img card-img-top" ?>">
                                            <div class="card-body">
                                                <h5 class="component-title card-title"><?= htmlspecialchars($ram['model']) ?></h5>
                                                <div class="specs mb-2">
                                                    <p><strong>Type:</strong> <?= htmlspecialchars($ram['type']) ?></p>
                                                    <p><strong>Speed:</strong> <?= htmlspecialchars($ram['speed']) ?> MHz</p>
                                                </div>
                                                <p class="component-price">₹<?= number_format($ram['price'], 2) ?></p>
                                                <?php if (!$_SESSION['pc_build']['ram'] || $_SESSION['pc_build']['ram']['product_id'] != $ram['product_id']): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="component_type" value="ram">
                                                    <input type="hidden" name="product_id" value="<?= $ram['product_id'] ?>">
                                                    <button type="submit" class="btn btn-primary">Select</button>
                                                </form>
                                                <?php else: ?>
                                                <button class="btn btn-success" disabled>Selected</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">No compatible RAM found for this motherboard.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Please select a motherboard first.</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Storage Tab -->
                    <div class="tab-pane fade" id="storage" role="tabpanel">
                        <h3>Select Storage</h3>
                        <div class="row">
                            <?php while($storage = $storages->fetch_assoc()): ?>
                            <div class="col-md-6">
                                <div class="component-card card p-3 <?= $_SESSION['pc_build']['storage'] && $_SESSION['pc_build']['storage']['product_id'] == $storage['product_id'] ? 'selected' : '' ?>">
                                    <img src="uploads/<?= htmlspecialchars($storage['image']) ?>" class="component-img card-img-top" >
                                    <div class="card-body">
                                        <h5 class="component-title card-title"><?= htmlspecialchars($storage['model']) ?></h5>
                                        <div class="specs mb-2">
                                            <p><strong>Interface:</strong> <?= htmlspecialchars($storage['interface']) ?></p>
                                        </div>
                                        <p class="component-price">₹<?= number_format($storage['price'], 2) ?></p>
                                        <?php if (!$_SESSION['pc_build']['storage'] || $_SESSION['pc_build']['storage']['product_id'] != $storage['product_id']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="component_type" value="storage">
                                            <input type="hidden" name="product_id" value="<?= $storage['product_id'] ?>">
                                            <button type="submit" class="btn btn-primary">Select</button>
                                        </form>
                                        <?php else: ?>
                                        <button class="btn btn-success" disabled>Selected</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Build Summary -->
            <div class="col-md-4">
                <div class="build-summary">
                    <h3>Your PC Build</h3>
                    
                    <!-- Processor Summary -->
                    <div class="mb-3">
                        <h5>Processor</h5>
                        <?php if ($_SESSION['pc_build']['processor']): ?>
                            <p><?= htmlspecialchars($_SESSION['pc_build']['processor']['model']) ?></p>
                            <div class="specs">
                                <p><strong>Socket:</strong> <?= htmlspecialchars($_SESSION['pc_build']['processor']['socket_type']) ?></p>
                                <p><?= htmlspecialchars($_SESSION['pc_build']['processor']['number_of_cores']) ?> cores / <?= htmlspecialchars($_SESSION['pc_build']['processor']['number_of_threads']) ?> threads</p>
                                <p><?= htmlspecialchars($_SESSION['pc_build']['processor']['base_clock_speed']) ?> - <?= htmlspecialchars($_SESSION['pc_build']['processor']['max_boost_clock_speed']) ?> GHz</p>
                                <p class="text-muted">₹<?= number_format($_SESSION['pc_build']['processor']['price'], 2) ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Not selected</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Motherboard Summary -->
                    <div class="mb-3">
                        <h5>Motherboard</h5>
                        <?php if ($_SESSION['pc_build']['motherboard']): ?>
                            <p><?= htmlspecialchars($_SESSION['pc_build']['motherboard']['model']) ?></p>
                            <div class="specs">
                                <p><strong>Socket:</strong> <?= htmlspecialchars($_SESSION['pc_build']['motherboard']['socket_type']) ?></p>
                                <p><?= htmlspecialchars($_SESSION['pc_build']['motherboard']['supported_ram_type']) ?></p>
                                <p class="text-muted">₹<?= number_format($_SESSION['pc_build']['motherboard']['price'], 2) ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?= $_SESSION['pc_build']['processor'] ? 'Not selected' : 'Select processor first' ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Case Summary -->
                    <div class="mb-3">
                        <h5>Case</h5>
                        <?php if ($_SESSION['pc_build']['case']): ?>
                            <p><?= htmlspecialchars($_SESSION['pc_build']['case']['model']) ?></p>
                            <div class="specs">
                                <p><?= htmlspecialchars($_SESSION['pc_build']['case']['form_factor']) ?></p>
                                <p class="text-muted">₹<?= number_format($_SESSION['pc_build']['case']['price'], 2) ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Not selected</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- RAM Summary -->
                    <div class="mb-3">
                        <h5>RAM</h5>
                        <?php if ($_SESSION['pc_build']['ram']): ?>
                            <p><?= htmlspecialchars($_SESSION['pc_build']['ram']['model']) ?></p>
                            <div class="specs">
                                <p><?= htmlspecialchars($_SESSION['pc_build']['ram']['type']) ?> @ <?= htmlspecialchars($_SESSION['pc_build']['ram']['speed']) ?> MHz</p>
                                <p class="text-muted">₹<?= number_format($_SESSION['pc_build']['ram']['price'], 2) ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?= $_SESSION['pc_build']['motherboard'] ? 'Not selected' : 'Select motherboard first' ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Storage Summary -->
                    <div class="mb-3">
                        <h5>Storage</h5>
                        <?php if ($_SESSION['pc_build']['storage']): ?>
                            <p><?= htmlspecialchars($_SESSION['pc_build']['storage']['model']) ?></p>
                            <div class="specs">
                                <p><?= htmlspecialchars($_SESSION['pc_build']['storage']['interface']) ?></p>
                                <p class="text-muted">₹<?= number_format($_SESSION['pc_build']['storage']['price'], 2) ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Not selected</p>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h4>Total Price</h4>
                        <?php
                        $total = 0;
                        foreach ($_SESSION['pc_build'] as $component) {
                            if ($component) {
                                $total += $component['price'];
                            }
                        }
                        ?>
                        <h3>₹<?= number_format($total, 2) ?></h3>
                    </div>
                    
                    <div class="d-grid gap-2">
    <?php if (count(array_filter($_SESSION['pc_build'])) === 5): ?>
        <!-- Complete Build button that redirects to checkout page -->
        <a href="Build_checkout.php" class="btn btn-success">Complete Build</a>
    <?php endif; ?>
    <a href="?reset=1" class="btn btn-outline-danger">Reset Build</a>
</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Activate tab based on first missing component
        document.addEventListener('DOMContentLoaded', function() {
            const components = ['processor', 'motherboard', 'ram', 'case', 'storage'];
            for (const component of components) {
                if (!document.querySelector(`.nav-link#${component}-tab`).classList.contains('disabled') && 
                    !document.querySelector(`.nav-link#${component}-tab`).innerHTML.includes('fa-check')) {
                    const tab = new bootstrap.Tab(document.querySelector(`#${component}-tab`));
                    tab.show();
                    break;
                }
            }
        });
    </script>
</body>
</html>