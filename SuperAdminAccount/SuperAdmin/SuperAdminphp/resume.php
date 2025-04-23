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
        <title>CEDOC FIVERES</title>
        <link rel="stylesheet" href="../../Css/resumes.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <a href="resume.php"><img src="../../Assets/Icon/Resume.png" alt="Resume Icon" class="sidebar-icon"> Intern Application</a>
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
            <div class="table-container">
                <h1 class="main-title">Intern Application</h1>
                <br>

                <table>
                    <thead>
                        <tr>
                        <th>NAME</th>
                        <th>NAME</th>
                        <th>NAME</th>
                        <th>NAME</th>
                        <th>NAME</th>
                        <th>NAME</th>
                        <th>NAME</th>
                        <th>NAME</th>
                        <th>NAME</th>

                        </tr>
                    </thead>
                    <tbody id="resume">
                        <!-- Users will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

    <script src="../../js/resumeAdmin.js"></script>
    </body>
    </html>