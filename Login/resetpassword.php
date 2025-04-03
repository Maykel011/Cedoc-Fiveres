
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/reset.css">
    <title>Reset Password</title>
</head>
<body>
    <div class="login-container">
        <form action="resetpassword.php?token=" method="POST" class="login-form">
            <h2>Reset Password</h2>
            <p>Enter your new password</p>
          <!--  <?php if(isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?> -->
            <div class="input-container">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-container">
                <i class="fa fa-lock"></i>
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="input-container">
                <i class="fa fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <div class="button-container">
                <button class="login-button" type="submit">Reset Password</button>
            </div>
            <div class="forgot-password">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>

    <script src="./js/login.js"></script>
</body>
</html>
