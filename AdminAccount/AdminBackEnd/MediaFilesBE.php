<?php
include '../connection/Connection.php'; // Database connection

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Handle folder actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === "create") {
        $folderName = trim($_POST['folder_name']);

        if (empty($folderName)) {
            echo json_encode(["status" => "error", "message" => "Folder name is required"]);
            exit;
        }

        // Insert folder into the database
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
    $newName = trim($_POST['new_name']);

    if (empty($newName)) {
        echo json_encode(["status" => "error", "message" => "New folder name is required"]);
        exit;
    }

    // Get current folder name
    $stmt = $conn->prepare("SELECT folder_name FROM media_folders WHERE id = ?");
    $stmt->bind_param("i", $folderId);
    $stmt->execute();
    $stmt->bind_result($oldName);
    $stmt->fetch();
    $stmt->close();

    if (empty($oldName)) {
        echo json_encode(["status" => "error", "message" => "Folder not found"]);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Rename folder in media_folders table
        $stmt = $conn->prepare("UPDATE media_folders SET folder_name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $folderId);
        $stmt->execute();
        $stmt->close();

        // Update all files in this folder to reference the new folder name
        $stmt = $conn->prepare("UPDATE files SET folder_name = ? WHERE folder_name = ?");
        $stmt->bind_param("ss", $newName, $oldName);
        $stmt->execute();
        $stmt->close();

        // Rename the physical folder
        $oldPath = "../../Mediaupload/" . $oldName;
        $newPath = "../../Mediaupload/" . $newName;

        if (file_exists($oldPath)) {
            if (!rename($oldPath, $newPath)) {
                throw new Exception("Failed to rename folder on filesystem");
            }
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Folder renamed successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Error renaming folder: " . $e->getMessage()]);
    }
}

    // Handle folder deletion
    elseif ($action === "delete") {
        $folderId = $_POST['folder_id'];
        $pinCode = $_POST['pin_code'] ?? null;

        // Verify admin PIN code
        $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'Admin' AND pin_code = ?");
        $stmt->bind_param("s", $pinCode);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Invalid PIN code"]);
            exit;
        }
        $stmt->close();

        // Get folder name before deleting
        $stmt = $conn->prepare("SELECT folder_name FROM media_folders WHERE id = ?");
        $stmt->bind_param("i", $folderId);
        $stmt->execute();
        $stmt->bind_result($folderName);
        $stmt->fetch();
        $stmt->close();

        if (!empty($folderName)) {
            $conn->begin_transaction();

            try {
                // Delete all files from the database
                $stmt = $conn->prepare("DELETE FROM files WHERE folder_name = ?");
                $stmt->bind_param("s", $folderName);
                $stmt->execute();
                $stmt->close();

                // Delete the folder from the database
                $stmt = $conn->prepare("DELETE FROM media_folders WHERE id = ?");
                $stmt->bind_param("i", $folderId);
                $stmt->execute();
                $stmt->close();

                $conn->commit();

                // Remove folder from filesystem
                $folderPath = "../../Mediaupload/" . $folderName;
                deleteFolder($folderPath);

                echo json_encode(["status" => "success", "message" => "Folder and its contents deleted successfully"]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => "Error deleting folder: " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Folder not found"]);
        }
    }

    $conn->close();
}
// Function to delete folder and all its contents
function deleteFolder($folderPath) {
    if (!is_dir($folderPath)) {
        return;
    }
    $files = array_diff(scandir($folderPath), array('.', '..'));
    foreach ($files as $file) {
        $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
        is_dir($filePath) ? deleteFolder($filePath) : unlink($filePath);
    }
    rmdir($folderPath);
}



// Upload File & Update `num_contents`
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $folderName = $_POST['folder_name'];
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];
    $uploadDir = "../../Mediaupload/" . $folderName . "/";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmp, $filePath)) {
        $conn->begin_transaction();

        try {
            // Insert into files table
            $stmt = $conn->prepare("INSERT INTO files (file_name, file_type, folder_name) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fileName, $fileType, $folderName);
            $stmt->execute();
            $stmt->close();

            // Update num_contents
            $updateStmt = $conn->prepare("
                UPDATE media_folders 
                SET num_contents = num_contents + 1 
                WHERE folder_name = ?
            ");
            $updateStmt->bind_param("s", $folderName);
            $updateStmt->execute();
            $updateStmt->close();

            $conn->commit();

            echo json_encode(["status" => "success", "message" => "File uploaded successfully"]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Database insertion failed: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed"]);
    }
    $conn->close();
    exit;
}

// Delete File & Update `num_contents`
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_file'])) {
    $fileName = $_POST['delete_file'];
    $folderName = $_POST['folder_name'];

    $conn->begin_transaction();

    try {
        // Delete the file from the database first
        $stmt = $conn->prepare("DELETE FROM files WHERE file_name = ? AND folder_name = ?");
        $stmt->bind_param("ss", $fileName, $folderName);
        $stmt->execute();
        $stmt->close();

        // Update num_contents in media_folders
        $updateStmt = $conn->prepare("
            UPDATE media_folders 
            SET num_contents = num_contents - 1 
            WHERE folder_name = ?
        ");
        $updateStmt->bind_param("s", $folderName);
        $updateStmt->execute();
        $updateStmt->close();

        $conn->commit();

        // Delete file from filesystem
        $filePath = "uploads/" . $folderName . "/" . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        echo json_encode(["status" => "success", "message" => "File deleted successfully"]);
    } catch (Exception $e) {
        // Rollback if there's an error
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Error deleting file: " . $e->getMessage()]);
    }

    $conn->close();
    exit;
}


?>