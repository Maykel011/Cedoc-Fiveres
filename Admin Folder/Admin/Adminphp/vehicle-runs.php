<?php
include '../connection/Connection.php'; 
include '../AdminBackEnd/VehicleTrack.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/vhruns.css">
    <link rel="stylesheet" href="../../Css/VehicleTrack.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                <span class="admin-text">
                    <?php 
                    // Display first and last name if available, otherwise show "Admin"
                    if(isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                        echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                    } else {
                        echo 'Admin';
                    }
                    ?>
                </span>
                <div class="user-dropdown" id="userDropdown">
                    <a href="profile.php"><img src="../../assets/icon/updateuser.png" alt="Profile Icon" class="dropdown-icon"> Profile</a>
                    <a href="#" id="logoutLink"><img src="../../assets/icon/logout.png" alt="Logout Icon" class="dropdown-icon"> Logout</a>
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
        <h1 class="main-title">Vehicle Runs</h1>
            <div class="top-bar">
                <div class="button-group">
                <div class="date-filter">
            </div>

            <a href="adminDashboard.php"><button class="btn btn-custom">Back</button></a>
            <button type="button" class="btn btn-upload-case" onclick="openUploadModal()">Upload Case</button>

            </div>
            </div>
            <form id="deleteForm" method="POST">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 5px;">
           <button type="submit" name="delete_selected" class="btn btn-danger">Delete Selected</button>
            </div>
            <div class="filters">
    <label for="filter-vehicle">Vehicle Team:</label>
    <select id="filter-vehicle" onchange="filterTable()">
        <option value="">All</option>
        <option value="Alpha">Alpha</option>
        <option value="Bravo">Bravo</option>
        <option value="Charlie">Charlie</option>
        <option value="Delta">Delta</option>
    </select>

    <label for="filter-case">Case Type:</label>
    <select id="filter-case" onchange="filterTable()">
        <option value="">All</option>
        <option value="Medical Case">Medical Case</option>
        <option value="Medical Standby">Medical Standby</option>
        <option value="Trauma Case">Trauma Case</option>
        <option value="Patient Transportation">Patient Transportation</option>
    </select>
    
    <div>
    <label for="filter-start-date">Date Range:</label>
    </div>
    <div>
    <input type="date" id="filter-start-date" onchange="filterTable()">
    </div>
    <div>
    <label for="filter-end-date">To:</label>
    </div>
    <div>
    <input type="date" id="filter-end-date" onchange="filterTable()">
    </div>
    <div>
    <button type="button" onclick="clearFilters()">Clear Filters</button>
    </div>
    </div>
    <br>
            <div>
            <input type="text" id="searchBar" placeholder="Search files..." onkeyup="searchFiles()">
            </div>
            <br>
            <table border="1">
            <th>Select</th>
                <th>Vehicle Team</th>
                <th>Case Type</th>
                <th>Transport Officer</th>
                <th>Emergency Responders</th>
                <th>Location</th>
                <th>Dispatch Time</th>
                <th>Back to Base Time</th>
                <th>Case Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM vehicle_runs ORDER BY id DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" name="delete_cases[]" value="<?php echo $row['id']; ?>"></td>
                    <td><?php echo htmlspecialchars($row['vehicle_team']); ?></td>
                    <td><?php echo htmlspecialchars($row['case_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['transport_officer']); ?></td>
                    <td><?php echo htmlspecialchars($row['emergency_responders']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td><?php echo htmlspecialchars($row['dispatch_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['back_to_base_time']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($row['case_image']); ?>" width="100"></td>
                    <td>
                        <button type="button" onclick="viewCase(<?php echo htmlspecialchars(json_encode($row)); ?>)">View</button>
                        <button type="button" onclick="editCase(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</form>


<!-- Upload Case Modal -->
<div class="modal fade" id="uploadCaseModal" tabindex="-1" aria-labelledby="uploadCaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-transparent">
                    <h5 class="modal-title" id="uploadCaseModalLabel">Upload Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <label for="vehicle_team">Vehicle Team</label>
                        <select id="vehicle_team" name="vehicle_team" required>
                            <option value="Alpha">Alpha</option>
                            <option value="Bravo">Bravo</option>
                            <option value="Charlie">Charlie</option>
                            <option value="Delta">Delta</option>
                        </select>

                        <label for="case_type">Case Type</label>
                        <select id="case_type" name="case_type" required>
                            <option value="Medical Case">Medical Case</option>
                            <option value="Medical Standby">Medical Standby</option>
                            <option value="Trauma Case">Trauma Case</option>
                            <option value="Patient Transportation">Patient Transportation</option>
                        </select>

                        <label for="transport_officer">Transport Officer</label>
                        <input type="text" id="transport_officer" name="transport_officer" required>

                        <label for="emergency_responders">Emergency Responders</label>
                        <input type="text" id="emergency_responders" name="emergency_responders" required>

                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" required>

                        <label for="dispatch_time">Dispatch Time</label>
                        <input type="datetime-local" id="dispatch_time" name="dispatch_time" required>

                        <label for="back_to_base_time">Back to Base Time</label>
                        <input type="datetime-local" id="back_to_base_time" name="back_to_base_time" required>

                        <label for="case_image">Case Image</label>
                        <input type="file" id="case_image" name="case_image" accept="image/*">

                        <button type="submit" name="submit" class="btn btn-success">Upload Case</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> 
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Vehicle Run</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="edit-id" name="id">
                    
                    <label for="edit-vehicle_team">Vehicle Team</label>
                    <select id="edit-vehicle_team" name="vehicle_team" required>
                        <?php 
                        $vehicle_teams = ["Alpha", "Bravo", "Charlie", "Delta"];
                        foreach ($vehicle_teams as $team) {
                            echo "<option value='$team'>$team</option>";
                        } 
                        ?>
                    </select>

                    <label for="edit-case_type">Case Type</label>
                    <select id="edit-case_type" name="case_type" required>
                        <?php 
                        $case_types = ["Medical Case", "Medical Standby", "Trauma Case", "Patient Transportation"];
                        foreach ($case_types as $type) {
                            echo "<option value='$type'>$type</option>";
                        } 
                        ?>
                    </select>

                    <label for="edit-transport_officer">Transport Officer</label>
                    <input type="text" id="edit-transport_officer" name="transport_officer" required>

                    <label for="edit-emergency_responders">Emergency Responders</label>
                    <input type="text" id="edit-emergency_responders" name="emergency_responders" required>

                    <label for="edit-location">Location</label>
                    <input type="text" id="edit-location" name="location" required>

                    <label for="edit-case_image">Case Image</label>
                    <input type="file" id="edit-case_image" name="case_image">
                    <img id="edit-preview-image" width="100">

                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="../../js/vehiclerun.js"></script>
</body>
</html>