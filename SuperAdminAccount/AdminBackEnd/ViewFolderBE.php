<?php
include '../connection/Connection.php';

// Verify admin PIN code function
function verifyAdminPin($conn, $pinCode) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'Super Admin' AND pin_code = ?");
    $stmt->bind_param("s", $pinCode);
    $stmt->execute();
    $stmt->store_result();
    $isValid = $stmt->num_rows > 0;
    $stmt->close();
    return $isValid;
}

// Get folder name safely from POST or GET
$folderName = isset($_POST['folder_name']) ? trim($_POST['folder_name']) : (isset($_GET['folder']) ? trim($_GET['folder']) : '');
$folderName = $folderName !== null ? $folderName : '';

$uploadDir = "../../Mediaupload/" . $folderName . "/";

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

// Handle file editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editFile'])) {
    $id = intval($_POST['file_id']);
    $new_file_name = trim($_POST['file_name']);
    $temperature = !empty($_POST['temperature']) ? $_POST['temperature'] : NULL;
    $water_level = !empty($_POST['water_level']) ? floatval($_POST['water_level']) : NULL;
    $air_quality = !empty($_POST['air_quality']) ? floatval($_POST['air_quality']) : NULL;

    // Get current file details
    $stmt = $conn->prepare("SELECT file_name, folder_name, file_type FROM files WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($current_file_name, $folder_name, $file_type);
    $stmt->fetch();
    $stmt->close();

    if (empty($current_file_name)) {
        die("File not found in database");
    }

    // Preserve file extension
    $current_ext = pathinfo($current_file_name, PATHINFO_EXTENSION);
    $new_ext = pathinfo($new_file_name, PATHINFO_EXTENSION);
    
    if (empty($new_ext) && !empty($current_ext)) {
        $new_file_name .= '.' . $current_ext;
    }

    // Set paths
    $current_path = "../../Mediaupload/$folder_name/$current_file_name";
    $new_path = "../../Mediaupload/$folder_name/$new_file_name";

    if (!file_exists($current_path)) {
        die("Original file not found on server");
    }

    if ($new_file_name !== $current_file_name && file_exists($new_path)) {
        die("A file with that name already exists");
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Rename physical file
        if (!rename($current_path, $new_path)) {
            throw new Exception("Failed to rename file on server");
        }

        // Update database
        $stmt = $conn->prepare("UPDATE files SET file_name = ?, file_type = ?, temperature = ?, water_level = ?, air_quality = ?, date_modified = NOW() WHERE id = ?");
        $stmt->bind_param("sssddi", $new_file_name, $file_type, $temperature, $water_level, $air_quality, $id);

        if (!$stmt->execute()) {
            throw new Exception("Database update failed");
        }

        $conn->commit();
        echo "<meta http-equiv='refresh' content='0'>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        
        // Try to revert file rename if it succeeded but DB failed
        if (file_exists($new_path) && !file_exists($current_path)) {
            rename($new_path, $current_path);
        }
        
        die("Error: " . $e->getMessage());
    }
}

// Handle delete actions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    // Common for all delete actions - verify PIN
    $pinCode = $_POST['pin_code'] ?? null;
    if (!verifyAdminPin($conn, $pinCode)) {
        echo json_encode(["status" => "error", "message" => "Invalid PIN code"]);
        exit;
    } 

    // Single file delete
    if ($action === "deleteFile") {
        $fileId = $_POST['file_id'];

        // Get file details
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

                // Delete file from filesystem
                $filePath = "../../Mediaupload/$folderName/$fileName";
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                echo json_encode(["status" => "success", "message" => "File deleted successfully"]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => "Error deleting file: " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "File not found"]);
        }
        exit;
    }

    // Multiple files delete
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
            // Get files to delete
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
                $filePath = "../../Mediaupload/" . $file['folder_name'] . "/" . $file['file_name'];
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

    // Folder delete
    if ($action === "deleteFolder") {
        $folderName = $_POST['folder_name'];

        $conn->begin_transaction();
        try {
            // Get all files in folder
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

            // Delete folder from database
            $stmt = $conn->prepare("DELETE FROM media_folders WHERE folder_name = ?");
            $stmt->bind_param("s", $folderName);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            // Delete files from filesystem
            foreach ($files as $file) {
                $filePath = "../../Mediaupload/$folderName/$file";
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Delete folder itself
            $folderPath = "../../Mediaupload/$folderName";
            if (is_dir($folderPath)) {
                array_map('unlink', glob("$folderPath/*.*"));
                rmdir($folderPath);
            }

            echo json_encode(["status" => "success", "message" => "Folder and its contents deleted successfully"]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Error deleting folder: " . $e->getMessage()]);
        }
        exit;
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