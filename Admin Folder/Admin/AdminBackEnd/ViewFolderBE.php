<?php
include '../connection/Connection.php'; // Database connection

// Get folder name safely from POST or GET
$folderName = isset($_POST['folder_name']) ? trim($_POST['folder_name']) : (isset($_GET['folder']) ? trim($_GET['folder']) : '');
$folderName = $folderName !== null ? $folderName : ''; // Ensure it's a string

if (empty($folderName)) {
    die(json_encode(["status" => "error", "message" => "No folder selected."]));
}

$uploadDir = "uploads/" . $folderName . "/"; // Define upload path

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['file'])) {
    $temperature = isset($_POST['temperature']) && $_POST['temperature'] !== "" ? $_POST['temperature'] : null;
    $waterLevel = isset($_POST['waterLevel']) && $_POST['waterLevel'] !== "" ? floatval($_POST['waterLevel']) : null;
    $airQuality = isset($_POST['airQuality']) && $_POST['airQuality'] !== "" ? floatval($_POST['airQuality']) : null;

    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];

    // Ensure directory exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmp, $filePath)) {
        // Insert file details into database
        $stmt = $conn->prepare("INSERT INTO files (file_name, file_type, folder_name, temperature, water_level, air_quality) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdd", $fileName, $fileType, $folderName, $temperature, $waterLevel, $airQuality);

        if ($stmt->execute()) {
            // Update num_contents count in `media_folders`
            $updateStmt = $conn->prepare("UPDATE media_folders SET num_contents = (SELECT COUNT(*) FROM files WHERE folder_name = ?) WHERE folder_name = ?");
            $updateStmt->bind_param("ss", $folderName, $folderName);
            $updateStmt->execute();
            $updateStmt->close();

            echo json_encode(["status" => "success", "message" => "File uploaded successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database insertion failed"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed"]);
    }
    $conn->close();
    exit;
}

// Fetch files for the selected folder
$stmt = $conn->prepare("SELECT * FROM files WHERE folder_name = ? ORDER BY date_modified DESC");
$stmt->bind_param("s", $folderName);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();


// Handle folder operations (Create, Rename, Delete)

// Include database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "cedoc_fiveres";

$conn = new mysqli($host, $user, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Edit File Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editFile'])) {
    $id = intval($_POST['file_id']);
    $file_name = $_POST['file_name'];
    $temperature = !empty($_POST['temperature']) ? $_POST['temperature'] : NULL;
    $water_level = !empty($_POST['water_level']) ? floatval($_POST['water_level']) : NULL;
    $air_quality = !empty($_POST['air_quality']) ? floatval($_POST['air_quality']) : NULL;

    // ✅ Prepare SQL Statement
    $stmt = $conn->prepare("UPDATE files SET file_name = ?, temperature = ?, water_level = ?, air_quality = ?, date_modified = NOW() WHERE id = ?");
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssddi", $file_name, $temperature, $water_level, $air_quality, $id);

    if ($stmt->execute()) {
        // ✅ Stay on the same page by refreshing
        echo "<meta http-equiv='refresh' content='0'>";
        exit();
    }

    $stmt->close();
}

// ✅ Delete File Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteFile'])) {
    $id = intval($_POST['file_id']);

    $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // ✅ Refresh page to update the file list
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $stmt->close();
}

// ✅ Close connection
$conn->close();
?>

