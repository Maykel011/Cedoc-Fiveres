<?php
include '../connection/Connection.php';

// Only execute API functionality if this is an AJAX call with an action parameter
if (isset($_REQUEST['action'])) {
    handleRequest();
}

/**
 * Main request handler function
 */
function handleRequest() {
    session_start();
    
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
    }

    // Set JSON response header
    header('Content-Type: application/json');

    try {
        // Get action parameter from GET or POST
        $action = $_REQUEST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                handleCreate();
                break;
            case 'read':
                handleRead();
                break;
            case 'get':
                handleGet();
                break;
            case 'update':
                handleUpdate();
                break;
            case 'delete':
                handleDelete();
                break;
            case 'removeImage':
                handleRemoveImage();
                break;
            case 'searchOfficers':
                handleSearchOfficers();
                break;
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage(),
            'error' => $e->getTraceAsString() // Remove in production
        ]);
    }
}

/**
 * Get vehicle runs data for frontend display
 */
function getVehicleRunsData() {
    global $conn;
    
    $query = "SELECT * FROM vehicle_runs ORDER BY dispatch_time DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        error_log("Database error: " . $conn->error);
        return [];
    }
    
    $vehicleRuns = [];
    while ($row = $result->fetch_assoc()) {
        $vehicleRuns[] = $row;
    }
    
    return $vehicleRuns;
}

/**
 * Handle case creation
 */
function handleCreate() {
    global $conn;

    // Validate required fields
    $requiredFields = ['vehicleTeam', 'caseType', 'emergencyResponders', 'location', 'dispatchTime', 'backToBaseTime'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Process file upload
    $caseImagePath = handleFileUpload();

    // Prepare data
    $vehicleTeam = cleanInput($_POST['vehicleTeam']);
    $caseType = cleanInput($_POST['caseType']);
    $transportOfficer = !empty($_POST['transportOfficer']) ? cleanInput($_POST['transportOfficer']) : null;
    $emergencyResponders = cleanInput($_POST['emergencyResponders']);
    $location = cleanInput($_POST['location']);
    $dispatchTime = formatDateTime($_POST['dispatchTime']);
    $backToBaseTime = formatDateTime($_POST['backToBaseTime']);
    $createdBy = $_SESSION['user_id'];

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO vehicle_runs (
        vehicle_team, case_type, transport_officer, emergency_responders, 
        location, dispatch_time, back_to_base_time, case_image, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssi", 
        $vehicleTeam, 
        $caseType, 
        $transportOfficer, 
        $emergencyResponders, 
        $location, 
        $dispatchTime, 
        $backToBaseTime, 
        $caseImagePath, 
        $createdBy
    );

    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Case added successfully']);
    $stmt->close();
}

/**
 * Handle file upload
 */
function handleFileUpload() {
    if (!isset($_FILES['caseImage']) || $_FILES['caseImage']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = '../../assets/uploads/case_images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES['caseImage']['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
    }

    // Generate unique filename
    $fileExt = pathinfo($_FILES['caseImage']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('case_') . '.' . $fileExt;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['caseImage']['tmp_name'], $targetPath)) {
        throw new Exception('Failed to upload image');
    }

    return 'assets/uploads/case_images/' . $fileName;
}

/**
 * Handle case retrieval (list)
 */
function handleRead() {
    global $conn;

    $query = "SELECT * FROM vehicle_runs ORDER BY dispatch_time DESC";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $vehicleRuns = [];
    while ($row = $result->fetch_assoc()) {
        $vehicleRuns[] = $row;
    }

    echo json_encode(['success' => true, 'vehicleRuns' => $vehicleRuns]);
}

/**
 * Handle single case retrieval
 */
function handleGet() {
    global $conn;

    if (empty($_GET['id'])) {
        throw new Exception('Missing case ID');
    }

    $caseId = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM vehicle_runs WHERE id = ?");
    $stmt->bind_param("i", $caseId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Case not found');
    }

    $caseData = $result->fetch_assoc();
    echo json_encode(['success' => true, 'caseData' => $caseData]);
    $stmt->close();
}

/**
 * Handle case update
 */
function handleUpdate() {
    global $conn;

    if (empty($_POST['id'])) {
        throw new Exception('Missing case ID');
    }

    $caseId = (int)$_POST['id'];

    // Validate required fields
    $requiredFields = ['vehicleTeam', 'caseType', 'emergencyResponders', 'location', 'dispatchTime', 'backToBaseTime'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Get existing case data
    $existingData = getCaseById($caseId);

    // Process file upload
    $caseImagePath = $existingData['case_image'];
    if (isset($_FILES['caseImage']) && $_FILES['caseImage']['error'] === UPLOAD_ERR_OK) {
        // Delete old image if exists
        if ($caseImagePath && file_exists('../../' . $caseImagePath)) {
            unlink('../../' . $caseImagePath);
        }
        $caseImagePath = handleFileUpload();
    }

    // Prepare data
    $vehicleTeam = cleanInput($_POST['vehicleTeam']);
    $caseType = cleanInput($_POST['caseType']);
    $transportOfficer = !empty($_POST['transportOfficer']) ? cleanInput($_POST['transportOfficer']) : null;
    $emergencyResponders = cleanInput($_POST['emergencyResponders']);
    $location = cleanInput($_POST['location']);
    $dispatchTime = formatDateTime($_POST['dispatchTime']);
    $backToBaseTime = formatDateTime($_POST['backToBaseTime']);

    // Update database
    $stmt = $conn->prepare("UPDATE vehicle_runs SET
        vehicle_team = ?, case_type = ?, transport_officer = ?, 
        emergency_responders = ?, location = ?, dispatch_time = ?, 
        back_to_base_time = ?, case_image = ? WHERE id = ?");

    $stmt->bind_param(
        "ssssssssi", 
        $vehicleTeam, 
        $caseType, 
        $transportOfficer, 
        $emergencyResponders, 
        $location, 
        $dispatchTime, 
        $backToBaseTime, 
        $caseImagePath, 
        $caseId
    );

    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Case updated successfully']);
    $stmt->close();
}

/**
 * Handle case deletion
 */
function handleDelete() {
    global $conn;

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if PIN code is provided and valid
    if (empty($input['pinCode'])) {
        throw new Exception('PIN code is required for deletion');
    }

    // Get the user's stored PIN code
    $userId = $_SESSION['user_id'];
    $userStmt = $conn->prepare("SELECT pin_code FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $userData = $userResult->fetch_assoc();
    $storedPin = $userData['pin_code'];
    
    // Verify PIN code
    if (empty($storedPin)) {
        throw new Exception('No PIN code set for this user');
    }
    
    if ($input['pinCode'] !== $storedPin) {
        throw new Exception('Invalid PIN code');
    }

    if (empty($input['ids'])) {
        throw new Exception('No cases selected for deletion');
    }

    $ids = array_map('intval', $input['ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    // First get image paths to delete files
    $stmt = $conn->prepare("SELECT case_image FROM vehicle_runs WHERE id IN ($placeholders) AND case_image IS NOT NULL");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['case_image'] && file_exists('../../' . $row['case_image'])) {
            unlink('../../' . $row['case_image']);
        }
    }

    // Delete records
    $stmt = $conn->prepare("DELETE FROM vehicle_runs WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Cases deleted successfully', 
        'deletedCount' => $stmt->affected_rows
    ]);
    $stmt->close();
}

/**
 * Handle image removal
 */
function handleRemoveImage() {
    global $conn;

    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['id'])) {
        throw new Exception('Missing case ID');
    }

    $caseId = (int)$input['id'];
    $existingData = getCaseById($caseId);

    // Delete the image file if exists
    if ($existingData['case_image'] && file_exists('../../' . $existingData['case_image'])) {
        if (!unlink('../../' . $existingData['case_image'])) {
            throw new Exception('Failed to delete image file');
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE vehicle_runs SET case_image = NULL WHERE id = ?");
    $stmt->bind_param("i", $caseId);

    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Image removed successfully']);
    $stmt->close();
}

/**
 * Handle officer search for autocomplete
 */
function handleSearchOfficers() {
    global $conn;

    if (empty($_GET['query'])) {
        echo json_encode(['success' => true, 'officers' => []]);
        return;
    }

    $query = '%' . cleanInput($_GET['query']) . '%';
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS officer_name 
                           FROM users 
                           WHERE (first_name LIKE ? OR last_name LIKE ?) 
                           AND role = 'Officer' 
                           LIMIT 10");
    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $officers = [];
    while ($row = $result->fetch_assoc()) {
        $officers[] = $row['officer_name'];
    }

    echo json_encode(['success' => true, 'officers' => $officers]);
    $stmt->close();
}

/**
 * Helper function to get case by ID
 */
function getCaseById($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM vehicle_runs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Case not found');
    }

    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

/**
 * Helper function to clean input data
 */
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Helper function to format datetime
 */
function formatDateTime($datetime) {
    return date('Y-m-d H:i:s', strtotime($datetime));
}