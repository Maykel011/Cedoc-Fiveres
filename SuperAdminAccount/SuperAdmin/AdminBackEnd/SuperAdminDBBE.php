<?php
include '../connection/Connection.php'; // Database connection
session_start();

// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Super Admin')) {
    // Redirect to login page (not logout!)
    header("Location: ../../../login/login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize statistics arrays with default values
$stats = [
    'applicants' => [
        'total' => 0,
        'pending' => 0,
        'under_review' => 0,
        'accepted' => 0,
        'rejected' => 0,
        'by_month' => []
    ],
    'vehicle_runs' => [
        'total' => 0,
        'by_team' => [],
        'by_case_type' => [],
        'by_month' => []
    ]
];

// Check if filter parameter is set
$filter = $_GET['filter'] ?? 'all';

try {
    // Database connection
    $conn = new PDO("mysql:host=localhost;dbname=cedoc_fiveres", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Determine date conditions based on filter
    $dateConditions = [
        'today' => [
            'applicants' => "DATE(application_date) = CURDATE()",
            'vehicle_runs' => "DATE(created_at) = CURDATE()"
        ],
        'week' => [
            'applicants' => "application_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            'vehicle_runs' => "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        ],
        'month' => [
            'applicants' => "application_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)",
            'vehicle_runs' => "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"
        ],
        'year' => [
            'applicants' => "application_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)",
            'vehicle_runs' => "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)"
        ],
        'all' => [
            'applicants' => "1=1",
            'vehicle_runs' => "1=1"
        ]
    ];
    
    $appCondition = $dateConditions[$filter]['applicants'];
    $runCondition = $dateConditions[$filter]['vehicle_runs'];
    
    /* APPLICANT STATISTICS */
    // Main applicant counts
    $query = "SELECT 
                COUNT(*) as total,
                SUM(status = 'Pending') as pending,
                SUM(status = 'Under Review') as under_review,
                SUM(status = 'Accepted') as accepted,
                SUM(status = 'Rejected') as rejected
              FROM applicants
              WHERE $appCondition";
    $stmt = $conn->query($query);
    $stats['applicants'] = array_merge($stats['applicants'], $stmt->fetch(PDO::FETCH_ASSOC));
    
    // Monthly applicant data
    $query = "SELECT 
                DATE_FORMAT(application_date, '%Y-%m') as month,
                COUNT(*) as count
              FROM applicants
              WHERE $appCondition
              GROUP BY DATE_FORMAT(application_date, '%Y-%m')
              ORDER BY month";
    $stmt = $conn->query($query);
    $stats['applicants']['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    /* VEHICLE RUN STATISTICS */
    // Total runs
    $query = "SELECT COUNT(*) as total FROM vehicle_runs WHERE $runCondition";
    $stmt = $conn->query($query);
    $stats['vehicle_runs']['total'] = $stmt->fetchColumn();
    
    // Runs by team
    $query = "SELECT vehicle_team, COUNT(*) as count 
              FROM vehicle_runs 
              WHERE $runCondition
              GROUP BY vehicle_team";
    $stmt = $conn->query($query);
    $stats['vehicle_runs']['by_team'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Runs by case type
    $query = "SELECT case_type, COUNT(*) as count 
              FROM vehicle_runs 
              WHERE $runCondition
              GROUP BY case_type";
    $stmt = $conn->query($query);
    $stats['vehicle_runs']['by_case_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly run data
    $query = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
              FROM vehicle_runs
              WHERE $runCondition
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY month";
    $stmt = $conn->query($query);
    $stats['vehicle_runs']['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
// Initialize environmental data array with proper structure
$environmentalData = [
    'labels' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
    'datasets' => [
        'temperature' => array_fill(0, 7, 0),
        'water_level' => array_fill(0, 7, 0),
        'air_quality' => array_fill(0, 7, 0)
    ]
];

try {
    // Get the start and end of the current week (Monday to Sunday)
    $currentWeekStart = date('Y-m-d', strtotime('monday this week'));
    $currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));
    
    // Query to get environmental data by day of week
    $query = "SELECT 
                DAYOFWEEK(date_uploaded) as day_num,
                AVG(CAST(temperature AS DECIMAL(10,2))) as avg_temp,
                AVG(water_level) as avg_water,
                AVG(air_quality) as avg_air
              FROM files
              WHERE DATE(date_uploaded) BETWEEN :week_start AND :week_end
              GROUP BY DAYOFWEEK(date_uploaded)
              ORDER BY day_num";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':week_start', $currentWeekStart);
    $stmt->bindParam(':week_end', $currentWeekEnd);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Map database results to our structure
    foreach ($results as $row) {
        // Adjust day index (MySQL returns 1=Sunday, 2=Monday, etc. We want 0=Monday)
        $day_index = ($row['day_num'] - 2 + 7) % 7;
        
        if ($day_index >= 0 && $day_index < 7) {
            $environmentalData['datasets']['temperature'][$day_index] = (float)$row['avg_temp'];
            $environmentalData['datasets']['water_level'][$day_index] = (float)$row['avg_water'];
            $environmentalData['datasets']['air_quality'][$day_index] = (float)$row['avg_air'];
        }
    }
    
} catch (PDOException $e) {
    error_log("Environmental data error: " . $e->getMessage());
}
?>