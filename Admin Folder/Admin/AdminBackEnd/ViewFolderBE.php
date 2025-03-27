<?php
include '../connection/Connection.php'; // Database connection

// Get folder name safely from POST or GET
$folderName = isset($_POST['folder_name']) ? trim($_POST['folder_name']) : (isset($_GET['folder']) ? trim($_GET['folder']) : '');
$folderName = $folderName !== null ? $folderName : ''; // Ensure it's a string

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
        if (!mkdir($uploadDir, 0777, true)) {
            echo json_encode(["status" => "error", "message" => "Failed to create directory"]);
            exit;
        }
    }

    $filePath = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmp, $filePath)) {
        // Insert file details into database
        $stmt = $conn->prepare("INSERT INTO files (file_name, file_type, folder_name, temperature, water_level, air_quality) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdd", $fileName, $fileType, $folderName, $temperature, $waterLevel, $airQuality);

        if ($stmt->execute()) {
            // Update num_contents count in `media_folders`
            $updateStmt = $conn->prepare("UPDATE media_folders SET num_contents = num_contents + 1 WHERE folder_name = ?");
            $updateStmt->bind_param("s", $folderName);
            $updateStmt->execute();
            $updateStmt->close();

            echo json_encode(["status" => "success", "message" => "File uploaded successfully"]);
        } else {
            // Delete the uploaded file if database insertion fails
            unlink($filePath);
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

// Handle folder operations (Create, Rename, Delete)

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

// Handling Single delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    // Delete a single file
    if ($action === "deleteFile") {
        $fileId = $_POST['file_id'];

        // Get file name and folder name
        $stmt = $conn->prepare("SELECT file_name, folder_name FROM files WHERE id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $stmt->bind_result($fileName, $folderName);
        $stmt->fetch();
        $stmt->close();

        if (!empty($fileName)) {
            $conn->begin_transaction();
            try {
                // Delete from database
                $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
                $stmt->bind_param("i", $fileId);
                $stmt->execute();
                $stmt->close();

                // Update folder count
                $updateStmt = $conn->prepare("UPDATE media_folders SET num_contents = num_contents - 1 WHERE folder_name = ?");
                $updateStmt->bind_param("s", $folderName);
                $updateStmt->execute();
                $updateStmt->close();

                $conn->commit();

                // Delete file from uploads folder
                $filePath = "uploads/$folderName/$fileName";
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                echo json_encode(["status" => "success", "message" => "File deleted successfully"]);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => "Error deleting file: " . $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "File not found"]);
            exit;
        }
    }

    // Handling multiple delete
if ($action === "deleteMultipleFiles") {
    if (!isset($_POST['selected_files']) || empty($_POST['selected_files'])) {
        echo json_encode(["status" => "error", "message" => "No files selected"]);
        exit;
    }

    $selectedFiles = $_POST['selected_files'];
    $folderName = $_POST['folder_name'];
    $deletedCount = 0;

    $conn->begin_transaction();
    try {
        // First get all file names and folder name for filesystem deletion
        $placeholders = implode(',', array_fill(0, count($selectedFiles), '?'));
        $types = str_repeat('i', count($selectedFiles));
        
        $stmt = $conn->prepare("SELECT file_name, folder_name FROM files WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$selectedFiles);
        $stmt->execute();
        $result = $stmt->get_result();
        $filesToDelete = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM files WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$selectedFiles);
        $stmt->execute();
        $deletedCount = $stmt->affected_rows;
        $stmt->close();

        // Update folder count
        $updateStmt = $conn->prepare("UPDATE media_folders SET num_contents = num_contents - ? WHERE folder_name = ?");
        $updateStmt->bind_param("is", $deletedCount, $folderName);
        $updateStmt->execute();
        $updateStmt->close();

        $conn->commit();

        // Delete from filesystem
        foreach ($filesToDelete as $file) {
            $filePath = "uploads/" . $file['folder_name'] . "/" . $file['file_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        echo json_encode(["status" => "success", "message" => "$deletedCount files deleted successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Error deleting files: " . $e->getMessage()]);
    }
    exit;
}

    // Delete a folder and its contents
    elseif ($action === "deleteFolder") {
        $folderName = $_POST['folder_name'];

        // Start transaction
        $conn->begin_transaction();
        try {
            // Get count of files to be deleted
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM files WHERE folder_name = ?");
            $countStmt->bind_param("s", $folderName);
            $countStmt->execute();
            $countStmt->bind_result($fileCount);
            $countStmt->fetch();
            $countStmt->close();

            // Get all files inside the folder
            $stmt = $conn->prepare("SELECT file_name FROM files WHERE folder_name = ?");
            $stmt->bind_param("s", $folderName);
            $stmt->execute();
            $result = $stmt->get_result();
            $files = [];
            while ($row = $result->fetch_assoc()) {
                $files[] = $row['file_name'];
            }
            $stmt->close();

            // Delete all files from database
            $stmt = $conn->prepare("DELETE FROM files WHERE folder_name = ?");
            $stmt->bind_param("s", $folderName);
            $stmt->execute();
            $stmt->close();

            // Delete the folder from the database
            $stmt = $conn->prepare("DELETE FROM media_folders WHERE folder_name = ?");
            $stmt->bind_param("s", $folderName);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            // Delete files from filesystem
            foreach ($files as $file) {
                $filePath = "uploads/$folderName/$file";
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Remove the folder itself
            $folderPath = "uploads/$folderName";
            deleteFolder($folderPath);

            echo json_encode(["status" => "success", "message" => "Folder and its contents deleted successfully"]);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Error deleting folder: " . $e->getMessage()]);
            exit;
        }
    }

}

// Function to delete folder and its contents
function deleteFolder($folderPath) {
    if (!is_dir($folderPath)) return;
    
    $files = array_diff(scandir($folderPath), ['.', '..']);
    foreach ($files as $file) {
        $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
        is_dir($filePath) ? deleteFolder($filePath) : unlink($filePath);
    }
    rmdir($folderPath);
}


$conn->close();
?>