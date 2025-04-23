<?php
include '../connection/Connection.php';
include '../AdminBackEnd/ManageUserBE.php';



// Corrected check (using 'role' instead of 'user_role')
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Super Admin')) {
    // Redirect to login page (not logout!)
    header("Location: ../../../login/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="is-super-admin" content="true">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEDOC FIVERES</title>
    <link rel="stylesheet" href="../../Css/SAManageUsers.css">
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

    <div class="main-content">
        <div class="table-container">
            <h1 class="main-title">Manage Users</h1>
            <br>
            <div class="top-controls">
    <div class="search-filter-container">
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search users...">
            <button id="searchBtn"><i class="fas fa-search"></i></button>
        </div>
        
        <div class="filter-container">
            <select id="roleFilter" class="role-filter">
                <option value="">All Roles</option>
                <option value="Super Admin">Super Admin</option>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>
        </div>
    </div>

    <div class="folder-container">
        <button class="create-folder-btn" id="createUserBtn">Create Users</button>
    </div>
</div>

            <table>
            <thead>
    <tr>
        <th>Employee No.</th>
        <th>Name</th>
        <th>Position</th>
        <th>Role</th>
        <th>Email</th>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
            <th>Password</th>
        <?php endif; ?>
        <th>Pin-code</th>
        <th>Action</th>
    </tr>
</thead>
                <tbody id="manage-user">
                    <!-- Users will be loaded here dynamically -->
                </tbody>
                
            </table>
            <div id="paginationControls" class="pagination-controls"></div>
        </div>
    </div>
    


    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create New User</h2>
            <form id="createUserForm">
                <input type="hidden" name="action" value="create_user">

                <div class="form-container">
                    <div class="profile-container">
                        <h3>Profile Information</h3>
                        <div class="form-group">
                            <label for="create_employee_no">Employee No.</label>
                            <input type="text" id="create_employee_no" name="employee_no" required>
                        </div>
                        <div class="form-group">
                            <label for="create_first_name">First Name</label>
                            <input type="text" id="create_first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="create_last_name">Last Name</label>
                            <input type="text" id="create_last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="create_email">Email</label>
                            <input type="email" id="create_email" name="email" required>
                        </div>
                    </div>

                    <div class="designation-container">
                        <h3>Designation</h3>
                        <div class="form-group">
                            <label for="create_position">Position</label>
                            <select id="create_position" name="position" required>
                                <option value="" selected disabled>Choose position...</option>
                                <option value="Head">Head</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Employee">Employee</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" id="create_other_position" name="other_position" style="display: none; margin-top: 5px; width: 73%;" placeholder="Please specify position">
                        </div>
                        <div class="form-group">
                            <label for="create_role">Role</label>
                            <select id="create_role" name="role" required>
    <option value="" selected disabled>Choose role...</option>
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
        <option value="Super Admin">Super Admin</option>
    <?php endif; ?>
    <option value="Admin">Admin</option>
    <option value="User">User</option>
</select>
                            <small id="createAdminLimitMessage" style="color: red; display: none;">Maximum of 5 admin users reached</small>
                        </div>
                    </div>

                    <div class="password-container">
                        <h3>Password</h3>
                        <div class="form-group">
                            <label for="create_password">Password</label>
                            <input type="password" id="create_password" name="password" required>
                        </div>
                    </div>

                    <div class="pincode-container">
                        <h3>PIN Code</h3>
                        <div class="form-group">
                            <label for="create_pin_code">6-Digit PIN</label>
                            <input type="text" id="create_pin_code" name="pin_code" maxlength="6" pattern="\d{6}" title="6-digit pin code">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="createCancelBtn" class="btn cancel">Cancel</button>
                    <button type="submit" class="btn save">Create User</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit User</h2>
            <form id="editUserForm">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" id="edit_user_id">

                <div class="form-container">
                    <div class="profile-container">
                        <div class="container-header">
                            <h3>Profile Information</h3>

                        </div>
                        <div class="form-group">
                            <label for="edit_employee_no">Employee No.</label>
                            <input type="text" id="edit_employee_no" name="employee_no" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_first_name">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>
                        <button type="button" class="btn save-container" data-container="profile">Save Profile</button>
                    </div>

                    <div class="designation-container">
                        <div class="container-header">
                            <h3>Designation</h3>

                        </div>

                         <div class="form-group">
                            <label for="edit_position">Position</label>
                            <select id="edit_position" name="position" required>
                                <option value="Head">Head</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Employee">Employee</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" id="edit_other_position" name="other_position" style="display: none; margin-top: 5px; width: 73%;" placeholder="Please specify position">
                        </div>
                        <div class="form-group">
                            <label for="edit_role">Role</label>
                            <select id="edit_role" name="role" required>
                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
                                    <option value="Super Admin">Super Admin</option>
                                <?php endif; ?>
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                            </select>
                            <small id="editAdminLimitMessage" style="color: red; display: none;">Maximum of 5 admin users reached</small>
                        </div>
                        <button type="button" class="btn save-container" data-container="designation">Save Designation</button>
                    </div>

                    <div class="password-container">
                        <div class="container-header">
                            <h3>Update Password</h3>

                        </div>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        <button type="button" class="btn save-container" data-container="password">Save Password</button>
                    </div>

                    <div class="pincode-container">
                        <div class="container-header">
                            <h3>Update PIN Code</h3>

                        </div>
                        <div class="form-group">
                            <label for="current_pin">Current 6-Digit PIN</label>
                            <input type="text" id="current_pin" name="current_pin" maxlength="6">
                        </div>
                        <div class="form-group">
                            <label for="new_pin">New 6-Digit PIN</label>
                            <input type="text" id="new_pin" name="new_pin" maxlength="6">
                        </div>
                        <div class="form-group">
                            <label for="confirm_pin">Confirm New 6-Digit PIN</label>
                            <input type="text" id="confirm_pin" name="confirm_pin" maxlength="6">
                        </div>
                        <button type="button" class="btn save-container" data-container="pincode">Save PIN</button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="editCancelBtn" class="btn cancel">Cancel</button>
                    <button type="submit" class="btn save">Update User</button>
                </div>
            </form>
        </div>
        
    </div>


    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
    <div class="custom-modal-content">
        <span class="close"></span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
        
        <!-- PIN verification section -->
        <div id="pinVerificationSection" style="margin-top: 20px;">
            <label for="deletePinCode">Enter your PIN code to confirm:</label>
            <input type="password" id="deletePinCode" name="deletePinCode" maxlength="6" pattern="\d{6}" 
                   title="6-digit pin code" placeholder="Enter 6-digit PIN" required>
            <p id="pinError" style="color: red; display: none;">Incorrect PIN code</p>
        </div>
        
        <div class="form-actions">
            <button type="button" id="cancelDeleteBtn" class="btn cancel">Cancel</button>
            <button type="button" id="confirmDeleteBtn" class="btn delete">Delete</button>
        </div>
    </div>
</div>

    <!-- Edit Success Modal -->
    <div id="editSuccessModal" class="editsuccess-modal">
        <div class="success-modal-content">
            <span class="successclose" onclick="closeModal('editSuccessModal')">&times;</span>
            <h2 id="uploadSuccessMessage">User Updated Successfully</h2>
        </div>
    </div>

    <!-- Delete Success Modal -->
    <div id="deleteSuccessModal" class="deletesuccess-modal">
        <div class="success-modal-content">
            <span class="successclose" onclick="closeModal('deleteSuccessModal')">&times;</span>
            <h2 id="deleteSuccessMessage">Deleted Successfully</h2>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="editsuccess-modal">
        <div class="success-modal-content">
            <span class="successclose" onclick="closeModal('errorModal')">&times;</span>
            <h2 id="errorMessage">Error occurred</h2>
        </div>
    </div>

    <script src="../../js/SAmanageuser.js"></script>
</body>

</html>