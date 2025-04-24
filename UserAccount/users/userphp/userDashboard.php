<?php
  include '../connection/Connection.php';
  include '../userBackEnd/userDashboardBE.php';

// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Super Admin' && $_SESSION['role'] !== 'User')) {
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
    <title>Admin Dashboard | CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/usersDashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>

    </style>
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

    <aside class="sidebar">
            <ul>
                <li class="dashboard">
                    <a href="userDashboard.php"><img src="../../Assets/Icon/Analysis.png" alt="Dashboard Icon" class="sidebar-icon">Dashboard</a>
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
               
            </ul>
        </aside>
        <div class="main-content">
            <h1 class="main-title">Dashboard</h1>
    
    <div class="dashboard-filter">
        <select id="timeFilter" class="filter-select" onchange="applyFilter(this.value)">
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Time</option>
            <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
            <option value="week" <?php echo $filter === 'week' ? 'selected' : ''; ?>>This Week</option>
            <option value="month" <?php echo $filter === 'month' ? 'selected' : ''; ?>>This Month</option>
            <option value="year" <?php echo $filter === 'year' ? 'selected' : ''; ?>>This Year</option>
        </select>
    </div>


        <div class="dashboard-container">
            <h2>Intern Applicants</h2>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['applicants']['total']; ?></div>
                    <div>Total Applicants</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['applicants']['pending']; ?></div>
                    <div>Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['applicants']['under_review']; ?></div>
                    <div>Under Review</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['applicants']['accepted']; ?></div>
                    <div>Accepted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['applicants']['rejected']; ?></div>
                    <div>Rejected</div>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="applicantsMonthlyChart"></canvas>
            </div>
        </div>
        
        <!-- Vehicle Runs Dashboard -->
        <div class="dashboard-container">
            <h2>Vehicle Runs</h2>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['vehicle_runs']['total']; ?></div>
                    <div>Total Runs</div>
                </div>
                <?php foreach($stats['vehicle_runs']['by_team'] as $team): ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $team['count']; ?></div>
                    <div>Team <?php echo $team['vehicle_team']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="chart-container">
                <canvas id="runsMonthlyChart"></canvas>
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

<script>
    // Initialize charts
    let applicantsChart, runsChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Applicants Monthly Chart
        const applicantsMonthlyCtx = document.getElementById('applicantsMonthlyChart').getContext('2d');
        applicantsChart = new Chart(applicantsMonthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($stats['applicants']['by_month'], 'month')); ?>,
                datasets: [{
                    label: 'Applicants by Month',
                    data: <?php echo json_encode(array_column($stats['applicants']['by_month'], 'count')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Vehicle Runs Monthly Chart
        const runsMonthlyCtx = document.getElementById('runsMonthlyChart').getContext('2d');
        runsChart = new Chart(runsMonthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($stats['vehicle_runs']['by_month'], 'month')); ?>,
                datasets: [{
                    label: 'Vehicle Runs by Month',
                    data: <?php echo json_encode(array_column($stats['vehicle_runs']['by_month'], 'count')); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.7)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
    
    function applyFilter(filterValue) {
        // Reload page with filter parameter
        window.location.href = `?filter=${filterValue}`;
    }
    </script>

<script src="../../js/UserDashboard.js"></script>
</body>
</html>