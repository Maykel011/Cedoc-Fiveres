<?php
include '../connection/Connection.php'; 
include '../AdminBackEnd/ViewFolderBE.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES - View Folder</title>
    <link rel="stylesheet" href="../../Css/ViewFolds.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
<header class="header">
    <div class="header-content">
        <div class="left-side">
            <img src="../../assets/img/Logo.png" alt="Logo" class="logo">
        </div>
        <div class="right-side">
            <div class="user" id="userContainer">
                <img src="../../assets/icon/users.png" alt="User" class="icon" id="userIcon">
                <span class="admin-text">Admin</span>
                <div class="user-dropdown" id="userDropdown">
                    <a href="profile.php"><img src="../../assets/icon/updateuser.png" alt="Profile Icon" class="dropdown-icon"> Profile</a>
                    <a href="#"><img src="../../assets/icon/logout.png" alt="Logout Icon" class="dropdown-icon"> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<aside class="sidebar">
    <ul>
        <li class="dashboard">
            <a href="adminDashboard.php"><img src="../../Assets/Icon/Analysis.png" alt="Dashboard Icon" class="sidebar-icon"> Admin Dashboard</a>
        </li>
        <li class="media-files">
            <a href="media-files.php"><img src="../../Assets/Icon/file.png" alt="Media Files Icon" class="sidebar-icon"> Media Files</a>
        </li>
        <li class="resume">
            <a href="resume.php"><img src="../../Assets/Icon/Resume.png" alt="Resume Icon" class="sidebar-icon"> Resume</a>
        </li>
        <li class="vehicle-runs">
            <a href="vehicle-runs.php"><img src="../../assets/icon/vruns.png" alt="Vehicle Runs Icon" class="sidebar-icon"> Vehicle Runs</a>
        </li>
        <li class="manage-users">
            <a href="manage-users.php"><img src="../../Assets/Icon/user-management.png" alt="Manage Users Icon" class="sidebar-icon"> Manage Users</a>
        </li>
    </ul>
</aside>

<div class="main-content">
    
    <div class="table-container">
        <h1 class="main-title"><?php echo htmlspecialchars($folderName); ?></h1>
             <!-- Back Button -->
    <button onclick="window.location.href='media-files.php'" class="back-button">Back</button>
        <div class="top-controls">
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search Folder">
            <select class="filter-select">
                <option value="name">Sort by Name</option>
                <option value="date">Sort by Date Modified</option>
            </select>
        </div>

         <!-- Upload Button -->
    <button id="uploadBtn" class="upload-button">Upload File</button>
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
            <?php foreach ($files as $file): ?>
            <tr>
                <td><input type="checkbox" name="selected_files[]" value="<?= $file['id'] ?>"></td>
                <td><?= htmlspecialchars($file['file_name']) ?></td>
                <td><?= htmlspecialchars($file['date_modified']) ?></td>
                <td><?= htmlspecialchars($file['file_type']) ?></td>
                <td><?= htmlspecialchars($file['temperature'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($file['water_level'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($file['air_quality'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>

                <td>
                <button onclick="openEditModal(<?= $file['id'] ?>, '<?= htmlspecialchars($file['file_name']) ?>', <?= $file['temperature'] ?? 'null' ?>, <?= $file['water_level'] ?? 'null' ?>, <?= $file['air_quality'] ?? 'null' ?>)">Edit</button>
                <button onclick="openDeleteModal(<?= $file['id'] ?>, '<?= $file['file_name'] ?>')">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
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
        <h2>Delete File</h2>
        <p id="deleteFileName"></p>
        <p>Are you sure you want to delete this file?</p>
        <button id="deleteFileBtn">Delete</button>
        <button onclick="closeModal('deleteModal')">Cancel</button>
    </div>
</div>


<!-- 
Success Delete Modal  -->>
<div id="deleteSuccessModal" class="success-modal">
    <div class="success-modal-content">
        <h3>File Deleted Successfully!</h3>
    </div>
</div>

<script src="../../js/ViewFolder.js"></script>
</body>
</html>
