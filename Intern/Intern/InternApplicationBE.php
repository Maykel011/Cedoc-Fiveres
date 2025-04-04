<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "cedoc_fiveres"; // Changed to match your database name

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form inputs
    $full_name         = $_POST["full_name"] ?? '';
    $program_course    = $_POST["program_course"] ?? '';
    $school_university = $_POST["school_university"] ?? '';
    $other_school      = ($school_university === "Others") ? ($_POST["other_school"] ?? null) : null;
    $address           = $_POST["address"] ?? '';
    $contact_number    = $_POST["contact_number"] ?? '';
    $email             = $_POST["email"] ?? '';
    $ojt_hours         = $_POST["ojt_hours"] ?? '';
    $roles             = $_POST["roles"] ?? '';
    $q1                = $_POST["q1"] ?? '';
    $q2                = $_POST["q2"] ?? '';
    $q3                = $_POST["q3"] ?? '';
    $q4                = $_POST["q4"] ?? '';
    $q5                = $_POST["q5"] ?? '';

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM applicants WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        echo "<script>alert('This email has already submitted an application.'); window.location.href = 'internApplication.php';</script>";
        $checkEmail->close();
        exit();
    }
    $checkEmail->close();

    // Handle file uploads
    $resume_path = handleFileUpload('resume');
    $moa_path    = handleFileUpload('moa');
    $recom_path  = handleFileUpload('recom');

    // Validate required files
    if (!$resume_path) {
        echo "<script>alert('Resume is required.'); window.location.href = 'internApplication.php';</script>";
        exit();
    }

    // Store applicant data
    $stmt = $conn->prepare("INSERT INTO applicants 
        (full_name, program_course, school_university, other_school, address, 
         contact_number, email, ojt_hours, roles, resume_path, moa_path, recom_path,
         q1, q2, q3, q4, q5) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssssssssss", 
        $full_name, $program_course, $school_university, $other_school, $address, 
        $contact_number, $email, $ojt_hours, $roles, $resume_path, $moa_path, $recom_path,
        $q1, $q2, $q3, $q4, $q5);

    if ($stmt->execute()) {
        echo "<script>alert('Application submitted successfully!'); window.location.href = 'internHome.php';</script>";
    } else {
        echo "<script>alert('Error: " . addslashes($stmt->error) . "'); window.location.href = 'internApplication.php';</script>";
    }

    $stmt->close();
    $conn->close();
}

// Handles file upload and returns filename path
function handleFileUpload($inputName) {
    if (!empty($_FILES[$inputName]["name"]) && $_FILES[$inputName]["error"] == UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Validate file size (10MB max)
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        if ($_FILES[$inputName]["size"] > $maxFileSize) {
            return null;
        }

        // Validate file type
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = mime_content_type($_FILES[$inputName]["tmp_name"]);
        if (!in_array($fileType, $allowedTypes)) {
            return null;
        }

        $fileName = basename($_FILES[$inputName]["name"]);
        $safeName = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $fileName);
        $targetPath = $uploadDir . $safeName;

        if (move_uploaded_file($_FILES[$inputName]["tmp_name"], $targetPath)) {
            return $targetPath;
        }
    }
    return null;
}
?>