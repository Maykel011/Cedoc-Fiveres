<?php
include '../connection/Connection.php'; // Database connection

// Get folder name from POST or GET, with proper validation
$folderName = isset($_POST['folder_name']) ? trim($_POST['folder_name']) : (isset($_GET['folder']) ? trim($_GET['folder']) : '');

if (empty($folderName)) {
    die(json_encode(["status" => "error", "message" => "No folder selected."]));
}

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $temperature = isset($_POST['temperature']) ? trim($_POST['temperature']) : null;
    $waterLevel = isset($_POST['waterLevel']) ? trim($_POST['waterLevel']) : null;
    $airQuality = isset($_POST['airQuality']) ? trim($_POST['airQuality']) : null;

    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];
    $uploadDir = "uploads/" . $folderName . "/";

    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmp, $filePath)) {
        // Insert file details into the database
        $stmt = $conn->prepare("INSERT INTO files (file_name, file_type, folder_name, temperature, water_level, air_quality) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fileName, $fileType, $folderName, $temperature, $waterLevel, $airQuality);

        if ($stmt->execute()) {
            // Update num_contents in media_folders
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    include '../connection/Connection.php'; // Reopen connection for operations
    $action = $_POST['action'];

    // Handle folder creation
    if ($action === "create") {
        $folderName = trim($_POST['folder_name']);

        if (empty($folderName)) {
            echo json_encode(["status" => "error", "message" => "Folder name is required"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO media_folders (folder_name, num_contents) VALUES (?, 0)");
        $stmt->bind_param("s", $folderName);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Folder created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error creating folder"]);
        }
        $stmt->close();
    }

    // Handle folder renaming
    elseif ($action === "rename") {
        $folderId = $_POST['folder_id'];
        $newName = $_POST['new_name'];

        $stmt = $conn->prepare("UPDATE media_folders SET folder_name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $folderId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Folder renamed successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error renaming folder"]);
        }
        $stmt->close();
    }

    // Handle folder deletion
    elseif ($action === "delete") {
        $folderId = $_POST['folder_id'];

        $stmt = $conn->prepare("DELETE FROM media_folders WHERE id = ?");
        $stmt->bind_param("i", $folderId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Folder deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error deleting folder"]);
        }
        $stmt->close();
    }

    $conn->close();
}

// Handle file deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_file'])) {
    include '../connection/Connection.php'; // Reopen connection for operations

    $fileName = $_POST['delete_file'];
    $folderName = $_POST['folder_name'];

    $stmt = $conn->prepare("DELETE FROM files WHERE file_name = ? AND folder_name = ?");
    $stmt->bind_param("ss", $fileName, $folderName);

    if ($stmt->execute()) {
        // Update num_contents after deleting file
        $updateStmt = $conn->prepare("UPDATE media_folders SET num_contents = (SELECT COUNT(*) FROM files WHERE folder_name = ?) WHERE folder_name = ?");
        $updateStmt->bind_param("ss", $folderName, $folderName);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode(["status" => "success", "message" => "File deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting file"]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>
