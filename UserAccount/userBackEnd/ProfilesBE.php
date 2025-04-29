<?php
include '../connection/Connection.php'; // Database connection
session_start();


class ProfileBE {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function updateProfile($userId, $firstName, $lastName, $email) {
        $stmt = $this->conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $userId);
        
        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            
            return true;
        }
        return false;
    }

    public function updatePassword($userId, $currentPassword, $newPassword) {
        // First verify current password
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($currentPassword, $user['password'])) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $userId);
                return $updateStmt->execute();
            }
        }
        return false;
    }
}
?>