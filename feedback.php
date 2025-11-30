<?php
session_start();
require_once 'Connection.php'; // Your database connection file

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit();
}

$login_id = $_SESSION['login_id'];
$error = '';
$success = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM tbl_user WHERE login_id = ?");
$stmt->bind_param("s", $login_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $error = "User not found!";
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $first_name = trim($user['name']); // Assuming name is stored as full name
    $last_name = ''; // You might need to adjust this based on your user table structure
    $email = trim($user['email']);
    $phone_number = trim($user['phone_no'] ?? '');
    $message = trim($_POST['message']);
    
    // Basic validation
    if (empty($message)) {
        $error = "Feedback message is required!";
    } else {
        // Insert feedback into database
        $stmt = $conn->prepare("INSERT INTO tbl_feedbacks (first_name, last_name, email, phone_number, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone_number, $message);
        
        if ($stmt->execute()) {
            $success = "Thank you for your feedback!";
            // Clear the message field after successful submission
            $_POST['message'] = '';
        } else {
            $error = "Error submitting feedback: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Feedback - BYD Computer Parts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .feedback-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .feedback-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .feedback-header h2 {
            color: #343a40;
            font-weight: 600;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .section-title {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #ffca2c;
            border-color: #ffc720;
        }
        .user-info-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .rating-stars {
            font-size: 24px;
            color: #ffc107;
            margin-bottom: 15px;
        }
        .star {
            cursor: pointer;
            transition: color 0.2s;
        }
        .star:hover {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="user_home.php">
                <i class="fas fa-desktop me-2"></i>BYD
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="mainstyle/img/user_avatar.jpg" alt="User" class="user-avatar me-2">
                            <span><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="user_home.php"><i class="fas fa-home me-2"></i>Home</a></li>
                            <li><a class="dropdown-item" href="user_edit_profile.php"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
                            <li><a class="dropdown-item" href="user_view_orders.php"><i class="fas fa-clipboard-list me-2"></i>Orders</a></li>
                            <li><a class="dropdown-item" href="user_feedback.php"><i class="fas fa-comment me-2"></i>Feedback</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container feedback-container">
        <div class="feedback-header">
            <h2><i class="fas fa-comment me-2"></i>Share Your Feedback</h2>
            <p class="text-muted">We value your opinion and would love to hear from you</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- User Information -->
            <div class="col-md-4">
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-user-circle me-2"></i>Your Information</h4>
                    <div class="user-info-card">
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['name'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['phone_no'] ?? 'Not provided'); ?></span>
                        </div>
                    </div>
                    <p class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Your feedback will be submitted with the information above. 
                        To update your details, please visit the 
                        <a href="user_edit_profile.php">Edit Profile</a> page.
                    </p>
                </div>
            </div>

            <!-- Feedback Form -->
            <div class="col-md-8">
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-edit me-2"></i>Your Feedback</h4>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="8" 
                                      placeholder="Please share your thoughts, suggestions, or any issues you've encountered..." 
                                      required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            <div class="form-text">Your feedback helps us improve our services.</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                            <button type="submit" name="submit_feedback" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> BYD Computer Parts. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        
        // Character counter for feedback message
        const messageTextarea = document.getElementById('message');
        const formText = document.querySelector('.form-text');
        
        if (messageTextarea && formText) {
            // Create character counter element
            const charCounter = document.createElement('div');
            charCounter.className = 'form-text text-end';
            charCounter.id = 'charCounter';
            formText.parentNode.insertBefore(charCounter, formText.nextSibling);
            
            function updateCharCounter() {
                const length = messageTextarea.value.length;
                charCounter.textContent = `${length} characters`;
                
                if (length > 500) {
                    charCounter.classList.add('text-danger');
                } else {
                    charCounter.classList.remove('text-danger');
                }
            }
            
            messageTextarea.addEventListener('input', updateCharCounter);
            updateCharCounter(); // Initialize counter
        }
    </script>
</body>
</html>