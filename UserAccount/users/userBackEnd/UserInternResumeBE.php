<?php
include '../connection/Connection.php';

class InternResume {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getApplicants() {
        $query = "SELECT * FROM applicants ORDER BY application_date DESC";
        $result = $this->conn->query($query);
        
        $applicants = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $applicants[] = $row;
            }
        }
        return $applicants;
    }

    public function getApplicantData($id) {
        $query = "SELECT * FROM applicants WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function deleteApplicant($id) {
        $query = "DELETE FROM applicants WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function updateStatus($id, $status, $notes) {
        $query = "UPDATE applicants SET status = ?, notes = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $status, $notes, $id);
        return $stmt->execute();
    }
}

$internResume = new InternResume($conn);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getApplicantData') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_GET['id'])) {
        $applicant = $internResume->getApplicantData($_GET['id']);
        if ($applicant) {
            $response['success'] = true;
            $response['applicant'] = $applicant;
        } else {
            $response['message'] = 'Applicant not found';
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($_GET['action']) {
            case 'delete':
                if (isset($_POST['id'])) {
                    $success = $internResume->deleteApplicant($_POST['id']);
                    $response['success'] = $success;
                    $response['message'] = $success ? 'Applicant deleted successfully' : 'Failed to delete applicant';
                }
                break;
                
            case 'updateStatus':
                if (isset($_POST['id'], $_POST['status'], $_POST['notes'])) {
                    $success = $internResume->updateStatus($_POST['id'], $_POST['status'], $_POST['notes']);
                    $response['success'] = $success;
                    $response['message'] = $success ? 'Status updated successfully' : 'Failed to update status';
                }
                break;
                
            default:
                $response['message'] = 'Invalid action';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>