<?php
session_start();
include './connection/Connection.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'Super Admin') {
        header("Location: ../SuperAdminAccount/SuperAdmin/SuperAdminphp/SuperAdminDashboard.php");
    } elseif($_SESSION['role'] == 'Admin') {
        header("Location: ../AdminAccount/Admin/Adminphp/adminDashboard.php");
    } else {
        header("Location: ../UserAccount/Users/Userphp/UserDashboard.php");
    }
    exit();
}

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_no = $conn->real_escape_string($_POST['employee_no']);
    $password = $_POST['password'];

    // Query to check user credentials
    $sql = "SELECT * FROM users WHERE employee_no = '$employee_no'";
    $result = $conn->query($sql);

    if($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Check if account is locked
        if($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining_time = strtotime($user['locked_until']) - time();
            $minutes = ceil($remaining_time / 60);
            
            if($remaining_time > 3600) {
                $hours = floor($remaining_time / 3600);
                $minutes = ceil(($remaining_time % 3600) / 60);
                $error = "Account locked. Please try again in $hours hours and $minutes minutes.";
            } else {
                $error = "Account locked. Please try again in $minutes minutes.";
            }
        } else {
            // Verify password (plain text only)
            if($password === $user['password']) {
                // Reset failed attempts on successful login
                $resetSql = "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = " . $user['id'];
                $conn->query($resetSql);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['employee_no'] = $user['employee_no'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['position'] = $user['position'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect based on role
                if($user['role'] == 'Super Admin') {
                    header("Location: ../SuperAdminAccount/SuperAdmin/SuperAdminphp/SuperAdminDashboard.php");
                } elseif($user['role'] == 'Admin') {
                    header("Location: ../AdminAccount/Admin/Adminphp/adminDashboard.php");
                } else {
                    header("Location: ../UserAccount/Users/Userphp/UserDashboard.php");
                }
                exit();
            } else {
                // Increment failed attempts
                $new_attempts = $user['failed_attempts'] + 1;
                $lock_time = null;
                
                // Determine lockout duration based on failed attempts
                if($new_attempts >= 6) {
                    $lock_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                } elseif($new_attempts == 5) {
                    $lock_time = date('Y-m-d H:i:s', strtotime('+20 minutes'));
                } elseif($new_attempts == 4) {
                    $lock_time = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                }
                
                // Update failed attempts and lock time
                $updateSql = "UPDATE users SET failed_attempts = $new_attempts";
                if($lock_time) {
                    $updateSql .= ", locked_until = '$lock_time'";
                }
                $updateSql .= " WHERE id = " . $user['id'];
                $conn->query($updateSql);
                
                // Store warning message
                if($new_attempts >= 3) {
                    $warning = "Warning: $new_attempts attempts. ";
                    if($new_attempts == 3) {
                        $warning .= "Next failed attempt will result in a 10-minute lockout.";
                    } elseif($new_attempts == 4) {
                        $warning .= "Next attempt will result in a 20-minute lockout.";
                    } elseif($new_attempts == 5) {
                        $warning .= "Next attempt will lock your account for 30 minutes.";
                    }
                    $_SESSION['login_warning'] = $warning;
                }
                
                $error = "Invalid employee number or password";
            }
        }
    } else {
        $error = "Invalid employee number or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/logins.css">
    <title>San Juan CDRRMO | Login</title>
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST" class="login-form">
            <h2>Login</h2>
            <p>CEDOC FIVERES</p>
            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['login_warning'])): ?>
                <div class="warning-message">
                    <?php 
                    echo $_SESSION['login_warning'];
                    unset($_SESSION['login_warning']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="input-container">
                <i class="fa fa-user"></i>
                <input type="text" name="employee_no" placeholder="Employee No." required>
            </div>
            <div class="input-container">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="button-container">
                <button class="login-button" type="submit">Login</button>
            </div>
            <div class="forgot-password">
                <a href="forgotpassword.php">Forgot Password?</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('.login-form');
            
            if(loginForm) {
                loginForm.addEventListener('submit', function(event) {
                    const loginBtn = this.querySelector('.login-button');
                    const employeeNo = this.querySelector('input[name="employee_no"]').value;
                    const password = this.querySelector('input[name="password"]').value;
                    
                    // Simple client-side validation
                    if(!employeeNo || !password) {
                        event.preventDefault();
                        return;
                    }
                    
                    // Disable button during submission
                    loginBtn.disabled = true;
                    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                });
            }
        });
    </script>
    
<script src="./js/logins.js"></script>
</body>
</html>