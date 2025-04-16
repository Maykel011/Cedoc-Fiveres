<?php
include '../connection/Connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_user':
                createUser($conn);
                break;
            case 'update_user':
                updateUser($conn);
                break;
            case 'update_user_partial':
                updateUserPartial($conn);
                break;
            case 'delete_user':
                deleteUser($conn);
                break;
            case 'get_user_credentials':
                getUserCredentials($conn);
                break;
        }
    }
    exit;
}

// Get all users for display
if (isset($_GET['get_users'])) {
    $showSensitive = isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin';
    echo json_encode(getUsers($conn, $showSensitive));
    exit;
}

function getUsers($conn, $showSensitive = false) {
    $sql = "SELECT id, employee_no, CONCAT(first_name, ' ', last_name) AS name, 
                   position, role, email, ";
    
    if ($showSensitive) {
        $sql .= "password, pin_code ";
    } else {
        $sql .= "CASE WHEN password IS NOT NULL THEN '••••••••' ELSE 'N/A' END AS password, 
                CASE WHEN pin_code IS NOT NULL THEN '••••••' ELSE 'N/A' END AS pin_code ";
    }
    
    $sql .= "FROM users";
    $result = $conn->query($sql);
    
    $users = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

function createUser($conn) {
    $required = ['employee_no', 'first_name', 'last_name', 'position', 'role', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    $role = $conn->real_escape_string($_POST['role']);
    
    // Check admin limit if creating an admin
    if ($role === 'Admin') {
        $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Admin'")->fetch_assoc()['count'];
        if ($adminCount >= 5) {
            echo json_encode(['status' => 'error', 'message' => 'Maximum of 5 admin users allowed']);
            return;
        }
    }

    $employee_no = $conn->real_escape_string($_POST['employee_no']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $position = $conn->real_escape_string($_POST['position']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pin_code = isset($_POST['pin_code']) ? $conn->real_escape_string($_POST['pin_code']) : null;

    // Check if employee no or email already exists
    $check = $conn->query("SELECT id FROM users WHERE employee_no = '$employee_no' OR email = '$email'");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Employee number or email already exists']);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO users (employee_no, first_name, last_name, position, role, email, password, pin_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $employee_no, $first_name, $last_name, $position, $role, $email, $password, $pin_code);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error creating user: ' . $conn->error]);
    }
    $stmt->close();
}

function updateUser($conn) {
    if (empty($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        return;
    }

    $id = (int)$_POST['id'];
    $role = $conn->real_escape_string($_POST['role']);
    
    // Check admin limit if changing to admin
    if ($role === 'Admin') {
        // Get current role of the user being updated
        $currentRole = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc()['role'];
        
        // Only check limit if changing from non-admin to admin
        if ($currentRole !== 'Admin') {
            $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Admin'")->fetch_assoc()['count'];
            if ($adminCount >= 5) {
                echo json_encode(['status' => 'error', 'message' => 'Maximum of 5 admin users allowed']);
                return;
            }
        }
    }

    $employee_no = $conn->real_escape_string($_POST['employee_no']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $position = $conn->real_escape_string($_POST['position']);
    $email = $conn->real_escape_string($_POST['email']);
    $pin_code = isset($_POST['pin_code']) ? $conn->real_escape_string($_POST['pin_code']) : null;

    // Check if employee no or email already exists for another user
    $check = $conn->query("SELECT id FROM users WHERE (employee_no = '$employee_no' OR email = '$email') AND id != $id");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Employee number or email already exists for another user']);
        return;
    }

    // Prepare the base query
    $query = "UPDATE users SET employee_no=?, first_name=?, last_name=?, position=?, role=?, email=?, pin_code=?";
    $params = [$employee_no, $first_name, $last_name, $position, $role, $email, $pin_code];
    $types = "sssssss";

    // Check if current user is Super Admin (can change passwords/pins without verification)
    $isSuperAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin';

    // Add password if provided
    if (!empty($_POST['new_password'])) {
        if (!$isSuperAdmin && !verifyCurrentPassword($conn, $id, $_POST['current_password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            return;
        }
        
        $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $query .= ", password=?";
        $params[] = $password;
        $types .= "s";
    }

    // Add pin code if provided
    if (!empty($_POST['new_pin'])) {
        if (!$isSuperAdmin && !verifyCurrentPin($conn, $id, $_POST['current_pin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current PIN is incorrect']);
            return;
        }
        
        $pin_code = $conn->real_escape_string($_POST['new_pin']);
        $query .= ", pin_code=?";
        $params[] = $pin_code;
        $types .= "s";
    }

    $query .= " WHERE id=?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating user: ' . $conn->error]);
    }
    $stmt->close();
}

function updateUserPartial($conn) {
    if (empty($_POST['id']) || empty($_POST['container_type'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $id = (int)$_POST['id'];
    $containerType = $conn->real_escape_string($_POST['container_type']);

    // Verify user exists
    $userCheck = $conn->query("SELECT id FROM users WHERE id = $id");
    if ($userCheck->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }

    // Check if current user is Super Admin (can change passwords/pins without verification)
    $isSuperAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin';

    switch ($containerType) {
        case 'profile':
            updateProfile($conn, $id);
            break;
        case 'designation':
            updateDesignation($conn, $id);
            break;
        case 'password':
            updatePassword($conn, $id, $isSuperAdmin);
            break;
        case 'pincode':
            updatePinCode($conn, $id, $isSuperAdmin);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid container type']);
    }
    
    return;
}

function updateProfile($conn, $id) {
    $required = ['employee_no', 'first_name', 'last_name', 'email'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    $employee_no = $conn->real_escape_string($_POST['employee_no']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);

    // Check if employee no or email already exists for another user
    $check = $conn->query("SELECT id FROM users WHERE (employee_no = '$employee_no' OR email = '$email') AND id != $id");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Employee number or email already exists for another user']);
        return;
    }

    $stmt = $conn->prepare("UPDATE users SET employee_no=?, first_name=?, last_name=?, email=? WHERE id=?");
    $stmt->bind_param("ssssi", $employee_no, $first_name, $last_name, $email, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating profile: ' . $conn->error]);
    }
    $stmt->close();
}

function updateDesignation($conn, $id) {
    $required = ['position', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    $position = $conn->real_escape_string($_POST['position']);
    $role = $conn->real_escape_string($_POST['role']);
    
    // Check admin limit if changing to admin
    if ($role === 'Admin') {
        // Get current role of the user being updated
        $currentRole = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc()['role'];
        
        // Only check limit if changing from non-admin to admin
        if ($currentRole !== 'Admin') {
            $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Admin'")->fetch_assoc()['count'];
            if ($adminCount >= 5) {
                echo json_encode(['status' => 'error', 'message' => 'Maximum of 5 admin users allowed']);
                return;
            }
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET position=?, role=? WHERE id=?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ssi", $position, $role, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Designation updated successfully']);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error updating designation: ' . $e->getMessage()]);
    }
}

function updatePassword($conn, $id, $isSuperAdmin = false) {
    $required = ['new_password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        echo json_encode(['status' => 'error', 'message' => 'New password and confirmation do not match']);
        return;
    }

    if (!$isSuperAdmin) {
        if (empty($_POST['current_password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is required']);
            return;
        }
        
        if (!verifyCurrentPassword($conn, $id, $_POST['current_password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            return;
        }
    }

    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $password, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating password: ' . $conn->error]);
    }
    $stmt->close();
}

function updatePinCode($conn, $id, $isSuperAdmin = false) {
    $required = ['new_pin', 'confirm_pin'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    if ($_POST['new_pin'] !== $_POST['confirm_pin']) {
        echo json_encode(['status' => 'error', 'message' => 'New PIN and confirmation do not match']);
        return;
    }

    if (strlen($_POST['new_pin']) !== 6 || !ctype_digit($_POST['new_pin'])) {
        echo json_encode(['status' => 'error', 'message' => 'PIN code must be exactly 6 digits']);
        return;
    }

    if (!$isSuperAdmin) {
        if (empty($_POST['current_pin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current PIN is required']);
            return;
        }
        
        if (!verifyCurrentPin($conn, $id, $_POST['current_pin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current PIN is incorrect']);
            return;
        }
    }

    $pin_code = $conn->real_escape_string($_POST['new_pin']);
    
    $stmt = $conn->prepare("UPDATE users SET pin_code=? WHERE id=?");
    $stmt->bind_param("si", $pin_code, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'PIN code updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating PIN code: ' . $conn->error]);
    }
    $stmt->close();
}

function verifyCurrentPassword($conn, $id, $password) {
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return password_verify($password, $user['password']);
}

function verifyCurrentPin($conn, $id, $pin) {
    $stmt = $conn->prepare("SELECT pin_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return ($pin === $user['pin_code']);
}

function deleteUser($conn) {
    if (empty($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        return;
    }

    $id = (int)$_POST['id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting user: ' . $conn->error]);
    }
    $stmt->close();
}

function getUserCredentials($conn) {
    if (empty($_GET['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        return;
    }

    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT password, pin_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'status' => 'success',
        'password' => $user['password'],
        'pin_code' => $user['pin_code']
    ]);
}
?>