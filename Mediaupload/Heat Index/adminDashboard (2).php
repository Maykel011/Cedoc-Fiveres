
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .main-content {
            margin-left: 250px;
            padding: 1rem;
            margin-top: 120px;
            overflow-y: auto;
            height: calc(100vh - 100px);
            position: fixed;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 1.5rem;
            width: calc(95% - 200px);
            right: 0;
            top: 80px;
        }

        .dashboard-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: 550px;
            min-width: 0;
        }

        .dashboard-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding-bottom: 10px;
        }

        .stat-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            flex: 1;
            min-width: 150px;
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .chart-container {
            flex: 1;
            min-height: 250px;
            min-width: 0;
        }

        .dashboard-filter {
            display: flex;
            padding:20px;
            justify-content: flex-end;
            margin-bottom: 1rem;
            grid-column: 1 / -1;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: white;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3498db;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .dashboard-container {
                height: auto;
                min-height: 500px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="left-side">
                <img src="../../assets/img/Logo.png" alt="Logo" class="logo" style="height: 40px;">
            </div>
            <div class="right-side">
                <div class="user">
                    <span class="admin-text">
                        <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <aside class="sidebar">
        <ul>
            <li><a href="adminDashboard.php">Admin Dashboard</a></li>
            <li><a href="media-files.php">Media Files</a></li>
            <li><a href="resume.php">Intern Application</a></li>
            <li><a href="vehicle-runs.php">Vehicle Runs</a></li>
            <li><a href="manage-users.php">Manage Users</a></li>
        </ul>
    </aside>
        <div class="dashboard-filter">
            <select id="timeFilter" class="filter-select" onchange="applyFilter(this.value)">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Time</option>
                <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $filter === 'week' ? 'selected' : ''; ?>>This Week</option>
                <option value="month" <?php echo $filter === 'month' ? 'selected' : ''; ?>>This Month</option>
                <option value="year" <?php echo $filter === 'year' ? 'selected' : ''; ?>>This Year</option>
            </select>
        </div>
        <center><div><h2>ADMIND DASHBOARD</h2></div></center>
        <div class="main-content">
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
</body>
</html>