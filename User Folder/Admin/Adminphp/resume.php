<?php
include '../connection/Connection.php';
session_start();

// Check admin authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/resumes.css">
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
                <span class="admin-text">
                    <?php 
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
    <div class="table-container">
        <h1 class="main-title">Resume</h1>
        
        <!-- Search and Filter Section -->
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search applicants...">
                <button id="searchButton"><i class="fas fa-search"></i></button>
            </div>
            <div class="filter-box">
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Program Course</th>
                    <th>School/University</th>
                    <th>Contact Number</th>
                    <th>Email Address</th>
                    <th>OJT Hours</th>
                    <th>Role Position</th>
                    <th>Status</th>
                    <th>Resume</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="resumeTableBody">
                <?php
                // Fetch applicants from database
                $sql = "SELECT id, full_name, program_course, 
                        IF(school_university = 'Others', other_school, school_university) as school,
                        contact_number, email, ojt_hours, roles, resume_path, status 
                        FROM applicants 
                        ORDER BY application_date DESC";
                
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr data-id='{$row['id']}'>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['program_course']}</td>";
                        echo "<td>{$row['school']}</td>";
                        echo "<td>{$row['contact_number']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['ojt_hours']}</td>";
                        echo "<td>{$row['roles']}</td>";
                        echo "<td class='status-cell'><span class='status-badge status-{$row['status']}'>{$row['status']}</span></td>";
                        echo "<td><a href='{$row['resume_path']}' class='view-resume' target='_blank'>View</a></td>";
                        echo "<td class='action-cell'>";
                        echo "<button class='action-btn view-btn' data-id='{$row['id']}'><i class='fas fa-eye'></i></button>";
                        echo "<button class='action-btn edit-btn' data-id='{$row['id']}'><i class='fas fa-edit'></i></button>";
                        echo "<button class='action-btn delete-btn' data-id='{$row['id']}'><i class='fas fa-trash'></i></button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='no-data'>No applications found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="pagination">
            <button id="prevPage"><i class="fas fa-chevron-left"></i></button>
            <span id="pageInfo">Page 1 of 1</span>
            <button id="nextPage"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<!-- View Application Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Application Details</h2>
        <div id="applicationDetails"></div>
    </div>
</div>

<!-- Edit Status Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Update Application Status</h2>
        <form id="statusForm">
            <input type="hidden" id="applicantId">
            <div class="form-group">
                <label for="statusSelect">Status:</label>
                <select id="statusSelect" name="status">
                    <option value="Pending">Pending</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="4"></textarea>
            </div>
            <button type="submit" class="submit-btn">Update</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../js/resume.js"></script>
</body>
</html>
