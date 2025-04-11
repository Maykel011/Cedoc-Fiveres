<?php
include '../connection/Connection.php';
session_start();

// Check admin authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}

// Handle delete action
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    
    // First get the resume path to delete the file
    $sql = "SELECT resume_path, moa_path, recom_path FROM applicants WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row) {
        // Delete files from server
        $files = [$row['resume_path']];
        if (!empty($row['moa_path'])) $files[] = $row['moa_path'];
        if (!empty($row['recom_path'])) $files[] = $row['recom_path'];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Delete from database
        $delete_sql = "DELETE FROM applicants WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Record not found']);
    }
    exit();
}

// Handle status update
if (isset($_POST['update_id'])) {
    $id = $_POST['update_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    
    $sql = "UPDATE applicants SET status = ?, notes = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $notes, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'status' => $status]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

// Handle view details request
if (isset($_GET['view_id'])) {
    $id = $_GET['view_id'];
    
    $sql = "SELECT *, IF(school_university = 'Others', other_school, school_university) as school 
            FROM applicants WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Application not found']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/resume.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Main Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 60%;
            max-width: 700px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
        }

        .close-modal:hover {
            color: #333;
        }

        /* Detail Rows */
        .detail-row {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .detail-row label {
            font-weight: bold;
            width: 150px;
            flex-shrink: 0;
        }

        .detail-row span, .detail-row p {
            flex-grow: 1;
        }

        #detail-notes {
            white-space: pre-line;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            margin-top: 5px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background: #f0f0f0;
        }

        .edit-btn {
            color: #3498db;
        }

        .delete-btn {
            color: #e74c3c;
        }

        /* Notes and Questions */
        .notes-cell, .questions-cell {
            position: relative;
            max-width: 200px;
        }

        .notes-preview {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .view-notes {
            cursor: pointer;
            margin-left: 5px;
            color: #3498db;
        }

        .view-notes:hover {
            color: #2980b9;
        }

        /* Documents */
        .documents-cell {
           
            gap: 10px;
        }

        .document-link {
            color: #555;
            font-size: 1.2em;
            text-decoration: none;
            cursor: pointer;
        }

        .document-link:hover {
            color: #3498db;
        }

        /* Questions Modal */
        .questions-modal-content {
            max-height: 80vh;
            overflow-y: auto;
        }

        .question-item {
            margin-bottom: 15px;
        }

        .question-item h4 {
            margin-bottom: 5px;
            font-size: 14px;
            color: #333;
        }

        .question-item p {
            background: #f9f9f9;
            padding: 8px;
            border-radius: 4px;
            font-size: 13px;
            white-space: pre-line;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-Pending {
            background-color: #FFF3CD;
            color: #856404;
        }

        .status-Under Review {
            background-color: #D1ECF1;
            color: #0C5460;
        }

        .status-Accepted {
            background-color: #D4EDDA;
            color: #155724;
        }

        .status-Rejected {
            background-color: #F8D7DA;
            color: #721C24;
        }

        /* Search and Filter */
        .search-filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box {
            flex-grow: 1;
            display: flex;
        }

        .search-box input {
            flex-grow: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }

        .search-box button {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        .filter-box select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

    </style>
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
<div id="logoutModal" class="modal">
    <div class="modal-content">
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
    <tbody id="resumeTableBody">
        <?php
        $sql = "SELECT id, full_name, program_course, 
                       IF(school_university = 'Others', other_school, school_university) AS school,
                       contact_number, email, ojt_hours, roles, resume_path, moa_path, recom_path, 
                       status, notes, q1, q2, q3, q4, q5
                FROM applicants 
                ORDER BY application_date DESC";
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr data-id='{$row['id']}'>";
                
                echo "<td>{$row['full_name']}</td>";
                echo "<td>{$row['program_course']}</td>";
                echo "<td>{$row['school']}</td>";
                echo "<td>{$row['contact_number']}</td>";
                echo "<td>{$row['email']}</td>";
                echo "<td>{$row['ojt_hours']}</td>";
                echo "<td>{$row['roles']}</td>";

                // Status
                echo "<td class='status-cell'>
                        <span class='status-badge status-{$row['status']}'>
                            {$row['status']}
                        </span>
                      </td>";

                // Notes
                $notesText = !empty($row['notes']) ? substr($row['notes'], 0, 20) . '...' : 'No notes';
                echo "<td class='notes-cell'>
                        <span class='notes-preview'>{$notesText}</span>
                        <i class='fas fa-eye view-notes' data-id='{$row['id']}'></i>
                      </td>";

                // Questions
                echo "<td class='questions-cell'>
                        <a href='#' class='view-questions-link' data-id='{$row['id']}'>
                            <i class='fas fa-eye'></i> View
                        </a>
                      </td>";

                // Documents
               // Documents
echo "<td class='documents-cell'>";
if (!empty($row['resume_path'])) {
    echo "<a href='" . htmlspecialchars($row['resume_path']) . "' class='document-link' target='_blank' title='Resume' data-path='" . htmlspecialchars($row['resume_path']) . "'>
            <i class='fas fa-file-alt'></i>
          </a> ";
}
if (!empty($row['moa_path'])) {
    echo "<a href='" . htmlspecialchars($row['moa_path']) . "' class='document-link' target='_blank' title='MOA' data-path='" . htmlspecialchars($row['moa_path']) . "'>
            <i class='fas fa-file-contract'></i>
          </a> ";
}
if (!empty($row['recom_path'])) {
    echo "<a href='" . htmlspecialchars($row['recom_path']) . "' class='document-link' target='_blank' title='Recommendation' data-path='" . htmlspecialchars($row['recom_path']) . "'>
            <i class='fas fa-file-signature'></i>
          </a>";
}
echo "</td>";
                

                // Actions
                echo "<td class='action-cell'>
                        <div class='action-buttons'>
                            <button class='action-btn edit-btn' data-id='{$row['id']}' title='Edit'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button class='action-btn delete-btn' data-id='{$row['id']}' title='Delete'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>
                      </td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='12' class='no-data'>No applications found</td></tr>";
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
        <div id="applicationDetails">
            <div class="detail-row">
                <label>Full Name:</label>
                <span id="detail-name"></span>
            </div>
            <div class="detail-row">
                <label>Program/Course:</label>
                <span id="detail-course"></span>
            </div>
            <div class="detail-row">
                <label>School/University:</label>
                <span id="detail-school"></span>
            </div>
            <div class="detail-row">
                <label>Contact Number:</label>
                <span id="detail-contact"></span>
            </div>
            <div class="detail-row">
                <label>Email Address:</label>
                <span id="detail-email"></span>
            </div>
            <div class="detail-row">
                <label>OJT Hours:</label>
                <span id="detail-hours"></span>
            </div>
            <div class="detail-row">
                <label>Role Position:</label>
                <span id="detail-role"></span>
            </div>
            <div class="detail-row">
                <label>Status:</label>
                <span id="detail-status" class="status-badge"></span>
            </div>
            <div class="detail-row">
                <label>Notes:</label>
                <p id="detail-notes"></p>
            </div>
            <div class="detail-row">
                <label>Resume:</label>
                <a id="detail-resume-link" href="#" target="_blank" class="view-resume">
                    <i class="fas fa-eye"></i> View Resume
                </a>
            </div>
        </div>
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
                <select id="statusSelect" name="status" class="form-control">
                    <option value="Pending">Pending</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="4" class="form-control"></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Notes Modal -->
<div id="notesModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Application Notes</h2>
        <div id="notesContent" class="modal-body-content"></div>
    </div>
</div>

<!-- Questions Modal -->
<div id="questionsModal" class="modal">
    <div class="modal-content questions-modal-content">
        <span class="close-modal">&times;</span>
        <h2>Application Questions</h2>
        <div id="questionsContent"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // User dropdown toggle
    $('#userIcon').click(function() {
        $('#userDropdown').toggle();
    });

    // Logout modal handling
    $('#logoutLink').click(function(e) {
        e.preventDefault();
        $('#logoutModal').show();
    });

    $('#logoutCancel').click(function() {
        $('#logoutModal').hide();
    });

    $('#logoutConfirm').click(function() {
        window.location.href = '../../../login/logout.php';
    });

    // Close modals when clicking the X button
    $('.close-modal').click(function() {
        $(this).closest('.modal').hide();
    });

    // Close modals when clicking outside
    $(window).click(function(event) {
        if ($(event.target).hasClass('modal')) {
            $('.modal').hide();
        }
    });

    // Search functionality
    $('#searchButton').click(function() {
        filterApplications();
    });

    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            filterApplications();
        }
    });

    // Filter functionality
    $('#statusFilter').change(function() {
        filterApplications();
    });

    // View application details
    $(document).on('click', '.view-resume', function(e) {
        e.preventDefault();
        var resumeUrl = $(this).attr('href');
        if (resumeUrl) {
            // Adjust path if needed
            if (!resumeUrl.startsWith('http') && !resumeUrl.startsWith('/')) {
                resumeUrl = '../' + resumeUrl;
            }
            window.open(resumeUrl, '_blank');
        }
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        var applicantId = $(this).data('id');
        $('#applicantId').val(applicantId);
        
        // Fetch current status and notes
        $.get('resume.php?view_id=' + applicantId, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                $('#statusSelect').val(data.status);
                $('#notes').val(data.notes || '');
                $('#editModal').show();
            }
        }, 'json');
    });

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this application?')) {
            var applicantId = $(this).data('id');
            
            $.ajax({
                url: 'resume.php',
                type: 'POST',
                data: { delete_id: applicantId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('tr[data-id="' + applicantId + '"]').remove();
                        checkEmptyTable();
                    } else {
                        alert('Error: ' + (response.error || 'Failed to delete'));
                    }
                },
                error: function() {
                    alert('Error: Failed to communicate with server');
                }
            });
        }
    });

    // Status form submission
    $('#statusForm').submit(function(e) {
        e.preventDefault();
        var applicantId = $('#applicantId').val();
        var status = $('#statusSelect').val();
        var notes = $('#notes').val();
        
        $.ajax({
            url: 'resume.php',
            type: 'POST',
            data: {
                update_id: applicantId,
                status: status,
                notes: notes
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the status in the table
                    var statusCell = $('tr[data-id="' + applicantId + '"] .status-cell');
                    statusCell.html('<span class="status-badge status-' + status + '">' + status + '</span>');
                    
                    // Update the notes preview
                    var notesPreview = notes ? notes.substring(0, 20) + '...' : 'No notes';
                    $('tr[data-id="' + applicantId + '"] .notes-preview').text(notesPreview);
                    
                    $('#editModal').hide();
                } else {
                    alert('Error: ' + (response.error || 'Failed to update'));
                }
            },
            error: function() {
                alert('Error: Failed to communicate with server');
            }
        });
    });

    // Check if table is empty and show message
    function checkEmptyTable() {
        if ($('#resumeTableBody tr:not(.no-data)').length === 0) {
            $('#resumeTableBody').html('<tr><td colspan="12" class="no-data">No applications found</td></tr>');
        }
    }

    // Filter applications based on search and status
    function filterApplications() {
        var searchText = $('#searchInput').val().toLowerCase();
        var statusFilter = $('#statusFilter').val();
        
        $('#resumeTableBody tr').each(function() {
            var $row = $(this);
            if ($row.hasClass('no-data')) return true;
            
            var rowText = $row.text().toLowerCase();
            var rowStatus = $row.find('.status-badge').text();
            
            var matchesSearch = searchText === '' || rowText.indexOf(searchText) > -1;
            var matchesStatus = statusFilter === '' || rowStatus === statusFilter;
            
            if (matchesSearch && matchesStatus) {
                $row.show();
            } else {
                $row.hide();
            }
        });
        
        // Check if any rows are visible
        var visibleRows = $('#resumeTableBody tr:visible:not(.no-data)').length;
        if (visibleRows === 0) {
            if ($('#resumeTableBody .no-data').length === 0) {
                $('#resumeTableBody').append('<tr><td colspan="12" class="no-data">No matching applications found</td></tr>');
            }
        } else {
            $('#resumeTableBody .no-data').remove();
        }
    }

    // View notes button click
    $(document).on('click', '.view-notes', function() {
        var applicantId = $(this).data('id');
        
        $.get('resume.php?view_id=' + applicantId, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                var notesContent = data.notes || 'No notes available';
                $('#notesContent').text(notesContent);
                $('#notesModal').show();
            }
        }, 'json');
    });

    // View questions button click
    $(document).on('click', '.view-questions-link', function(e) {
        e.preventDefault();
        var applicantId = $(this).data('id');
        
        $.get('resume.php?view_id=' + applicantId, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                var questionsHtml = `
                    <div class="question-item">
                        <h4>Why do you want to join our company?</h4>
                        <p>${data.q1 || 'No answer provided'}</p>
                    </div>
                    <div class="question-item">
                        <h4>What skills can you bring to our team?</h4>
                        <p>${data.q2 || 'No answer provided'}</p>
                    </div>
                    <div class="question-item">
                        <h4>Describe a challenging project you worked on</h4>
                        <p>${data.q3 || 'No answer provided'}</p>
                    </div>
                    <div class="question-item">
                        <h4>How do you handle teamwork?</h4>
                        <p>${data.q4 || 'No answer provided'}</p>
                    </div>
                    <div class="question-item">
                        <h4>Where do you see yourself in 5 years?</h4>
                        <p>${data.q5 || 'No answer provided'}</p>
                    </div>
                `;
                $('#questionsContent').html(questionsHtml);
                $('#questionsModal').show();
            }
        }, 'json');
    });

    $(document).on('click', '.document-link', function(e) {
    e.preventDefault();
    var docUrl = $(this).attr('data-path'); // Use data-path instead of href
    
    // Check if the path is absolute (starts with http or /)
    if (!docUrl.startsWith('http') && !docUrl.startsWith('/')) {
        // Make sure the path is relative to the root
        if (!docUrl.startsWith('../')) {
            docUrl = '../../' + docUrl; // Adjust based on your folder structure
        }
    }
    
    // Check if file exists before opening
    fetch(docUrl, { method: 'HEAD' })
        .then(response => {
            if (response.ok) {
                window.open(docUrl, '_blank');
            } else {
                alert('Document not found: ' + docUrl);
            }
        })
        .catch(() => {
            alert('Error accessing document: ' + docUrl);
        });
});
   
});
</script>
</body>
</html>