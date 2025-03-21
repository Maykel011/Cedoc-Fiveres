<?php
include '../connection/Connection.php'; 
include '../AdminBackEnd/MediaFilesBE.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/media-files.css">
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
        <h1 class="main-title">Media Files</h1>

        <div class="top-controls">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search Folder">
                <select class="filter-select">
                    <option>Filter by Name</option>
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
            <tbody>
            <?php
$query = "SELECT * FROM media_folders ORDER BY date_modified DESC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>📁 {$row['folder_name']}</td>
        <td>{$row['date_modified']}</td>
        <td>Folder</td> <!-- Replacing 'type' column -->
        <td>{$row['num_contents']}</td>
        <td>
            <button class='rename-btn' data-id='{$row['id']}'>Rename</button>
            <button class='delete-btn' data-id='{$row['id']}'>Delete</button>
        </td>
    </tr>";
}
?>

            </tbody>
        </table>
    </div>
</div>



<script src="../../js/mediafile.js"></script>
</body>
</html>
