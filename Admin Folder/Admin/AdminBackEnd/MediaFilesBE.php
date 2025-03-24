<?php
include '../connection/Connection.php'; // Database connection

// Handle folder creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === "create") {
        $folderName = $_POST['folder_name'];

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
?>
