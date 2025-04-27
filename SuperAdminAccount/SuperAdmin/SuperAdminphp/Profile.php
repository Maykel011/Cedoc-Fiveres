<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connection/Connection.php';
include __DIR__ . '/../AdminBackEnd/SaAdminProfileBE.php';
session_start();

// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') { 
    // Redirect to login page (not logout!)
    header("Location: ../../../login/login.php");
    exit();
}

// Verify class exists
if (!class_exists('SuperAdminProfile')) {
    die('Error: SuperAdminProfile class not found. Check include paths.');
}

$profile = new SuperAdminProfile($conn);
$userData = $profile->getUserData($_SESSION['user_id']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        
        if ($profile->updateProfile($_SESSION['user_id'], $firstName, $lastName, $email)) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to update profile.";
        }
    } elseif (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = "New passwords don't match!";
        } elseif ($profile->updatePassword($_SESSION['user_id'], $currentPassword, $newPassword)) {
            $_SESSION['success_message'] = "Password updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Current password is incorrect!";
        }
    } elseif (isset($_POST['update_pin'])) {
        $currentPin = $_POST['current_pin'];
        $newPin = $_POST['new_pin'];
        $confirmPin = $_POST['confirm_new_pin'];
        
        if (strlen($newPin) !== 6 || strlen($confirmPin) !== 6) {
            $_SESSION['error_message'] = "PIN must be exactly 6 digits!";
        } elseif ($newPin !== $confirmPin) {
            $_SESSION['error_message'] = "New PINs don't match!";
        } elseif ($profile->updatePinCode($_SESSION['user_id'], $currentPin, $newPin)) {
            $_SESSION['success_message'] = "PIN code updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Current PIN is incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES - Super Admin Profile</title>
    <link rel="stylesheet" href="../../Css/SAprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Modern Modal CSS */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
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
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            width: 400px;
            max-width: 90%;
            text-align: center;
            transform: translateY(-50px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }
        
        .modal-icon {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 1rem;
        }
        
        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .modal-message {
            margin-bottom: 2rem;
            color: #666;
        }
        
        .modal-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        .modal-button:hover {
            background-color: #45a049;
        }
        
        .error .modal-icon {
            color: #f44336;
        }
        
        .error .modal-button {
            background-color: #f44336;
        }
        
        .error .modal-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>

<body>
<!-- Success/Error Modal -->
<div id="messageModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="modal-title" id="modalTitle">Success!</h3>
        <p class="modal-message" id="modalMessage">Your changes have been saved successfully.</p>
        <button class="modal-button" id="modalCloseButton">OK</button>
    </div>
</div>

<header class="header">
    <div class="header-content">
        <div class="left-side">
        <img src="../../Assets/img/logo.png" alt="Logo" class="logo">
        </div>
        <div class="right-side">
            <div class="user" id="userContainer">
            <img src="../../Assets/Icon/users.png" alt="User" class="icon" id="userIcon">
                <span class="admin-text">
                <?php 
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin') {
                    if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
                        echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                    } else {
                        echo 'Super Admin';
                    }
                }
                ?>
                </span>
                <div class="user-dropdown" id="userDropdown">
                <a href="Profile.php"><img src="../../Assets/Icon/updateuser.png" alt="Profile Icon" class="dropdown-icon"> Profile</a>
                <a href="#" id="logoutLink"><img src="../../Assets/Icon/logout.png" alt="Logout Icon" class="dropdown-icon"> Logout</a>
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
            <p>Are you sure you want to logout from your account?</p>
            <div class="logout-modal-buttons">
                <button id="logoutCancel" class="logout-modal-btn logout-modal-cancel">Cancel</button>
                <button id="logoutConfirm" class="logout-modal-btn logout-modal-confirm">Logout</button>
            </div>
        </div>
    </div>

    <aside class="sidebar">
        <ul>
            <li class="dashboard">
                <a href="SuperAdminDashboard.php"><img src="../../Assets/Icon/analysis.png" alt="Dashboard Icon" class="sidebar-icon">Dashboard</a>
            </li>
            <li class="media-files">
                <a href="media-files.php"><img src="../../Assets/Icon/file.png" alt="Media Files Icon" class="sidebar-icon"> Media Files</a>
            </li>
            <li class="resume">
                <a href="resume.php"><img src="../../Assets/Icon/resume.png" alt="Resume Icon" class="sidebar-icon">Intern Application</a>
            </li>
            <li class="vehicle-runs">
                <a href="vehicle-runs.php"><img src="../../Assets/Icon/vruns.png" alt="Vehicle Runs Icon" class="sidebar-icon"> Vehicle Runs</a>
            </li>
            <li class="manage-users">
                <a href="manage-users.php"><img src="../../Assets/Icon/user-management.png" alt="Manage Users Icon" class="sidebar-icon"> Manage Users</a>
            </li>
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

        <!-- Update PIN CODE Container -->
        <div class="delete-pincode-container">
            <h2>Update PIN code</h2>
            <p>Please enter your current 6-digit PIN code before setting a new one.</p>
            <form method="POST" action="profile.php">
                <input type="hidden" name="update_pin" value="1">
                
                <label for="current_pin">Current 6-Digit PIN</label>
                <input type="password" id="current_pin" name="current_pin" placeholder="Enter current PIN" required maxlength="6" pattern="\d{6}" title="Please enter exactly 6 digits">

                <label for="new_pin">New 6-Digit PIN</label>
                <input type="password" id="new_pin" name="new_pin" placeholder="Enter new PIN" required maxlength="6" pattern="\d{6}" title="Please enter exactly 6 digits">

                <label for="confirm_new_pin">Confirm New 6-Digit PIN</label>
                <input type="password" id="confirm_new_pin" name="confirm_new_pin" placeholder="Confirm new PIN" required maxlength="6" pattern="\d{6}" title="Please enter exactly 6 digits">
                
                <button type="submit" class="custom-save-button">Save</button>
            </form>
        </div>
    </div>
</div>

<script src="../../js/SAprofiles.js"></script>
<script>
    // Handle modal display based on PHP messages
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('messageModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalCloseButton = document.getElementById('modalCloseButton');
        
        <?php if (isset($_SESSION['success_message'])): ?>
            modalTitle.textContent = 'Success!';
            modalMessage.textContent = '<?php echo $_SESSION['success_message']; ?>';
            modal.querySelector('.modal-icon').className = 'modal-icon';
            modal.querySelector('.modal-icon').innerHTML = '<i class="fas fa-check-circle"></i>';
            modal.classList.add('active');
            <?php unset($_SESSION['success_message']); ?>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            modalTitle.textContent = 'Error!';
            modalMessage.textContent = '<?php echo $_SESSION['error_message']; ?>';
            modal.querySelector('.modal-icon').className = 'modal-icon';
            modal.querySelector('.modal-icon').innerHTML = '<i class="fas fa-exclamation-circle"></i>';
            modal.querySelector('.modal-content').classList.add('error');
            modal.classList.add('active');
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        modalCloseButton.addEventListener('click', function() {
            modal.classList.remove('active');
        });
        
        // PIN code input validation
        const pinInputs = document.querySelectorAll('input[type="password"][maxlength="6"]');
        pinInputs.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, ''); // Remove non-digit characters
                if (this.value.length > 6) {
                    this.value = this.value.slice(0, 6);
                }
            });
        });
    });
</script>
</body>
</html>