<?php
include '../connection/Connection.php';
session_start();

// Check if current user is Admin
function isAdmin($conn) {
    if (!isset($_SESSION['user_id'])) {
        error_log("No user_id in session");
        return false;
    }
    $userId = $_SESSION['user_id'];
    error_log("Checking Admin status for user ID: $userId");
    
    $result = $conn->query("SELECT role FROM users WHERE id = $userId");
    if ($result && $row = $result->fetch_assoc()) {
        error_log("User role: " . $row['role']);
        return $row['role'] === 'Admin';
    }
    error_log("Failed to fetch user role");
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAdmin = isAdmin($conn);
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_user':
                createUser($conn, $isAdmin);
                break;
            case 'update_user':
                updateUser($conn, $isAdmin);
                break;
            case 'update_user_partial':
                updateUserPartial($conn, $isAdmin);
                break;
            case 'delete_user':
                deleteUser($conn, $isAdmin);
                break;
            case 'verify_pin':
                verifyPinCode($conn);
                break;
        }
    }
    exit;
}

function verifyPinCode($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        return;
    }

    if (empty($_POST['pin_code'])) {
        echo json_encode(['status' => 'error', 'message' => 'PIN code is required']);
        return;
    }

    $userId = $_SESSION['user_id'];
    $pinCode = $conn->real_escape_string($_POST['pin_code']);

    $stmt = $conn->prepare("SELECT pin_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user['pin_code'] === $pinCode) {
        echo json_encode(['status' => 'success', 'message' => 'PIN verified']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect PIN code']);
    }
}

// Get all users for display
if (isset($_GET['get_users'])) {
    echo json_encode(getUsers($conn));
    exit;
}

function getUsers($conn) {
    $isAdmin = isAdmin($conn);
    $currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $currentUserRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Build base query with role visibility rules
    $baseQuery = "SELECT id, employee_no, CONCAT(first_name, ' ', last_name) AS name, 
                 position, role, email, password, pin_code FROM users";
    
    // Add WHERE clause based on user role
    $whereClause = '';
    if (!$isAdmin) {
        // For regular users, only show their own account
        $whereClause = " WHERE id = $currentUserId";
    } else {
        // For Admins, show all accounts except other Admins (except their own) and Super Admin
        $whereClause = " WHERE (role != 'Admin' OR id = $currentUserId) AND role != 'Super Admin'";
    }
    
    // Add search filter if provided
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $conn->real_escape_string($_GET['search']);
        $whereClause .= $whereClause ? " AND " : " WHERE ";
        $whereClause .= "(employee_no LIKE '%$search%' OR 
                         CONCAT(first_name, ' ', last_name) LIKE '%$search%' OR 
                         position LIKE '%$search%' OR 
                         role LIKE '%$search%' OR 
                         email LIKE '%$search%')";
    }
    
    // Get total count with filters applied
    $countQuery = $conn->query("SELECT COUNT(*) as total FROM users" . $whereClause);
    $totalUsers = $countQuery->fetch_assoc()['total'];
    $totalPages = ceil($totalUsers / $limit);
    
    // Get paginated users with filters applied
    $sql = $baseQuery . $whereClause . " ORDER BY 
           CASE 
               WHEN role = 'Admin' THEN 1 
               ELSE 2 
           END, name ASC 
           LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    
    $users = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Only show N/A if pin_code is NULL or empty string
             $row['password'] = $row['password'] ? $row['password'] : 'N/A';
             $row['pin_code'] = isset($row['pin_code']) && $row['pin_code'] !== '' ? $row['pin_code'] : 'N/A';
            $users[] = $row;
        }
    }
    
    return [
        'users' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_users' => $totalUsers,
            'limit' => $limit
        ]
    ];
}

function createUser($conn, $isAdmin) {
    $required = ['employee_no', 'first_name', 'last_name', 'position', 'role', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    // Validate and escape role
    $allowed_roles = ['Admin', 'User'];
    $role = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : '';
    if (!in_array($role, $allowed_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role specified']);
        return;
    }
    
    // Only Admin can create Admin accounts
    if ($role === 'Admin' && !$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Only Admin can create Admin accounts']);
        return;
    }
    
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
    $password = $conn->real_escape_string($_POST['password']);
    $pin_code = isset($_POST['pin_code']) && $_POST['pin_code'] !== '' ? $conn->real_escape_string($_POST['pin_code']) : null;

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

function updateUser($conn, $isAdmin) {
    if (empty($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        return;
    }

    $id = (int)$_POST['id'];
    
    // Validate and escape role
    $allowed_roles = ['Admin', 'User'];
    $role = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : '';
    if (!in_array($role, $allowed_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role specified']);
        return;
    }
    
    // Only Admin can modify Admin accounts
    if (!$isAdmin) {
        $targetUser = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc();
        if ($targetUser['role'] === 'Admin') {
            echo json_encode(['status' => 'error', 'message' => 'Only Admin can modify Admin accounts']);
            return;
        }
    }
    
    // Only Admin can change role to Admin
    if ($role === 'Admin' && !$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Only Admin can assign Admin role']);
        return;
    }
    
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
    $pin_code = isset($_POST['pin_code']) && $_POST['pin_code'] !== '' ? $conn->real_escape_string($_POST['pin_code']) : null;

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

    // Add password if provided
    if (!empty($_POST['new_password'])) {
        $password = $conn->real_escape_string($_POST['new_password']);
        if ($_POST['current_password'] !== $conn->query("SELECT password FROM users WHERE id = $id")->fetch_assoc()['password']) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            return;
        }
        
        $password = $conn->real_escape_string($_POST['password']);
        $query .= ", password=?";
        $params[] = $password;
        $types .= "s";
    }

    // Add pin code if provided
    if (!empty($_POST['new_pin'])) {
        if (!verifyCurrentPin($conn, $id, $_POST['current_pin'])) {
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

function updateUserPartial($conn, $isAdmin) {
    if (empty($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        return;
    }

    $id = (int)$_POST['id'];
    
    // Get the user's current role
    $userCheck = $conn->query("SELECT role FROM users WHERE id = $id");
    if ($userCheck->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }
    
    $user = $userCheck->fetch_assoc();
    $currentRole = $user['role'];
    
    // If the user is an Admin and we're trying to update designation, prevent it
    if ($currentRole === 'Admin' && $_POST['container_type'] === 'designation') {
        echo json_encode(['status' => 'error', 'message' => 'Cannot update designation for Admin accounts']);
        return;
    }
    
    // Handle updates based on container_type
    switch ($_POST['container_type']) {
        case 'profile':
            updateProfile($conn, $id);
            break;
        case 'designation':
            updateDesignation($conn, $id, $isAdmin);
            break;
        case 'password':
            updatePassword($conn, $id);
            break;
        case 'pincode':
            updatePinCode($conn, $id);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid container type']);
    }
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

function updateDesignation($conn, $id, $isAdmin) {
    $required = ['position', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    $position = $conn->real_escape_string($_POST['position']);
    
    // Validate and escape role
    $allowed_roles = ['Admin', 'User'];
    $role = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : '';
    if (!in_array($role, $allowed_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role specified']);
        return;
    }
    
    // Only Admin can assign Admin role
    if ($role === 'Admin' && !$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Only Admin can assign Admin role']);
        return;
    }
    
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

function updatePassword($conn, $id) {
    $required = ['current_password', 'new_password', 'confirm_password'];

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

    $currentPassword = $_POST['current_password'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if ($currentPassword !== $user['password']) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        return;
    }

    $password = $conn->real_escape_string($_POST['new_password']);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $password, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating password: ' . $conn->error]);
    }
    $stmt->close();
}


function updatePinCode($conn, $id) {
    $required = ['current_pin', 'new_pin', 'confirm_pin'];

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

    $currentPin = $_POST['current_pin'];
    $stmt = $conn->prepare("SELECT pin_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if ($currentPin !== $user['pin_code']) {
        echo json_encode(['status' => 'error', 'message' => 'Current PIN is incorrect']);
        return;
    }

    $pin_code = $conn->real_escape_string($_POST['new_pin']);

    $stmt = $conn->prepare("UPDATE users SET pin_code=? WHERE id=?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        return;
    }

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
    if (!$stmt) return false;

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return false; // User not found
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Use password_verify() if passwords are hashed
    // return password_verify($password, $user['password']);

    return $password === $user['password']; // Plain text comparison (not secure!)
}


function verifyCurrentPin($conn, $id, $pin) {
    $stmt = $conn->prepare("SELECT pin_code FROM users WHERE id = ?");
    if (!$stmt) return false;

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return false; // User not found
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    return $pin === $user['pin_code']; // Plain text comparison
}


function deleteUser($conn, $isAdmin) {
    if (empty($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        return;
    }

    // Require PIN verification for all deletions
    if (empty($_POST['verified_pin'])) {
        echo json_encode(['status' => 'error', 'message' => 'PIN verification is required']);
        return;
    }

    $id = (int)$_POST['id'];
    $pinCode = $conn->real_escape_string($_POST['verified_pin']);
    
    // Verify the PIN code against the current user's PIN
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        return;
    }
    
    $currentUserId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT pin_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Current user not found']);
        return;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user['pin_code'] !== $pinCode) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid PIN code']);
        return;
    }
    
    if (!$isAdmin) {
        $targetUser = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc();
        if ($targetUser['role'] === 'Admin') {
            echo json_encode(['status' => 'error', 'message' => 'Only Admin can delete Admin accounts']);
            return;
        }
    }
    
    // Prevent deleting own account
    if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account']);
        return;
    }

    // Check if this is the last Admin
    $targetUser = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc();
    if ($targetUser['role'] === 'Admin') {
        $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Admin'")->fetch_assoc()['count'];
        if ($adminCount <= 1) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete the last Admin account']);
            return;
        }
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting user: ' . $conn->error]);
    }
    $stmt->close();
}
?>