<?php
include '../connection/Connection.php';

class SuperAdminProfile {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function updateProfile($userId, $firstName, $lastName, $email) {
        $stmt = $this->conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $userId);
        return $stmt->execute();
    }

    public function updatePassword($userId, $currentPassword, $newPassword) {
        // First verify current password
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        // Update to new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }

    public function updatePinCode($userId, $currentPin, $newPin) {
        // First verify current pin
        $stmt = $this->conn->prepare("SELECT pin_code FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($currentPin !== $user['pin_code']) {
            return false;
        }
        
        // Update to new pin
        $stmt = $this->conn->prepare("UPDATE users SET pin_code = ? WHERE id = ?");
        $stmt->bind_param("si", $newPin, $userId);
        return $stmt->execute();
    }

    public function getUserData($userId) {
        $stmt = $this->conn->prepare("SELECT first_name, last_name, email, pin_code FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>