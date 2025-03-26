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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    include '../connection/Connection.php'; // Reopen connection for operations
    $action = $_POST['action'];

    // Create a new folder
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

    // Rename a folder (database + filesystem)
    elseif ($action === "rename") {
        $folderId = $_POST['folder_id'];
        $newName = trim($_POST['new_name']);

        // Fetch the existing folder name
        $stmt = $conn->prepare("SELECT folder_name FROM media_folders WHERE id = ?");
        $stmt->bind_param("i", $folderId);
        $stmt->execute();
        $stmt->bind_result($oldName);
        $stmt->fetch();
        $stmt->close();

        if (!empty($oldName)) {
            $oldPath = "uploads/" . $oldName;
            $newPath = "uploads/" . $newName;

            // Rename folder in filesystem
            if (is_dir($oldPath) && !file_exists($newPath)) {
                rename($oldPath, $newPath);
            }

            // Update folder name in database
            $stmt = $conn->prepare("UPDATE media_folders SET folder_name = ? WHERE id = ?");
            $stmt->bind_param("si", $newName, $folderId);
            $stmt->execute();
            $stmt->close();

            // Update files table to reflect new folder name
            $stmt = $conn->prepare("UPDATE files SET folder_name = ? WHERE folder_name = ?");
            $stmt->bind_param("ss", $newName, $oldName);
            $stmt->execute();
            $stmt->close();

            echo json_encode(["status" => "success", "message" => "Folder renamed successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Folder not found"]);
        }
    }

    // Delete a folder (database + filesystem)
    elseif ($action === "delete") {
        $folderId = $_POST['folder_id'];

        // Fetch folder name
        $stmt = $conn->prepare("SELECT folder_name FROM media_folders WHERE id = ?");
        $stmt->bind_param("i", $folderId);
        $stmt->execute();
        $stmt->bind_result($folderName);
        $stmt->fetch();
        $stmt->close();

        if (!empty($folderName)) {
            // Delete all files in the folder first
            $stmt = $conn->prepare("DELETE FROM files WHERE folder_name = ?");
            $stmt->bind_param("s", $folderName);
            $stmt->execute();
            $stmt->close();

            // Delete folder record from database
            $stmt = $conn->prepare("DELETE FROM media_folders WHERE id = ?");
            $stmt->bind_param("i", $folderId);
            $stmt->execute();
            $stmt->close();

            // Delete folder from filesystem
            $folderPath = "uploads/" . $folderName;
            if (is_dir($folderPath)) {
                array_map('unlink', glob("$folderPath/*")); // Remove files first
                rmdir($folderPath); // Remove empty folder
            }

            echo json_encode(["status" => "success", "message" => "Folder deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Folder not found"]);
        }
    }

    $conn->close();
}

// Handle file deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_file'])) {
    include '../connection/Connection.php'; // Reopen connection

    $fileName = $_POST['delete_file'];
    $folderName = $_POST['folder_name'];
    $filePath = "uploads/" . $folderName . "/" . $fileName;

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM files WHERE file_name = ? AND folder_name = ?");
    $stmt->bind_param("ss", $fileName, $folderName);

    if ($stmt->execute()) {
        // Delete file from filesystem
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Update folder content count
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
