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