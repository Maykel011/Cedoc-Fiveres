<?php
include '../connection/Connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get':
        getVehicleRun();
        break;
    case 'create':
        createVehicleRun();
        break;
    case 'update':
        updateVehicleRun();
        break;
    case 'delete':
        deleteVehicleRun();
        break;
    case 'searchOfficers':
        searchOfficers();
        break;
    case 'removeImage':
        removeCaseImage();
        break;
    case 'setBTBTime':
        setBTBTime();
        break;
    default:
        getVehicleRunsData();
        break;
}

function getVehicleRunsData() {
    global $conn;
    
    $query = "SELECT * FROM vehicle_runs ORDER BY dispatch_time DESC";
    $result = $conn->query($query);
    
    $vehicleRuns = [];
    while ($row = $result->fetch_assoc()) {
        $vehicleRuns[] = $row;
    }
    
    return $vehicleRuns;
}

function getVehicleRun() {
    global $conn;
    
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }
    
    $query = "SELECT * FROM vehicle_runs WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'caseData' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Case not found']);
    }
}

function createVehicleRun() {
    global $conn;

    // Handle file upload
    $caseImagePath = '';
    if (!empty($_FILES['caseImage']['name'])) {
        // Change this line:
        $uploadDir = '../../VehicleCaseUploads/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['caseImage']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['caseImage']['tmp_name'], $targetPath)) {
            // Change this line to match the relative path you want to store in DB
            $caseImagePath = 'VehicleCaseUploads/' . $fileName;
        }
    }

    // Get form data
    $vehicleTeam = $_POST['vehicleTeam'] ?? '';
    $caseType = $_POST['caseType'] ?? '';
    $transportOfficer = $_POST['transportOfficer'] ?? '';
    $emergencyResponders = $_POST['emergencyResponders'] ?? '';
    $location = $_POST['location'] ?? '';
    $dispatchTime = $_POST['dispatchTime'] ?? '';
    $createdBy = $_SESSION['user_id']; // ✅ Add this line

    $dispatchTime = date('Y-m-d H:i:s', strtotime($dispatchTime));

    // ✅ Updated query and parameters
    $query = "INSERT INTO vehicle_runs (vehicle_team, case_type, transport_officer, emergency_responders, location, dispatch_time, case_image, created_by) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssi", $vehicleTeam, $caseType, $transportOfficer, $emergencyResponders, $location, $dispatchTime, $caseImagePath, $createdBy);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Case created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create case']);
    }
}


function updateVehicleRun() {
    global $conn;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }
    
    // Get existing case data
    $query = "SELECT case_image FROM vehicle_runs WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingData = $result->fetch_assoc();
    
    // Handle file upload
    $caseImagePath = $existingData['case_image'] ?? '';
    if (!empty($_FILES['caseImage']['name'])) {
        // Delete old image if exists
        if (!empty($caseImagePath)) {
            $oldImagePath = '../../../' . $caseImagePath;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        // Change this line:
        $uploadDir = '../../VehicleCaseUploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($_FILES['caseImage']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['caseImage']['tmp_name'], $targetPath)) {
            // Change this line to match the relative path you want to store in DB
            $caseImagePath = 'VehicleCaseUploads/' . $fileName;
        }
    }
    
    // Get other form data
    $vehicleTeam = $_POST['vehicleTeam'] ?? '';
    $caseType = $_POST['caseType'] ?? '';
    $transportOfficer = $_POST['transportOfficer'] ?? '';
    $emergencyResponders = $_POST['emergencyResponders'] ?? '';
    $location = $_POST['location'] ?? '';
    $dispatchTime = $_POST['dispatchTime'] ?? '';
    $backToBaseTime = $_POST['backToBaseTime'] ?? '';
    
    // Convert datetime format for MySQL
    $dispatchTime = date('Y-m-d H:i:s', strtotime($dispatchTime));
    $backToBaseTime = date('Y-m-d H:i:s', strtotime($backToBaseTime));
    
    $query = "UPDATE vehicle_runs SET 
                vehicle_team = ?, 
                case_type = ?, 
                transport_officer = ?, 
                emergency_responders = ?, 
                location = ?, 
                dispatch_time = ?, 
                back_to_base_time = ?, 
                case_image = ? 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssi", $vehicleTeam, $caseType, $transportOfficer, $emergencyResponders, $location, $dispatchTime, $backToBaseTime, $caseImagePath, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Case updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update case']);
    }
}

function deleteVehicleRun() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $ids = $data['ids'] ?? [];
    $pinCode = $data['pinCode'] ?? '';
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'No cases selected']);
        return;
    }
    
    // Verify PIN code (you should implement your own PIN verification logic)
    if (!verifyPinCode($pinCode)) {
        echo json_encode(['success' => false, 'message' => 'Invalid PIN code']);
        return;
    }
    
    // Convert single ID to array for consistency
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    
    // Prepare placeholders for the query
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    
    // First get image paths to delete files
    $query = "SELECT case_image FROM vehicle_runs WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $imagesToDelete = [];
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['case_image'])) {
            $imagesToDelete[] = '../../../' . $row['case_image'];
        }
    }
    
    // Delete the records
    $query = "DELETE FROM vehicle_runs WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$ids);
    
    if ($stmt->execute()) {
        // Delete associated image files
        foreach ($imagesToDelete as $imagePath) {
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        echo json_encode(['success' => true, 'deletedCount' => $stmt->affected_rows]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete cases']);
    }
}

function searchOfficers() {
    global $conn;
    
    $query = $_GET['query'] ?? '';
    if (strlen($query) < 2) {
        echo json_encode(['success' => false, 'message' => 'Query too short']);
        return;
    }
    
    $searchTerm = "%$query%";
    $stmt = $conn->prepare("SELECT DISTINCT transport_officer FROM vehicle_runs WHERE transport_officer LIKE ? LIMIT 10");
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $officers = [];
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['transport_officer'])) {
            $officers[] = $row['transport_officer'];
        }
    }
    
    echo json_encode(['success' => true, 'officers' => $officers]);
}

function removeCaseImage() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }
    
    // Get current image path
    $query = "SELECT case_image FROM vehicle_runs WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row && !empty($row['case_image'])) {
        $imagePath = '../../../' . $row['case_image'];
        
        // Delete the file
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        
        // Update database
        $query = "UPDATE vehicle_runs SET case_image = NULL WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No image found']);
    }
}

function setBTBTime() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $backToBaseTime = $data['backToBaseTime'] ?? null;
    
    if ($id && $backToBaseTime) {
        $query = "UPDATE vehicle_runs SET back_to_base_time = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $backToBaseTime, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    }
}

function verifyPinCode($pinCode) {
    // Implement your PIN verification logic here
    // This is just a placeholder - you should replace with your actual verification
    return strlen($pinCode) === 6 && is_numeric($pinCode);
}