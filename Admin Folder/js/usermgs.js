
document.addEventListener("DOMContentLoaded", function() {
    // First declare all variables at the top
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const logoutCancel = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');

    // Toggle dropdown visibility
    function toggleDropdown(show = null) {
        if (userDropdown) {
            if (show === null) {
                userDropdown.classList.toggle("show");
            } else {
                userDropdown.classList.toggle("show", show);
            }
        }
    }

    // Close all modals/dropdowns
    function closeAll() {
        if (userDropdown) {
            userDropdown.classList.remove("show");
        }
        if (logoutModal) {
            logoutModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Initialize dropdown - moved before other event listeners
    if (userContainer && userDropdown) {
        userContainer.addEventListener("click", function(event) {
            event.stopPropagation();
            toggleDropdown();
        });
    }

    // Logout Modal Functionality
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            closeAll();
            if (logoutModal) {
                logoutModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    }

    // Modal controls
    if (logoutCancel) {
        logoutCancel.addEventListener('click', closeAll);
    }

    if (logoutConfirm) {
        logoutConfirm.addEventListener('click', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../../login/logout.php';
            
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Close modal when clicking outside
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === logoutModal) {
                closeAll();
            }
        });
    }

    // Close with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAll();
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function(event) {
        if (userDropdown && userDropdown.classList.contains("show")) {
            if (!userContainer.contains(event.target) && !userDropdown.contains(event.target)) {
                toggleDropdown(false);
            }
        }
    });
});



// Manage Users
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    const position = document.getElementById('edit_position').value;
    const role = document.getElementById('edit_role').value;
    
    if (!position || !role) {
        e.preventDefault();
        alert('Please select both position and role');
    }
});

document.addEventListener("DOMContentLoaded", function() {
    // DOM Elements
    const createUserBtn = document.getElementById('createUserBtn');
    const createUserModal = document.getElementById('createUserModal');
    const editUserModal = document.getElementById('editUserModal');
    const createUserForm = document.getElementById('createUserForm');
    const editUserForm = document.getElementById('editUserForm');
    const deleteModal = document.getElementById('deleteModal');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    // State variables
    let allUsers = [];
    let currentUserId = null;

    // Initialize
    loadUsers();
    setupEventListeners();

    function setupEventListeners() {
        // Create User Modal
        createUserBtn.addEventListener('click', openCreateUserModal);
        createUserForm.addEventListener('submit', handleCreateUser);
        document.querySelector('#createUserModal .close').addEventListener('click', () => createUserModal.style.display = 'none');
        document.getElementById('createCancelBtn').addEventListener('click', () => createUserModal.style.display = 'none');
        
        // Edit User Modal
        editUserForm.addEventListener('submit', handleEditUser);
        document.querySelector('#editUserModal .close').addEventListener('click', () => editUserModal.style.display = 'none');
        document.getElementById('editCancelBtn').addEventListener('click', () => editUserModal.style.display = 'none');
        
        // Delete Modal
        document.querySelector('#deleteModal .close').addEventListener('click', () => deleteModal.style.display = 'none');
        document.getElementById('cancelDeleteBtn').addEventListener('click', () => deleteModal.style.display = 'none');
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
        
        // Search functionality
        searchBtn.addEventListener('click', searchUsers);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') searchUsers();
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === createUserModal) createUserModal.style.display = 'none';
            if (event.target === editUserModal) editUserModal.style.display = 'none';
            if (event.target === deleteModal) deleteModal.style.display = 'none';
        });
    }

    function loadUsers() {
        fetch('../AdminBackEnd/ManageUserBE.php?get_users=1')
            .then(response => response.json())
            .then(users => {
                allUsers = users;
                renderUsers(users);
            })
            .catch(error => console.error('Error loading users:', error));
    }

    function renderUsers(users) {
        const tbody = document.getElementById('manage-user');
        tbody.innerHTML = '';
        
        if (users.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `<td colspan="8" class="no-users">No users found</td>`;
            tbody.appendChild(row);
            return;
        }
        
        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.employee_no}</td>
                <td>${user.name}</td>
                <td>${user.position}</td>
                <td>${user.role}</td>
                <td>${user.email}</td>
                <td>••••••••</td>
                <td>${user.pin_code}</td>
                <td></td>
            `;
            
            const actionCell = row.querySelector('td:last-child');
            actionCell.appendChild(createKebabMenu(user.id));
            
            tbody.appendChild(row);
        });
    }

    function createKebabMenu(userId) {
        // Create menu container
        const menuContainer = document.createElement('div');
        menuContainer.className = 'kebab-menu';
        
        // Create kebab button
        const kebabButton = document.createElement('button');
        kebabButton.className = 'kebab-btn';
        kebabButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
        
        // Create dropdown menu
        const dropdownMenu = document.createElement('div');
        dropdownMenu.className = 'kebab-dropdown';
        
        // Create edit button
        const editButton = document.createElement('button');
        editButton.className = 'edit-btn';
        editButton.innerHTML = '<i class="fas fa-edit"></i> Edit';
        editButton.dataset.id = userId;
        
        // Create delete button
        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-btn';
        deleteButton.innerHTML = '<i class="fas fa-trash"></i> Delete';
        deleteButton.dataset.id = userId;
        
        // Append buttons to dropdown
        dropdownMenu.appendChild(editButton);
        dropdownMenu.appendChild(deleteButton);
        
        // Append elements to container
        menuContainer.appendChild(kebabButton);
        menuContainer.appendChild(dropdownMenu);
        
        // Toggle dropdown on button click
        kebabButton.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close all other open dropdowns
            document.querySelectorAll('.kebab-dropdown').forEach(dropdown => {
                if (dropdown !== dropdownMenu) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Toggle this dropdown
            dropdownMenu.classList.toggle('show');
        });
        
        // Edit button functionality
        editButton.addEventListener('click', function(e) {
            e.stopPropagation();
            editUser(userId);
            dropdownMenu.classList.remove('show');
        });
        
        // Delete button functionality
        deleteButton.addEventListener('click', function(e) {
            e.stopPropagation();
            confirmDeleteUser(userId);
            dropdownMenu.classList.remove('show');
        });
        
        // Close dropdown when clicking elsewhere
        document.addEventListener('click', function() {
            dropdownMenu.classList.remove('show');
        });
        
        return menuContainer;
    }

    function openCreateUserModal() {
        // Check admin limit before opening
        checkAdminLimit('create');
        createUserForm.reset();
        createUserModal.style.display = 'block';
    }

    function editUser(userId) {
        const user = allUsers.find(u => u.id == userId);
        if (!user) return;
        
        // Split name into first and last
        const names = user.name.split(' ');
        const firstName = names[0];
        const lastName = names.slice(1).join(' ');
        
        // Fill the form
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_employee_no').value = user.employee_no;
        document.getElementById('edit_first_name').value = firstName;
        document.getElementById('edit_last_name').value = lastName;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_position').value = user.position;
        document.getElementById('edit_role').value = user.role;
        
        // Clear password and pin fields
        document.getElementById('current_password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
        document.getElementById('current_pin').value = '';
        document.getElementById('new_pin').value = '';
        document.getElementById('confirm_pin').value = '';
        
        // Check admin limit
        checkAdminLimit('edit', user.role);
        
        editUserModal.style.display = 'block';
    }

    function confirmDeleteUser(userId) {
        currentUserId = userId;
        deleteModal.style.display = 'block';
    }

    function handleCreateUser(e) {
        e.preventDefault();
        
        const formData = new FormData(createUserForm);
        const role = formData.get('role');
        
        // Client-side validation
        if (role === 'Admin') {
            const adminCount = allUsers.filter(user => user.role === 'Admin').length;
            if (adminCount >= 5) {
                alert('Maximum of 5 admin users allowed');
                return;
            }
        }
        
        // PIN code validation
        const pinCode = formData.get('pin_code');
        if (pinCode && (pinCode.length !== 6 || !/^\d+$/.test(pinCode))) {
            alert('PIN code must be exactly 6 digits');
            return;
        }
        
        fetch('../AdminBackEnd/ManageUserBE.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                createUserModal.style.display = 'none';
                loadUsers();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating user');
        });
    }

    function handleEditUser(e) {
        e.preventDefault();
        
        const formData = new FormData(editUserForm);
        const userId = formData.get('id');
        const role = formData.get('role');
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');
        const newPin = formData.get('new_pin');
        const confirmPin = formData.get('confirm_pin');
        
        // Get the user being edited
        const user = allUsers.find(u => u.id == userId);
        if (!user) return;
        
        // Check admin limit if changing to admin
        if (role === 'Admin' && user.role !== 'Admin') {
            const adminCount = allUsers.filter(u => u.role === 'Admin').length;
            if (adminCount >= 5) {
                alert('Maximum of 5 admin users allowed');
                return;
            }
        }
        
        // Password validation
        if (newPassword || confirmPassword) {
            if (newPassword !== confirmPassword) {
                alert('New password and confirmation do not match');
                return;
            }
        }
        
        // PIN code validation
        if (newPin || confirmPin) {
            if (newPin !== confirmPin) {
                alert('New PIN and confirmation do not match');
                return;
            }
            if (newPin && (newPin.length !== 6 || !/^\d+$/.test(newPin))) {
                alert('PIN code must be exactly 6 digits');
                return;
            }
        }
        
        // Remove empty fields
        if (!newPassword) {
            formData.delete('new_password');
            formData.delete('confirm_password');
            formData.delete('current_password');
        }
        
        if (!newPin) {
            formData.delete('new_pin');
            formData.delete('confirm_pin');
            formData.delete('current_pin');
        }
        
        fetch('../AdminBackEnd/ManageUserBE.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                editUserModal.style.display = 'none';
                loadUsers();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating user');
        });
    }

    function confirmDelete() {
        if (!currentUserId) return;
        
        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('id', currentUserId);
        
        fetch('../AdminBackEnd/ManageUserBE.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadUsers();
            } else {
                alert(data.message);
            }
            deleteModal.style.display = 'none';
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            alert('Error deleting user');
            deleteModal.style.display = 'none';
        });
    }

    function searchUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        if (!searchTerm) {
            renderUsers(allUsers);
            return;
        }
        
        const filteredUsers = allUsers.filter(user => 
            user.employee_no.toLowerCase().includes(searchTerm) ||
            user.name.toLowerCase().includes(searchTerm) ||
            user.position.toLowerCase().includes(searchTerm) ||
            user.role.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm)
        );
        
        renderUsers(filteredUsers);
    }

    function checkAdminLimit(context = 'create', currentRole = null) {
        const adminCount = allUsers.filter(user => user.role === 'Admin').length;
        const limitReached = adminCount >= 5;
        
        if (context === 'create') {
            const adminLimitMessage = document.getElementById('createAdminLimitMessage');
            const saveBtn = createUserForm.querySelector('.btn.save');
            
            if (limitReached) {
                document.getElementById('create_role').value = 'User';
                adminLimitMessage.style.display = 'block';
                saveBtn.disabled = true;
            } else {
                adminLimitMessage.style.display = 'none';
                saveBtn.disabled = false;
            }
        } else if (context === 'edit') {
            const adminLimitMessage = document.getElementById('editAdminLimitMessage');
            const saveBtn = editUserForm.querySelector('.btn.save');
            const roleSelect = document.getElementById('edit_role');
            
            if (limitReached && currentRole !== 'Admin') {
                roleSelect.value = currentRole;
                adminLimitMessage.style.display = 'block';
                saveBtn.disabled = true;
            } else {
                adminLimitMessage.style.display = 'none';
                saveBtn.disabled = false;
            }
        }
    }
});





//Save
document.querySelectorAll('.save-container').forEach(button => {
    button.addEventListener('click', async function () {
        const containerType = this.dataset.container;
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);
        const userId = formData.get('id');
        const button = this;

        // Only include relevant fields for this container
        const containerData = new FormData();
        containerData.append('action', 'update_user_partial');
        containerData.append('id', userId);
        containerData.append('container_type', containerType);

        // Validate fields based on container type
        let isValid = true;
        switch (containerType) {
            case 'profile':
                containerData.append('employee_no', formData.get('employee_no'));
                containerData.append('first_name', formData.get('first_name'));
                containerData.append('last_name', formData.get('last_name'));
                containerData.append('email', formData.get('email'));
                
                // For profile, all fields are required but should already be populated in edit mode
                if (!formData.get('employee_no') || !formData.get('first_name') || 
                    !formData.get('last_name') || !formData.get('email')) {
                    alert('All profile fields are required');
                    isValid = false;
                }
                break;

            case 'designation':
                containerData.append('position', formData.get('position'));
                containerData.append('role', formData.get('role'));
                
                // For designation, both fields are required but should be pre-populated
                if (!formData.get('position') || !formData.get('role')) {
                    alert('Both position and role are required');
                    isValid = false;
                }
                break;

            case 'password':
                // For password, only validate if new password fields are filled
                const currentPassword = formData.get('current_password');
                const newPassword = formData.get('new_password');
                const confirmPassword = formData.get('confirm_password');
                
                if (newPassword || confirmPassword) {
                    if (!currentPassword) {
                        alert('Current password is required to change password');
                        isValid = false;
                    } else if (!newPassword) {
                        alert('New password is required');
                        isValid = false;
                    } else if (!confirmPassword) {
                        alert('Please confirm your new password');
                        isValid = false;
                    } else if (newPassword !== confirmPassword) {
                        alert('New password and confirmation do not match');
                        isValid = false;
                    } else {
                        containerData.append('current_password', currentPassword);
                        containerData.append('new_password', newPassword);
                        containerData.append('confirm_password', confirmPassword);
                    }
                } else {
                    // No password change requested
                    isValid = true;
                }
                break;

            case 'pincode':
                // For pincode, only validate if new pin fields are filled
                const currentPin = formData.get('current_pin');
                const newPin = formData.get('new_pin');
                const confirmPin = formData.get('confirm_pin');
                
                if (newPin || confirmPin) {
                    if (!currentPin) {
                        alert('Current PIN is required to change PIN');
                        isValid = false;
                    } else if (!newPin) {
                        alert('New PIN is required');
                        isValid = false;
                    } else if (!confirmPin) {
                        alert('Please confirm your new PIN');
                        isValid = false;
                    } else if (newPin !== confirmPin) {
                        alert('New PIN and confirmation do not match');
                        isValid = false;
                    } else if (newPin.length !== 6 || !/^\d+$/.test(newPin)) {
                        alert('PIN code must be exactly 6 digits');
                        isValid = false;
                    } else {
                        containerData.append('current_pin', currentPin);
                        containerData.append('new_pin', newPin);
                        containerData.append('confirm_pin', confirmPin);
                    }
                } else {
                    // No PIN change requested
                    isValid = true;
                }
                break;
        }

        if (!isValid) return;

        // Show loading state
        const originalText = button.textContent;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        button.classList.add('btn-saving');

        try {
            const [response] = await Promise.all([
                fetch('../AdminBackEnd/ManageUserBE.php', {
                    method: 'POST',
                    body: containerData
                }),
                new Promise(resolve => setTimeout(resolve, 800)) // Minimum loading time
            ]);

            const responseText = await response.text();
            console.log("Raw Response:", responseText);

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                if (responseText.toLowerCase().includes('success')) {
                    data = { status: 'success', message: 'Operation completed successfully' };
                } else {
                    throw new Error("Invalid server response");
                }
            }

            // Restore button state
            button.disabled = false;
            button.textContent = originalText;
            button.classList.remove('btn-saving');

            if (data.status === 'success') {
                showSuccessModal(
                    'editSuccessModal', 
                    'uploadSuccessMessage', 
                    `${containerType.charAt(0).toUpperCase() + containerType.slice(1)} updated successfully`
                );

                if (containerType === 'designation') {
                    loadUsers();
                }
            } else {
                if (containerType === 'designation' && data.message.includes('Maximum of 5 admin users')) {
                    const adminLimitMessage = document.getElementById('editAdminLimitMessage');
                    adminLimitMessage.style.display = 'block';
                    adminLimitMessage.textContent = data.message;
                    
                    setTimeout(() => {
                        adminLimitMessage.style.display = 'none';
                    }, 5000);
                }
                showErrorModal(data.message || 'An error occurred');
            }
        } catch (error) {
            // Restore button state
            button.disabled = false;
            button.textContent = originalText;
            button.classList.remove('btn-saving');
            
            showErrorModal(error.message || 'An error occurred while saving. Please try again.');
            console.error('Error:', error);
        }
    });
});

// Password validation function
function validatePasswordFields() {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!currentPassword) {
        alert('Current password is required');
        return false;
    }

    if (newPassword || confirmPassword) {
        if (!newPassword) {
            alert('New password is required');
            return false;
        }

        if (!confirmPassword) {
            alert('Please confirm your new password');
            return false;
        }

        if (newPassword !== confirmPassword) {
            alert('New password and confirmation do not match');
            return false;
        }
    }

    return true;
}

// PIN validation function
function validatePinFields() {
    const currentPin = document.getElementById('current_pin').value;
    const newPin = document.getElementById('new_pin').value;
    const confirmPin = document.getElementById('confirm_pin').value;

    if (!currentPin) {
        alert('Current PIN is required');
        return false;
    }

    if (newPin || confirmPin) {
        if (!newPin) {
            alert('New PIN is required');
            return false;
        }

        if (!confirmPin) {
            alert('Please confirm your new PIN');
            return false;
        }

        if (newPin !== confirmPin) {
            alert('New PIN and confirmation do not match');
            return false;
        }

        if (newPin.length !== 6 || !/^\d+$/.test(newPin)) {
            alert('PIN code must be exactly 6 digits');
            return false;
        }
    }

    return true;
}

// Function to show success modal
function showSuccessModal(modalId, messageId, message) {
    const modal = document.getElementById(modalId);
    const messageElement = document.getElementById(messageId);
    messageElement.textContent = message;
    
    // Show modal with animation
    modal.style.display = 'block';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.style.transform = 'translate(-50%, -50%) scale(1)';
    }, 10);

    // Auto-hide after 3 seconds
    setTimeout(() => {
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%, -45%) scale(0.9)';
        setTimeout(() => modal.style.display = 'none', 300);
    }, 3000);
}

// Function to show error modal
function showErrorModal(message) {
    const modal = document.getElementById('errorModal');
    const messageElement = document.getElementById('errorMessage');
    messageElement.textContent = message;
    
    // Show modal
    modal.style.display = 'block';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.style.transform = 'translate(-50%, -50%) scale(1)';
    }, 10);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        modal.style.opacity = '0';
        modal.style.transform = 'translate(-50%, -45%) scale(0.9)';
        setTimeout(() => modal.style.display = 'none', 300);
    }, 5000);
}

// Function to close modals
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.opacity = '0';
    modal.style.transform = 'translate(-50%, -45%) scale(0.9)';
    setTimeout(() => modal.style.display = 'none', 300);
}

