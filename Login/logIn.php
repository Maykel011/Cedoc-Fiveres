<?php
session_start();
include './connection/Connection.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'Admin') {
        header("Location: ../Admin Folder/Admin/Adminphp/adminDashboard.php");
    } else {
        header("Location: UserDashboard.php");
    }
    exit();
}

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_no = $conn->real_escape_string($_POST['employee_no']);
    $password = $_POST['password']; // Don't escape password as it will be hashed

    // Query to check user credentials
    $sql = "SELECT * FROM users WHERE employee_no = '$employee_no'";
    $result = $conn->query($sql);

    if($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if(password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['employee_no'] = $user['employee_no'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect based on role
            if($user['role'] == 'Admin') {
                header("Location: ../Admin Folder/Admin/Adminphp/adminDashboard.php");
            } else {
                header("Location: UserDashboard.php");
            }
            exit();
        } else {
            $error = "Invalid employee number or password";
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

    <script src="./js/logins.js"></script>
</body>
</html>