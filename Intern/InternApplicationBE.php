<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "cedoc_fiveres"; // Changed to match your database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    header("Location: internApplication.php?error=Database+connection+failed");
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Handles file upload and returns the relative file path
 * @param string $inputName The name attribute of the file input
 * @return string|null Relative file path if successful, null otherwise
 */
function handleFileUpload($inputName) {
    // Check if file was uploaded without errors
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error or no file uploaded for input: " . $inputName);
        return null;
    }

    // Define upload directory (absolute server path)
    $uploadDir = "../CedocIntern/InternApplication/";
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            return null;
        }
    }

    // Validate file size (10MB max)
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($_FILES[$inputName]['size'] > $maxFileSize) {
        error_log("File too large: " . $_FILES[$inputName]['name']);
        return null;
    }

    // Validate file type
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $fileExt = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedExtensions)) {
        error_log("Invalid file type: " . $fileExt);
        return null;
    }

    // Generate unique filename
    $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $_FILES[$inputName]['name']);
    $targetPath = $uploadDir . $safeName;

    // Move uploaded file to target directory
    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
        // Return relative path for database storage
        return 'InternApplication/' . $safeName;
    } else {
        error_log("File move operation failed: " . $_FILES[$inputName]['name']);
        return null;
    }
}

// Example usage (would typically be in your form processing logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Handle file upload
    $resumePath = handleFileUpload('resume');
    
    if ($resumePath) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO applications (full_name, email, resume_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $resumePath);
        
        if ($stmt->execute()) {
            header("Location: success.php");
            exit();
        } else {
            error_log("Database insert failed: " . $stmt->error);
            header("Location: internApplication.php?error=Database+error");
            exit();
        }
    } else {
        header("Location: internApplication.php?error=File+upload+failed");
        exit();
    }
}

// Close connection (optional as PHP closes it automatically)
$conn->close();
?>