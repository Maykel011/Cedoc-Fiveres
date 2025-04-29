<?php
include '../connection/Connection.php'; // Database connection
include '../userBackEnd/ProfilesBE.php';


// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$profileBE = new ProfileBE($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        
        if ($profileBE->updateProfile($_SESSION['user_id'], $firstName, $lastName, $email)) {
            $_SESSION['success_message'] = "Profile updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update profile.";
        }
    } elseif (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword === $confirmPassword) {
            if ($profileBE->updatePassword($_SESSION['user_id'], $currentPassword, $newPassword)) {
                $_SESSION['success_message'] = "Password updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update password. Current password may be incorrect.";
            }
        } else {
            $_SESSION['error_message'] = "New passwords do not match!";
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: profile.php");
    exit();
}

// Get current user data
$userId = $_SESSION['user_id'];
$query = "SELECT first_name, last_name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES - Update Profile</title>
    <link rel="stylesheet" href="../Css/profiles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Modern Modal CSS */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
            transform: translateY(-50px);
            transition: transform 0.3s ease;
            text-align: center;
            overflow: hidden;
        }
        
        .modal-overlay.active .modal-container {
            transform: translateY(0);
        }
        
        .modal-icon {
            font-size: 60px;
            color: #4CAF50;
            margin: 20px 0;
        }
        
        .modal-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .modal-message {
            color: #666;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        
        .modal-button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            width: 100%;
            margin-top: 20px;
        }
        
        .modal-button:hover {
            background: #45a049;
        }
        
        .error .modal-icon {
            color: #f44336;
        }
        
        .error .modal-button {
            background: #f44336;
        }
        
        .error .modal-button:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>
<header class="header">
        <div class="header-content">
            <div class="left-side">
            <img src="../Assets/img/logo.png" alt="Logo" class="logo">
            </div>
            <div class="right-side">
                <div class="user" id="userContainer">
                <img src="../Assets/Icon/users.png" alt="User" class="icon" id="userIcon">
                    <span class="admin-text">
                    <?php 
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'User') {
                        if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
                            echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                        } else {
                            echo 'User';
                        }
                    }
                    ?>
                </span>
                <div class="user-dropdown" id="userDropdown">
                    <a href="Profile.php"><img src="../Assets/Icon/updateuser.png" alt="Profile Icon" class="dropdown-icon"> Profile</a>
                    <a href="#" id="logoutLink"><img src="../Assets/Icon/logout.png" alt="Logout Icon" class="dropdown-icon"> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

<!-- Logout Modal -->
<div id="logoutModal" class="logout-modal">
    <div class="logout-modal-content">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout from your admin account?</p>
        <div class="logout-modal-buttons">
            <button id="logoutCancel" class="logout-modal-btn logout-modal-cancel">Cancel</button>
            <button id="logoutConfirm" class="logout-modal-btn logout-modal-confirm">Logout</button>
        </div>
    </div>
</div>



    <aside class="sidebar">
        <ul>
            <li class="dashboard">
                <a href="userDashboard.php"><img src="../Assets/Icon/analysis.png" alt="Dashboard Icon" class="sidebar-icon">Dashboard</a>
            </li>
            <li class="media-files">
                <a href="media-files.php"><img src="../Assets/Icon/file.png" alt="Media Files Icon" class="sidebar-icon"> Media Files</a>
            </li>
            <li class="resume">
                <a href="resume.php"><img src="../Assets/Icon/resume.png" alt="Resume Icon" class="sidebar-icon">Intern Application</a>
            </li>
            <li class="vehicle-runs">
                <a href="vehicle-runs.php"><img src="../Assets/Icon/vruns.png" alt="Vehicle Runs Icon" class="sidebar-icon"> Vehicle Runs</a>
            </li>q
        </ul>
    </aside>
<div class="main-content">
    <div class="main-container">
        
    <!-- Profile Information Container -->
        <div class="profile-container">
            <h2>Profile Information</h2>
            <p>Update your account's profile information and email address.</p>
            <form method="POST" action="profile.php">
                <input type="hidden" name="update_profile" value="1">
                
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" 
                       value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>" required>
                
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" 
                       value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>" required>
                
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                
                <button type="submit" class="custom-save-button">Save</button>
            </form>
        </div>

        <!-- Update Password Container -->
        <div class="update-password-container">
            <h2>Update Password</h2>
            <p>Ensure your account is using a long, random password to stay secure.</p>
            <form method="POST" action="profile.php">
                <input type="hidden" name="update_password" value="1">
                
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                
                <button type="submit" class="custom-save-button">Save</button>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Modal -->
<div id="statusModal" class="modal-overlay <?php echo isset($_SESSION['error_message']) ? 'error' : ''; ?>">
    <div class="modal-container">
        <div class="modal-icon">
            <?php if (isset($_SESSION['error_message'])): ?>
                <i class="fas fa-times-circle"></i>
            <?php else: ?>
                <i class="fas fa-check-circle"></i>
            <?php endif; ?>
        </div>
        <h3 class="modal-title">
            <?php echo isset($_SESSION['error_message']) ? 'Error!' : 'Success!'; ?>
        </h3>
        <p class="modal-message">
            <?php 
            echo isset($_SESSION['error_message']) 
                ? $_SESSION['error_message'] 
                : ($_SESSION['success_message'] ?? '');
            ?>
        </p>
        <button class="modal-button" id="modalCloseButton">OK</button>
    </div>
</div>

<script src="../js/profiles.js"></script>
<script>
    // Show modal if there's a message
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])): ?>
            const modal = document.getElementById('statusModal');
            modal.classList.add('active');
            
            // Close modal when clicking OK or outside
            document.getElementById('modalCloseButton').addEventListener('click', function() {
                modal.classList.remove('active');
            });
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
            
            // Remove message after showing
            setTimeout(function() {
                modal.classList.remove('active');
            }, 5000);
        <?php 
            unset($_SESSION['success_message']);
            unset($_SESSION['error_message']);
        endif; ?>
    });
</script>
</body>
</html>
















