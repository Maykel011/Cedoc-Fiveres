<?php
include '../connection/Connection.php'; 
include '../userBackEnd/ViewFolderBE.php';

session_start();

// Corrected check (only allowing 'User')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    // Redirect to login page
    header("Location: ../../login/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES - View Folder</title>
    <link rel="stylesheet" href="../Css/Viewsfi.css">
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
            </li>
        </ul>
    </aside>

<div class="main-content">
    
<div class="table-container">
    <div class="header-controls">
        <h1 class="main-title"><?php echo htmlspecialchars($folderName); ?></h1>
        <div class="top-controls">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search Folder">
                <select class="filter-select">
                    <option value="name">Sort by Name</option>
                    <option value="date">Sort by Date Modified</option>
                </select>
            </div>
            <div class="action-buttons">
                <button id="uploadBtn" class="upload-button">Upload File</button>
                <button id="deleteSelectedBtn" class="delete-selected-button" disabled>Delete Selected</button>
                <button onclick="window.location.href='media-files.php'" class="back-button">Back</button>
            </div>
        </div>
    </div>
    <table>
    <thead>
        <tr>
            <th>Select</th>
            <th>Name</th>
            <th>Date Modified</th>
            <th>Type</th>
            <th>Temperature (°C)</th>
            <th>Water Level (M)</th>
            <th>Air Quality (PM2.5)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($files as $file): 
    // Correct path construction - matches where files are actually stored
    $filePath = "../../Mediaupload/" . htmlspecialchars($folderName) . "/" . htmlspecialchars($file['file_name']);
    error_log("Checking file at path: " . realpath($filePath));
    $fileType = strtolower($file['file_type']);
    $fileExists = file_exists($filePath);
    $fileExists = file_exists($filePath) && is_readable($filePath);
?>
        <tr>
            <td><input type="checkbox" name="selected_files[]" value="<?= $file['id'] ?>"></td>
            <td>
                <?php if ($fileExists): ?>
                    <a href="<?= "../../Mediaupload/" . htmlspecialchars($folderName) . "/" . htmlspecialchars($file['file_name']) ?>" 
   target="_blank" 
   class="file-link" 
   data-type="<?= htmlspecialchars($fileType) ?>"
   data-filename="<?= htmlspecialchars($file['file_name']) ?>"
   title="Click to view/download">
                   <?php 
                    // Display icon based on file type
                    if (strpos($fileType, 'image/') === 0) {
                        echo ' ';
                    } elseif ($fileType === 'application/pdf') {
                        echo '';
                    } elseif (strpos($fileType, 'video/') === 0) {
                        echo '';
                    } elseif (strpos($fileType, 'msword') !== false || 
                              strpos($fileType, 'wordprocessingml') !== false) {
                        echo ' ';
                    } elseif (strpos($fileType, 'ms-excel') !== false || 
                              strpos($fileType, 'spreadsheetml') !== false) {
                        echo ' ';
                    } elseif (strpos($fileType, 'audio/') === 0) {
                        echo '';
                    } else {
                        echo '';
                    }
                    echo htmlspecialchars($file['file_name']); 
                   ?>
                </a>
                <?php else: ?>
                    <span class="missing-file">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?= htmlspecialchars($file['file_name']) ?> (Missing)
                    </span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($file['date_modified']) ?></td>
            <td>
                <?php 
                    // Display simplified file type
                    if (strpos($fileType, 'image/') === 0) {
                        echo 'Image';
                    } elseif ($fileType === 'application/pdf') {
                        echo 'PDF';
                    } elseif (strpos($fileType, 'video/') === 0) {
                        echo 'Video';
                    } elseif (strpos($fileType, 'msword') !== false || 
                              strpos($fileType, 'wordprocessingml') !== false) {
                        echo 'Word';
                    } elseif (strpos($fileType, 'ms-excel') !== false || 
                              strpos($fileType, 'spreadsheetml') !== false) {
                        echo 'Excel';
                    } elseif (strpos($fileType, 'audio/') === 0) {
                        echo 'Audio';
                    } else {
                        echo htmlspecialchars($file['file_type']); 
                    }
                ?>
            </td>
            <td><?= htmlspecialchars($file['temperature'] ?? '') ?></td>
            <td><?= htmlspecialchars($file['water_level'] ?? '') ?></td>
            <td><?= htmlspecialchars($file['air_quality'] ?? '') ?></td>
            <td>
                <button onclick="openEditModal(<?= $file['id'] ?>, '<?= htmlspecialchars($file['file_name']) ?>', <?= $file['temperature'] ?? 'null' ?>, <?= $file['water_level'] ?? 'null' ?>, <?= $file['air_quality'] ?? 'null' ?>)">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    
    <!-- upload file-->
    <div id="uploadModal" class="customs-modal" style="display:none;">
    <div class="upload-modal-content">
        <span class="close" onclick="closeModal('uploadModal')"></span>
        <h2>Upload File</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fileInput">Choose File:</label>
                <input type="file" id="fileInput" name="file" required>
            </div>
            <div class="form-group">
                <label for="temperature">Temperature:</label>
                <input type="text" id="temperature" name="temperature" placeholder="Enter Temperature">
            </div>
            <div class="form-group">
                <label for="waterLevel">Water Level:</label>
                <input type="text" id="waterLevel" name="waterLevel" placeholder="Enter Water Level">
            </div>
            <div class="form-group">
                <label for="airQuality">Air Quality:</label>
                <input type="text" id="airQuality" name="airQuality" placeholder="Enter Air Quality">
            </div>
            <div class="button-group">
                <button type="submit" class="upload-btn">Upload</button>
                <button type="button" class="cancel-btn" onclick="closeModal('uploadModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>
    
<!-- Edit Modal -->
<div id="editModal" class="editcustom-modal"style="display:none;">
    <div class="edit-modal-content">
        <span class="close" onclick="closeModal('editModal')"></span>
        <h2>Edit File</h2>
        <form method="post">
            <input type="hidden" id="editFileId" name="file_id">
            
            <div class="form-group">
                <label>File Name:</label>
                <input type="text" id="editFileName" name="file_name" required>
            </div>

            <div class="form-group">
                <label>Temperature (°C):</label>
                <input type="number" id="editTemperature" name="temperature" step="0.1">
            </div>

            <div class="form-group">
                <label>Water Level (M):</label>
                <input type="number" id="editWaterLevel" name="water_level" step="0.1">
            </div>

            <div class="form-group">
                <label>Air Quality (PM2.5):</label>
                <input type="number" id="editAirQuality" name="air_quality" step="0.1">
            </div>

<!-- Button container for spacing -->
<div class="button-group">
        <button type="submit" name="editFile">Save Changes</button>
        <button type="button" class="cancel-button" onclick="closeModal('editModal')">Cancel</button>
    </div>
</form>
    </div>
</div>



<!-- Delete Modal -->
<div id="deleteModal" class="deletecustom-modal">
    <div class="delete-modal-content">
        <span class="close" onclick="closeModal('deleteModal')"></span>
        <h2>Delete Confirmation</h2>
        <p id="deleteFileName"></p>
        <p>Are you sure you want to delete this?</p>
        <div id="pinCodeSection" style="margin: 15px 0;">
            <label for="deletePinCode">Enter PIN Code:</label>
            <input type="password" id="deletePinCode" placeholder="6-digit PIN" maxlength="6" style="width: 90%;padding: 8px;margin-top: 5px; border-radius: 5px;">
            <p id="deletePinError" style="color: red; display: none;"></p>
        </div>
        <button id="deleteFileBtn">Delete</button>
        <button onclick="closeModal('deleteModal')">Cancel</button>
    </div>
</div>

<!-- Multiple Delete Confirmation Modal -->
<div id="multipleDeleteModal" class="deletecustom-modal" style="display:none;">
    <div class="delete-modal-content">
        <span class="close" onclick="closeModal('multipleDeleteModal')"></span>
        <h2>Delete Confirmation</h2>
        <p id="multipleDeleteMessage"></p>
        <div id="multipleDeletePinSection" style="margin: 15px 0;">
            <label for="multipleDeletePinCode">Enter PIN Code:</label>
            <input type="password" id="multipleDeletePinCode" placeholder="6-digit PIN" maxlength="6" style="width: 90%;padding: 8px;margin-top: 5px; border-radius: 5px;">
            <p id="multipleDeletePinError" style="color: red; display: none;"></p>
        </div>
        <button id="confirmMultipleDelete" class="btn-danger">Delete</button>
        <button onclick="closeModal('multipleDeleteModal')" class="btn-cancel">Cancel</button>
    </div>
</div>

<!-- Upload Success Modal -->
<div id="uploadSuccessModal" class="deletesuccess-modal" style="display: none;">
    <div class="success-modal-content">
        <span class="successclose" onclick="closeModal('uploadSuccessModal')"></span>
        <h2 id="uploadSuccessMessage">File Uploaded Successfully</h2>
    </div>
</div>


<!-- Success Modal -->
<div id="deleteSuccessModal" class="deletesuccess-modal" style="display: none;">
    <div class="success-modal-content">
        <span class="successclose" onclick="closeModal('deleteSuccessModal')"></span>
        <h2 id="deleteSuccessMessage">Deleted Successfully</h2>
    </div>
</div>

<script src="../js/Views.js"></script>
</body>
</html>
