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
// Handle folder renaming
elseif ($action === "rename") {
    $folderId = $_POST['folder_id'];
    $newName = $_POST['new_name'];

    // Get the current folder name
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
        if (is_dir($oldPath)) {
            rename($oldPath, $newPath);
        }

        // Update database
        $stmt = $conn->prepare("UPDATE media_folders SET folder_name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $folderId);

        if ($stmt->execute()) {
            // Also update file references in the `files` table
            $updateStmt = $conn->prepare("UPDATE files SET folder_name = ? WHERE folder_name = ?");
            $updateStmt->bind_param("ss", $newName, $oldName);
            $updateStmt->execute();
            $updateStmt->close();

            echo json_encode(["status" => "success", "message" => "Folder renamed successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error renaming folder"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Folder not found"]);
    }
}


    // Handle folder deletion
    elseif ($action === "delete") {
        $folderId = $_POST['folder_id'];

        // Get folder name before deleting
        $stmt = $conn->prepare("SELECT folder_name FROM media_folders WHERE id = ?");
        $stmt->bind_param("i", $folderId);
        $stmt->execute();
        $stmt->bind_result($folderName);
        $stmt->fetch();
        $stmt->close();

        if (!empty($folderName)) {
            // Delete all files from the database
            $stmt = $conn->prepare("DELETE FROM files WHERE folder_name = ?");
            $stmt->bind_param("s", $folderName);
            $stmt->execute();
            $stmt->close();

            // Delete the folder from the database
            $stmt = $conn->prepare("DELETE FROM media_folders WHERE id = ?");
            $stmt->bind_param("i", $folderId);

            if ($stmt->execute()) {
                // Remove folder from filesystem
                $folderPath = "uploads/" . $folderName;
                deleteFolder($folderPath);

                echo json_encode(["status" => "success", "message" => "Folder and its contents deleted successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error deleting folder"]);
            }
            $stmt->close();
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

// ✅ Update `num_contents` when a file is uploaded
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $folderName = $_POST['folder_name'];
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];
    $uploadDir = "uploads/" . $folderName . "/";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmp, $filePath)) {
        $stmt = $conn->prepare("INSERT INTO files (file_name, file_type, folder_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fileName, $fileType, $folderName);

        if ($stmt->execute()) {
            // ✅ Update `num_contents`
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

// ✅ Update `num_contents` when a file is deleted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_file'])) {
    $fileName = $_POST['delete_file'];
    $folderName = $_POST['folder_name'];

    $stmt = $conn->prepare("DELETE FROM files WHERE file_name = ? AND folder_name = ?");
    $stmt->bind_param("ss", $fileName, $folderName);

    if ($stmt->execute()) {
        // ✅ Update `num_contents`
        $updateStmt = $conn->prepare("UPDATE media_folders SET num_contents = (SELECT COUNT(*) FROM files WHERE folder_name = ?) WHERE folder_name = ?");
        $updateStmt->bind_param("ss", $folderName, $folderName);
        $updateStmt->execute();
        $updateStmt->close();

        // ✅ Delete file from filesystem
        $filePath = "uploads/" . $folderName . "/" . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        echo json_encode(["status" => "success", "message" => "File deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting file"]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
