<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/login.css">

    <title>San Juan CDRRMO | Login</title>

</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST" class="login-form">
            <h2>Login</h2>
            <p>CEDOC FIVERES</p>
            <div class="input-container">
                <i class="fa fa-user"></i>
                <input type="text" name="Employee No." placeholder="Employee No." required>
            </div>
            <div class="input-container">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="button-container">
                <button class="login-button" type="submit">Login</button>
            </div>
        </form>
    </div>

    <script src="./js/login.js"></script>
</body>
</html>
