<?php
include '../connection/Connection.php';
include '../AdminBackEnd/SuperAdminDBBE.php';

// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') { // Note: 'Admin' vs 'admin'
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
    <link rel="stylesheet" href="../../Css/SuperAdminDashboards.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

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

    <div class="dashboard-wrapper">
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

            <!-- Environmental Data Dashboard -->
            <div class="environmental-data-container">
                <h2>Environmental Data (This Week)</h2>
                <div class="chart-container">
                    <canvas id="environmentalChart"></canvas>
                </div>
            </div>

            <!-- Intern Applicants Dashboard -->
            <div class="intern-applicant-container">
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
            <div class="vehicle-runs-container">
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

            // Environmental Data Chart
            const environmentalCtx = document.getElementById('environmentalChart').getContext('2d');
            const environmentalChart = new Chart(environmentalCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($environmentalData['labels']); ?>,
                    datasets: [
                        {
                            label: 'Temperature (°C)',
                            data: <?php echo json_encode($environmentalData['datasets']['temperature']); ?>,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderWidth: 2,
                            yAxisID: 'y',
                            tension: 0.1
                        },
                        {
                            label: 'Water Level (m)',
                            data: <?php echo json_encode($environmentalData['datasets']['water_level']); ?>,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderWidth: 2,
                            yAxisID: 'y1',
                            tension: 0.1
                        },
                        {
                            label: 'Air Quality (AQI)',
                            data: <?php echo json_encode($environmentalData['datasets']['air_quality']); ?>,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderWidth: 2,
                            yAxisID: 'y2',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Day of Week'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Temperature (°C)'
                            },
                            grid: {
                                drawOnChartArea: true,
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Water Level (m)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            min: 0,
                            max: 100
                        },
                        y2: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Air Quality (AQI)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            min: 0,
                            max: 100,
                            offset: true
                        }
                    }
                }
            });
        });
        
        function applyFilter(filterValue) {
            window.location.href = `?filter=${filterValue}`;
        }
    </script>
    <script src="../../js/SuperAdminDashboard.js"></script>
</body>
</html>