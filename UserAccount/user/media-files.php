<?php
include '../connection/Connection.php'; 
include '../userBackEnd/MediaFilesBE.php';

session_start();

// Corrected check (only allowing 'User')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    // Redirect to login page
    header("Location: ../../login/login.php");
    exit();
}


?>

<!DOCTYPE<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES</title>
    <link rel="stylesheet" href="../Css/mediafiles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
<header class="header">
        <div class="header-content">
            <div class="left-side">
            <img src="../Assets/img/logo.png" alt="Logo" class="logo">
            </div>
            <div class="right-side">
                <div class="user" id="userContainer">
                <img src="../Assets/Icon/users.png" alt="User" class="icon" id="userIcon">
                    <span class="admin-text">
                    <?php 
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'User') {
                        if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
                            echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                        } else {
                            echo 'User';
                        }
                    }
                    ?>
                </span>
                <div class="user-dropdown" id="userDropdown">
                    <a href="Profile.php"><img src="../Assets/Icon/updateuser.png" alt="Profile Icon" class="dropdown-icon"> Profile</a>
                    <a href="#" id="logoutLink"><img src="../Assets/Icon/logout.png" alt="Logout Icon" class="dropdown-icon"> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

<!-- Logout Modal -->
<div id="logoutModal" class="logout-modal">
    <div class="logout-modal-content">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout from your admin account?</p>
        <div class="logout-modal-buttons">
            <button id="logoutCancel" class="logout-modal-btn logout-modal-cancel">Cancel</button>
            <button id="logoutConfirm" class="logout-modal-btn logout-modal-confirm">Logout</button>
        </div>
    </div>
</div>



    <aside class="sidebar">
        <ul>
            <li class="dashboard">
                <a href="userDashboard.php"><img src="../Assets/Icon/analysis.png" alt="Dashboard Icon" class="sidebar-icon">Dashboard</a>
            </li>
            <li class="media-files">
                <a href="media-files.php"><img src="../Assets/Icon/file.png" alt="Media Files Icon" class="sidebar-icon"> Media Files</a>
            </li>
            <li class="resume">
                <a href="resume.php"><img src="../Assets/Icon/resume.png" alt="Resume Icon" class="sidebar-icon">Intern Application</a>
            </li>
            <li class="vehicle-runs">
                <a href="vehicle-runs.php"><img src="../Assets/Icon/vruns.png" alt="Vehicle Runs Icon" class="sidebar-icon"> Vehicle Runs</a>
            </li>q
        </ul>
    </aside>

<div class="main-content">
<div class="table-container">
    <h1 class="main-title">Media Files</h1>

    <div class="top-controls">
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search Folder">
            <select class="filter-select">
                <option value="name">Sort by Name</option>
                <option value="date">Sort by Date Modified</option>
            </select>
        </div>

        <div class="folder-container">
            <input type="text" class="folder-name-input" placeholder="Enter folder name">
            <button class="create-folder-btn">Create Folder</button>
        </div>
    </div>
            <table>
                <thead>
                    <tr>
                        <th>Folder Name</th>
                        <th>Date Modified</th>
                        <th>Type</th>
                        <th>Number of Contents</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="media-table-body">
    <?php
    $query = "SELECT * FROM media_folders ORDER BY date_modified DESC";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) :
    ?>
        <tr id="folder-<?php echo $row['id']; ?>">
            <td>
                <a href="view-folder.php?folder=<?php echo urlencode($row['folder_name']); ?>" style="text-decoration: none; color: black;">
                    <span class="folder-icon">📁</span>
                    <span class="folder-name" id="folder-name-<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['folder_name']); ?>
                    </span>
                </a>
                <input type="text" id="rename-input-<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['folder_name']); ?>" style="display:none;">
            </td>
            <td><?php echo date('Y-m-d h:i:s A', strtotime($row['date_modified'])); ?></td>
            <td>Folder</td>
            <td><?php echo htmlspecialchars($row['num_contents']); ?></td>
            <td>
                <button class="rename-btn" data-id="<?php echo $row['id']; ?>">Rename</button>
                
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>



<!-- Rename Modal -->
<div id="renameModal" class="custom-modal">
    <div class="rename-modal-content">
        <span class="close" onclick="ModalManager.closeModal('renameModal')"></span>
        <h2>Rename Folder</h2>
        <p id="renameFolderName"></p>
        <input type="text" id="newFolderName" placeholder="Enter new folder name">
        <p id="renameError" style="color: red; display: none; font-size: 14px;"></p> <!-- Error Message Field -->
        <button id="renameFolderBtn">Rename</button>
        <button onclick="ModalManager.closeModal('renameModal')">Cancel</button>
    </div>
</div>




<!-- Delete Modal -->
<div id="deleteModal" class="custom-modal">
    <div class="delete-modal-content">
        <span class="close" onclick="closeModal('deleteModal')"></span>
        <h2>Delete Folder</h2>
        <p id="deleteFolderName"></p>
        <p>Are you sure you want to delete this folder?</p>
        <div id="pinCodeSection" style="margin: 15px 0;">
            <label for="deletePinCode">Enter PIN code:</label>
            <input type="password" id="deletePinCode" placeholder="6-digit PIN" maxlength="6" style="width: 90%; padding: 8px; margin-top: 5px;">
            <p id="deletePinError" style="color: red; display: none;"></p>
        </div>
        <button id="deleteFolderBtn">Delete</button>
        <button onclick="closeModal('deleteModal')">Cancel</button>
    </div>
</div>


<!-- Success Rename Modal -->
<div id="renameSuccessModal" class="success-modal">
    <div class="success-modal-content">
        <h3>Folder Renamed Successfully!</h3>
    </div>
</div>

<!-- Success Delete Modal -->
<div id="deleteSuccessModal" class="success-modal">
    <div class="success-modal-content">
        <h3>Folder Deleted Successfully!</h3>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="custom-modal error-modal">
    <div class="custom-modal-content">
        <h2 class="modal-title">Error</h2>
        <p class="modal-message">Please enter a folder name.</p>
        <button class="close-btn" onclick="closeModal('errorModal')">OK</button>
    </div>
</div>

<script src="../js/mediafiles.js"></script>
</body>
</html>
