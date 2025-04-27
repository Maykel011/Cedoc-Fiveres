<?php
include '../connection/Connection.php'; 
include '../userBackEnd/UserInternResumeBE.php';
session_start();

// Corrected check (only allowing 'User')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    // Redirect to login page
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
    <link rel="stylesheet" href="../../Css/UserInternsResume.css">
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
        <p>Are you sure you want to logout from your account?</p>
        <div class="logout-modal-buttons">
            <button id="logoutCancel" class="logout-modal-btn logout-modal-cancel">Cancel</button>
            <button id="logoutConfirm" class="logout-modal-btn logout-modal-confirm">Logout</button>
        </div>
    </div>
</div>

<aside class="sidebar">
    <ul>
        <li class="dashboard">
            <a href="userDashboard.php"><img src="../../Assets/Icon/Analysis.png" alt="Dashboard Icon" class="sidebar-icon">Dashboard</a>
        </li>
        <li class="media-files">
            <a href="media-files.php"><img src="../../Assets/Icon/file.png" alt="Media Files Icon" class="sidebar-icon"> Media Files</a>
        </li>
        <li class="resume">
            <a href="resume.php"><img src="../../Assets/Icon/Resume.png" alt="Resume Icon" class="sidebar-icon"> Intern Application</a>
        </li>
        <li class="vehicle-runs">
            <a href="vehicle-runs.php"><img src="../../assets/icon/vruns.png" alt="Vehicle Runs Icon" class="sidebar-icon"> Vehicle Runs</a>
        </li>
     
    </ul>
</aside>

<div class="main-content">
        <div class="table-container">
            <h1 class="main-title">Intern Application</h1>
            <br>

            <table>
             <!-- Add this inside your table head -->
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Program Course</th>
                            <th>School/University</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>OJT Hours</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Questions</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resume">
                    <?php
                    $applicants = $internResume->getApplicants();
                    foreach ($applicants as $applicant) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($applicant['full_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($applicant['program_course']) . '</td>';
                        echo '<td>' . htmlspecialchars($applicant['school_university']) . '</td>';
                        echo '<td>' . htmlspecialchars($applicant['contact_number']) . '</td>';
                        echo '<td>' . htmlspecialchars($applicant['email']) . '</td>';
                        echo '<td>' . htmlspecialchars($applicant['ojt_hours']) . '</td>';
                        echo '<td>' . htmlspecialchars($applicant['roles']) . '</td>';
                        echo '<td>' . htmlspecialchars($applicant['status']) . '</td>';
                        
                        // Notes button and modal
                        echo '<td><button class="view-notes-btn" data-id="' . $applicant['id'] . '" data-notes="' . htmlspecialchars($applicant['notes'] ?? '') . '">View Notes</button></td>';
                        
                        // Questions button and modal
                        echo '<td><button class="view-questions-btn" data-id="' . $applicant['id'] . '" 
                            data-q1="' . htmlspecialchars($applicant['q1']) . '"
                            data-q2="' . htmlspecialchars($applicant['q2']) . '"
                            data-q3="' . htmlspecialchars($applicant['q3']) . '"
                            data-q4="' . htmlspecialchars($applicant['q4']) . '"
                            data-q5="' . htmlspecialchars($applicant['q5']) . '">View Questions</button></td>';
                        
                        // Documents button - simplified version
                        echo '<td><button class="view-documents-btn" data-id="' . $applicant['id'] . '">View Documents</button></td>';
                        
                        // Actions
                        echo '<td>
                                <button class="edit-btn" data-id="' . $applicant['id'] . '">Edit</button>
                               
                            </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
<!-- Notes Modal -->
<div id="notesModal" class="modal notes-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Applicant Notes</h2>
        <div class="notes-content">
            <p id="notesText"></p>
        </div>
        <div class="notes-form">
            <input id="newNotes" placeholder="Add or update notes..."></input>
            <select id="statusSelect">
                <option value="Pending">Pending</option>
                <option value="Under Review">Under Review</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
            </select>
            <button id="saveNotes">Save Changes</button>
        </div>
    </div>
</div>

<!-- Questions Modal -->
<div id="questionsModal" class="modal questions-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Applicant Questions</h2>
        <div class="questions-content">
            <div class="question-item">
                <h3>Tell me about yourself?</h3>
                <p id="q1Answer"></p>
            </div>
            <div class="question-item">
                <h3>What are your strengths and weaknesses?</h3>
                <p id="q2Answer"></p>
            </div>
            <div class="question-item">
                <h3>What do you hope to gain from this internship?</h3>
                <p id="q3Answer"></p>
            </div>
            <div class="question-item">
                <h3>How do you handle deadlines and pressure?</h3>
                <p id="q4Answer"></p>
            </div>
            <div class="question-item">
                <h3>What makes you stand out from the rest?</h3>
                <p id="q5Answer"></p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal delete-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Confirm Delete</h2>
        <p>Are you sure you want to delete this applicant's record?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="confirm-btn">Delete</button>
            <button id="cancelDelete" class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal edit-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Applicant Status</h2>
        <div class="edit-form">
            <select id="editStatusSelect">
                <option value="Pending">Pending</option>
                <option value="Under Review">Under Review</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
            </select>
            <input id="editNotes" placeholder="Update notes..."></input>
            <button id="saveEdit">Save Changes</button>
        </div>
    </div>
</div>

<!-- Saving Modal -->
<div id="savingModal" class="custom-modal saving-modal" style="display: none;">
  <div class="custom-modal-center">
    <div class="custom-modal-content">
      <div class="custom-modal-body">
        <div class="loading-indicator">
          <div class="loading-spinner"></div>
          <p id="savingMessage" class="loading-text">Saving changes...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="custom-modal success-modal" style="display: none;">
  <div class="custom-modal-center">
    <div class="custom-modal-content">
      <div class="custom-modal-body">
        <div class="success-indicator">
          <i class="fas fa-check-circle success-icon"></i>
          <p id="successMessage" class="success-text">Changes saved successfully!</p>
        </div>
      </div>
    </div>
  </div>
</div>
    
<script src="../../js/UserInternsResume.js"></script>
</body>
</html>