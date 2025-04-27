<?php
include '../connection/Connection.php';
include '../AdminBackEnd/VehicleRunsBE.php';


// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') { 
    // Redirect to login page (not logout!)
    header("Location: ../../../login/login.php");
    exit();
}
// Get vehicle runs data
$vehicleRuns = getVehicleRunsData();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/SAdminsVehicleruns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body>
    <!-- Header Section -->
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
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin') {
    if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
        echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
    } else {
        echo 'Super Admin';
    }
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

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul>
            <li class="dashboard">
                <a href="SuperAdminDashboard.php"><img src="../../Assets/Icon/Analysis.png" alt="Dashboard Icon" class="sidebar-icon">Dashboard</a>
            </li>
            <li class="media-files">
                <a href="media-files.php"><img src="../../Assets/Icon/file.png" alt="Media Files Icon" class="sidebar-icon"> Media Files</a>
            </li>
            <li class="resume">
                <a href="resume.php"><img src="../../Assets/Icon/Resume.png" alt="Resume Icon" class="sidebar-icon">Intern Application</a>
            </li>
            <li class="vehicle-runs">
                <a href="vehicle-runs.php"><img src="../../assets/icon/vruns.png" alt="Vehicle Runs Icon" class="sidebar-icon"> Vehicle Runs</a>
            </li>
            <li class="manage-users">
                <a href="manage-users.php"><img src="../../Assets/Icon/user-management.png" alt="Manage Users Icon" class="sidebar-icon"> Manage Users</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="table-container">
            <h1 class="main-title">Vehicle Runs</h1>
            <br>
            <div class="top-controls">
                <!-- First Line: Search | Date Range | (Upload Case on right) -->
                <div class="first-line-controls">
                    <div class="left-controls">
                        <div class="search-container">
                            <input type="text" class="search-input" placeholder="Search VCTEL">
                        </div>
                        <div class="date-range-container">
                            <div class="date-input">
                                <label for="dateFrom">From:</label>
                                <input type="date" id="dateFrom" class="date-filter">
                            </div>
                            <div class="date-input">
                                <label for="dateTo">To:</label>
                                <input type="date" id="dateTo" class="date-filter">
                            </div>
                        </div>
                    </div>
                    <div class="upload-case-content">
                        <button id="uploadCase" class="action-btn upload-btn">
                            <i class="fas fa-upload"></i> Upload Case
                        </button>
                    </div>
                </div>

                <!-- Second Line: Vehicle Team | Case Type | Clear Filter | (Delete Selected on right) -->
                <div class="second-line-controls">
                    <div class="left-controls">
                        <div class="filter-container">
                            <select id="vehicleTeamFilter" class="filter-dropdown">
                                <option value="" selected disabled hidden>Vehicle Team</option>
                                <option value="Alpha">Alpha</option>
                                <option value="Bravo">Bravo</option>
                                <option value="Charlie">Charlie</option>
                                <option value="Delta">Delta</option>
                            </select>
                        </div>
                        <div class="filter-container">
                            <select id="caseTypeFilter" class="filter-dropdown">
                                <option value="" selected disabled hidden>Case Type</option>
                                <option value="Fire Case">Fire Case</option>
                                <option value="Medical Case">Medical Case</option>
                                <option value="Medical Standby">Medical Standby</option>
                                <option value="Trauma Case">Trauma Case</option>
                                <option value="Patient Transportation">Patient Transportation</option>
                            </select>
                        </div>
                        <button id="clearFilters" class="clear-filters-btn">
                            <i class="fas fa-times x-icon"></i> Clear Filters
                        </button>
                    </div>
                    <div class="delete-selected-content">
                        <button id="deleteSelected" class="action-btn delete-btn">
                            <i class="fas fa-trash-alt"></i> Delete Selected
                        </button>
                    </div>
                </div>
          
                <table>
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAll"> Select all</th>
            <th>Vehicle Team</th>
            <th>Case Type</th>
            <th>Transport Officer</th>
            <th>Emergency Responders</th>
            <th>Location</th>
            <th>Dispatch Time</th>
            <th>Back to Base Time</th>
            <th>Case Image</th>
            <th>Case Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($vehicleRuns)): ?>
            <?php foreach ($vehicleRuns as $run): ?>
                <?php
                // Format dispatch time with validation
                $dispatchTime = 'N/A';
                if (!empty($run['dispatch_time']) && $run['dispatch_time'] != '0000-00-00 00:00:00') {
                    $dispatchTime = date('m/d/Y H:i', strtotime($run['dispatch_time']));
                }
                
                // Handle case image
                $imageHtml = 'No image';
                if (!empty($run['case_image'])) {
                    $filename = basename($run['case_image']);
                    $imageHtml = '<a href="../../../' . htmlspecialchars($run['case_image']) . '" target="_blank" class="image-preview-link" data-filename="' . htmlspecialchars($filename) . '">View Image</a>';
                }
                
                // Handle transport officer
                $transportOfficer = !empty($run['transport_officer']) ? htmlspecialchars($run['transport_officer']) : 'N/A';
                ?>
                <tr>
                    <td><input type="checkbox" class="case-checkbox" value="<?= htmlspecialchars($run['id']) ?>"></td>
                    <td><?= htmlspecialchars($run['vehicle_team']) ?></td>
                    <td><?= htmlspecialchars($run['case_type']) ?></td>
                    <td><?= $transportOfficer ?></td>
                    <td><?= htmlspecialchars($run['emergency_responders']) ?></td>
                    <td><?= htmlspecialchars($run['location']) ?></td>
                    <td><?= $dispatchTime ?></td>
                    <td>
                        <?php if (!empty($run['back_to_base_time']) && $run['back_to_base_time'] != '0000-00-00 00:00:00'): ?>
                            <?= date('m/d/Y H:i', strtotime($run['back_to_base_time'])) ?>
                        <?php else: ?>
                            <button class="set-btb-btn" data-id="<?= htmlspecialchars($run['id']) ?>">Set BTB Time</button>
                        <?php endif; ?>
                    </td>
                    <td><?= $imageHtml ?></td>
                    <td class="action-buttons">
                        <button class="edit-btn" data-id="<?= htmlspecialchars($run['id']) ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="delete-btn" data-id="<?= htmlspecialchars($run['id']) ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="no-data">No vehicle runs found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
        </div>
    </div>
    </div>
    

    <!-- Upload Case Modal -->
    <div id="uploadCaseModal" class="upload-modal-content">
        <div class="upload-modal-inner">
            <div class="upload-modal-header">
                <h3>Update Case</h3>
                <span class="upload-modal-close">&times;</span>
            </div>
            <div class="upload-modal-body">
               <!-- In the upload modal form, remove the Back to Base Time field completely -->
                <form id="caseUploadForm" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vehicleTeam">Vehicle Team</label>
                            <select id="vehicleTeam" name="vehicleTeam" required>
                                <option value="" selected disabled>Select Team</option>
                                <option value="Alpha">Alpha</option>
                                <option value="Bravo">Bravo</option>
                                <option value="Charlie">Charlie</option>
                                <option value="Delta">Delta</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="caseType">Case Type</label>
                            <select id="caseType" name="caseType" required>
                                <option value="" selected disabled>Select Type</option>
                                <option value="Fire Case">Fire Case</option>
                                <option value="Medical Case">Medical Case</option>
                                <option value="Medical Standby">Medical Standby</option>
                                <option value="Trauma Case">Trauma Case</option>
                                <option value="Patient Transportation">Patient Transportation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="transportOfficer">Transport Officer</label>
                            <input type="text" id="transportOfficer" name="transportOfficer" placeholder="Start typing officer name">
                            <div id="officerSuggestions" class="autocomplete-suggestions"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emergencyResponders">Emergency Responders</label>
                        <textarea id="emergencyResponders" name="emergencyResponders" required placeholder="Enter responder names separated by commas"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" required placeholder="Enter case location">
                    </div>

                    <div class="form-group">
                        <label for="dispatchTime">Dispatch Time</label>
                        <input type="datetime-local" id="dispatchTime" name="dispatchTime" required>
                    </div>

                    <div class="form-group">
                        <label>Case Image (Optional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="caseImage" name="caseImage" accept="image/*" style="display: none;">
                            <label for="caseImage" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i> Choose File
                            </label>
                            <span class="file-upload-filename">No file chosen</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" class="submit-btn">Upload Case</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <button onclick="openEditModal(<?= $file['id'] ?>, '<?= htmlspecialchars($file['file_name']) ?>', <?= $file['temperature'] ?? 'null' ?>, <?= $file['water_level'] ?? 'null' ?>, <?= $file['air_quality'] ?? 'null' ?>)">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button onclick="openDeleteModal(<?= $file['id'] ?>, '<?= $file['file_name'] ?>')">
                    <i class="fas fa-trash"></i> Delete
                </button>

    <!-- Edit Case Modal -->
    <div id="editCaseModal" class="upload-modal-content">
        <div class="upload-modal-inner">
            <div class="upload-modal-header">
                <h3>Edit Case</h3>
                <span class="edit-modal-close">&times;</span>
            </div>
            <div class="upload-modal-body">
                <form id="caseEditForm" enctype="multipart/form-data">
                    <input type="hidden" id="editCaseId" name="id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editVehicleTeam">Vehicle Team</label>
                            <select id="editVehicleTeam" name="vehicleTeam" required>
                                <option value="" selected disabled>Select Team</option>
                                <option value="Alpha">Alpha</option>
                                <option value="Bravo">Bravo</option>
                                <option value="Charlie">Charlie</option>
                                <option value="Delta">Delta</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editCaseType">Case Type</label>
                            <select id="editCaseType" name="caseType" required>
                                <option value="" selected disabled>Select Type</option>
                                <option value="Fire Case">Fire Case</option>
                                <option value="Medical Case">Medical Case</option>
                                <option value="Medical Standby">Medical Standby</option>
                                <option value="Trauma Case">Trauma Case</option>
                                <option value="Patient Transportation">Patient Transportation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editTransportOfficer">Transport Officer</label>
                            <input type="text" id="editTransportOfficer" name="transportOfficer" placeholder="Start typing officer name">
                            <div id="editOfficerSuggestions" class="autocomplete-suggestions"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editEmergencyResponders">Emergency Responders</label>
                        <textarea id="editEmergencyResponders" name="emergencyResponders" required placeholder="Enter responder names separated by commas"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="editLocation">Location</label>
                        <input type="text" id="editLocation" name="location" required placeholder="Enter case location">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editDispatchTime">Dispatch Time</label>
                            <input type="datetime-local" id="editDispatchTime" name="dispatchTime" required>
                        </div>
                        <div class="form-group">
    <label for="editBackToBaseTime">
        Back to Base Time 
        <button type="button" class="set-btb-btn-edit" id="setCurrentBTBTime">
            ⏱ Set Current Time
        </button>
    </label>
    <input type="datetime-local" id="editBackToBaseTime" name="backToBaseTime" required>
</div>
                    </div>

                    <div class="form-group">
                        <label>Case Image (Optional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="editCaseImage" name="caseImage" accept="image/*" style="display: none;">
                            <label for="editCaseImage" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i> Choose File
                            </label>
                            <span class="file-upload-filename">No file chosen</span>
                            <div id="currentImageContainer" style="margin-top: 10px;"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="cancel-btn edit-cancel-btn">Cancel</button>
                        <button type="submit" class="submit-btn">Update Case</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<!-- Single Delete Modal -->
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

<!-- Modern Image Removal Confirmation Modal -->
<div id="removeImageConfirmationModal" class="modern-modal" style="display: none;">
    <div class="modal-overlay" onclick="hideImageRemoveModal()"></div>
    <div class="modal-content">
        <button class="modal-close-btn" onclick="hideImageRemoveModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h3 class="modal-title">Confirm Image Removal</h3>
        
        <p class="modal-message">Are you sure you want to permanently remove this case image?</p>
        
        <div class="modal-actions">
            <button id="cancelImageRemove" class="modal-btn secondary-btn">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button id="confirmImageRemove" class="modal-btn danger-btn">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
    </div>
</div>

    <script src="../../js/SAdminVehicleRuns.js"></script>

    <script>
    document.getElementById('setCurrentBTBTime').addEventListener('click', function () {
        const now = new Date();
        const localDatetime = now.toISOString().slice(0, 16);
        document.getElementById('editBackToBaseTime').value = localDatetime;
    });
</script>


</body>



</html>