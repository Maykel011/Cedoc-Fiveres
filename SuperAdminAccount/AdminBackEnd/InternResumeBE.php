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
        // First get the current status and applicant data
        $applicantData = $this->getApplicantData($id);
        $oldStatus = $applicantData['status'];
        $email = $applicantData['email'];
        $name = $applicantData['full_name'];

        // Update the status in database
        $query = "UPDATE applicants SET status = ?, notes = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $status, $notes, $id);
        $result = $stmt->execute();
        
        // If status changed, send email
        if ($result && $oldStatus !== $status) {
            $this->sendStatusEmail($email, $name, $oldStatus, $status);
        }
        
        return $result;
    }

    private function sendStatusEmail($to, $name, $oldStatus, $newStatus) {
        $subject = 'Your Application Status Update - CEDOC Fiveres';
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                    border: 1px solid #e0e0e0;
                    border-radius: 10px;
                    background-color: #f9f9f9;
                }
                .email-header {
                    text-align: center;
                    padding: 20px 0;
                    background-color: #0066cc;
                    color: white;
                    border-radius: 8px 8px 0 0;
                }
                .email-logo {
                    max-height: 60px;
                    margin-bottom: 15px;
                }
                .email-content {
                    padding: 20px;
                    background-color: white;
                    border-radius: 0 0 8px 8px;
                }
                .status-change {
                    background-color: #f0f0f0;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 15px 0;
                }
                .old-status { color: #888; }
                .new-status { color: #0066cc; font-weight: bold; }
                .email-footer {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #e0e0e0;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <img src="https://cedoc-fiveres.online/assets/img4.png" alt="CEDOC Fiveres Logo" class="email-logo">
                    <h2>Application Status Update</h2>
                </div>
                <div class="email-content">
                    <p>Hello ' . htmlspecialchars($name) . ',</p>
                    <p>The status of your internship application has been updated:</p>
                    
                    <div class="status-change">
                        <p><span class="old-status">Previous Status: ' . htmlspecialchars($oldStatus) . '</span></p>
                        <p><span class="new-status">New Status: ' . htmlspecialchars($newStatus) . '</span></p>
                    </div>
                    
                    <p>You can log in to your account to view more details about your application.</p>
                    
                    <p>If you have any questions, please don\'t hesitate to contact us.</p>
                </div>
                <div class="email-footer">
                    <p>&copy; ' . date('Y') . ' CEDOC Fiveres. All rights reserved.</p>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $headers = "From: CEDOC Fiveres <cedocfiveres@cedoc-fiveres.online>\r\n";
        $headers .= "Reply-To: cedocfiveres@cedoc-fiveres.online\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($to, $subject, $message, $headers);
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
                    
                    if ($success) {
                        $response['emailSent'] = ($_POST['status'] !== $internResume->getApplicantData($_POST['id'])['status']);
                    }
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