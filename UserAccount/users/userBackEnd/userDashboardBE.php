<?php
include '../connection/Connection.php'; // Database connection
// Start session first
session_start();

// Check authentication and admin role
// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Super Admin' && $_SESSION['role'] !== 'User')) {
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
?>