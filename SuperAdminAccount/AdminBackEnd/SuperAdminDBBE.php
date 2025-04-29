<?php
include '../connection/Connection.php'; // Database connection
session_start();


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
    $result = $conn->query($query);
    $stats['applicants'] = array_merge($stats['applicants'], $result->fetch_assoc());
    
    // Monthly applicant data
    $query = "SELECT 
                DATE_FORMAT(application_date, '%Y-%m') as month,
                COUNT(*) as count
              FROM applicants
              WHERE $appCondition
              GROUP BY DATE_FORMAT(application_date, '%Y-%m')
              ORDER BY month";
    $result = $conn->query($query);
    $stats['applicants']['by_month'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['applicants']['by_month'][] = $row;
    }
    
    /* VEHICLE RUN STATISTICS */
    // Total runs
    $query = "SELECT COUNT(*) as total FROM vehicle_runs WHERE $runCondition";
    $result = $conn->query($query);
    $stats['vehicle_runs']['total'] = $result->fetch_row()[0];
    
    // Runs by team
    $query = "SELECT vehicle_team, COUNT(*) as count 
              FROM vehicle_runs 
              WHERE $runCondition
              GROUP BY vehicle_team";
    $result = $conn->query($query);
    $stats['vehicle_runs']['by_team'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['vehicle_runs']['by_team'][] = $row;
    }
    
    // Runs by case type
    $query = "SELECT case_type, COUNT(*) as count 
              FROM vehicle_runs 
              WHERE $runCondition
              GROUP BY case_type";
    $result = $conn->query($query);
    $stats['vehicle_runs']['by_case_type'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['vehicle_runs']['by_case_type'][] = $row;
    }
    
    // Monthly run data
    $query = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
              FROM vehicle_runs
              WHERE $runCondition
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY month";
    $result = $conn->query($query);
    $stats['vehicle_runs']['by_month'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['vehicle_runs']['by_month'][] = $row;
    }
    
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
              WHERE DATE(date_uploaded) BETWEEN ? AND ?
              GROUP BY DAYOFWEEK(date_uploaded)
              ORDER BY day_num";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $currentWeekStart, $currentWeekEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
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
    
} catch (Exception $e) {
    error_log("Environmental data error: " . $e->getMessage());
}
?>