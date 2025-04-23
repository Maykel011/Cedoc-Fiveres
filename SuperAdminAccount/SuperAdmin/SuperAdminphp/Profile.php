<?php

session_start();

// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Super Admin')) {
    // Redirect to login page (not logout!)
    header("Location: ../../../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES - Update Profile</title>
    <link rel="stylesheet" href="../../Css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        function handleSubmit(event) {
            event.preventDefault();
            alert("Your profile info has been saved");
        }
    </script>
</head>

<body>
<header class="header">
    <div class="header-content">
        <div class="left-side">
            <img src="../../assets/img/Logo.png" alt="Logo" class="logo">
        </div>
        <div class="right-side">
            <div class="user" id="userContainer">
                <img src="../../assets/icon/users.png" alt="User" class="icon" id="userIcon">
                <span class="admin-text">
                    <?php 
                    // Display first and last name if available, otherwise show "Admin"
                    if(isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                        echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                    } else {
                        echo 'Admin';
                    }
                    ?>
                </span>
                <div class="user-dropdown" id="userDropdown">
                    <a href="profile.php"><img src="../../assets/icon/updateuser.png" alt="Profile Icon" class="dropdown-icon"> Profile</a>
                    <a href="#" id="logoutLink"><img src="../../assets/icon/logout.png" alt="Logout Icon" class="dropdown-icon"> Logout</a>
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
            <a href="SuperAdminDashboard.php"><img src="../../Assets/Icon/Analysis.png" alt="Dashboard Icon" class="sidebar-icon">Dashboard</a>
        </li>
        <li class="media-files">
            <a href="media-files.php"><img src="../../Assets/Icon/file.png" alt="Media Files Icon" class="sidebar-icon"> Media Files</a>
        </li>
        <li class="resume">
            <a href="resume.php"><img src="../../Assets/Icon/Resume.png" alt="Resume Icon" class="sidebar-icon">Intern Application</a>
        </li>
        <li class="vehicle-runs">
            <a href="vehicle-runs.php"><img src="../../assets/icon/vruns.png" alt="Vehicle Runs Icon" class="sidebar-icon"> Vehicle Runs</a>
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
        <form onsubmit="handleSubmit(event)">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="User Name" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter Email" required>
            
            <button type="submit" class="custom-save-button">Save</button>
        </form>
    </div>

    <!-- Update Password Container -->
    <div class="update-password-container">
        <h2>Update Password</h2>
        <p>Ensure your account is using a long, random password to stay secure.</p>
        <form onsubmit="handleSubmit(event)">
            <label for="current-password">Current Password</label>
            <input type="password" id="current-password" placeholder="Enter current password" required>
            
            <label for="new-password">New Password</label>
            <input type="password" id="new-password" placeholder="Enter new password" required>
            
            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" placeholder="Confirm password" required>
            
            <button type="submit" class="custom-save-button">Save</button>
        </form>
    </div>

    <!-- Update PIN CODE Container -->
    <div class="delete-pincode-container">
        <h2>Update PIN code</h2>
        <p>Please enter your current 6-digit PIN code before setting a new one.</p>
        <form onsubmit="handleSubmit(event)">
        <label for="confirm-pincode">Current 6-Digit PIN</label>
            <input type="password" id="confirm-pincode" placeholder="Enter current PIN" required maxlength="6">

            <label for="new-pincode">New 6-Digit PIN</label>
            <input type="password" id="new-pincode" placeholder="Enter new PIN" required maxlength="6">

            <label for="confirm-new-pincode">Confirm New 6-Digit PIN</label>
            <input type="password" id="confirm-new-pincode" placeholder="Confirm new PIN" required maxlength="6">

            
            <button type="submit" class="custom-save-button">Save</button>
        </form>
    </div>
</div>
    </div>
<script src="../../js/profiles.js"></script>
</body>
</html>
