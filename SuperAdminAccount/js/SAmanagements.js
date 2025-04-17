document.addEventListener("DOMContentLoaded", function() {
    // First declare all variables at the top
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const logoutCancel = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');

    // Position "Other" option elements
    const createPositionSelect = document.getElementById('create_position');
    const createOtherPositionInput = document.getElementById('create_other_position');
    const editPositionSelect = document.getElementById('edit_position');
    const editOtherPositionInput = document.getElementById('edit_other_position');

    // Check if current user is Super Admin
    const isSuperAdmin = document.querySelector('meta[name="is-super-admin"]')?.content === 'true';
   
    function setupPasswordVisibilityToggles() {
        const isSuperAdmin = document.querySelector('meta[name="is-super-admin"]')?.content === 'true';
        if (!isSuperAdmin) return;
    
        document.querySelectorAll('.password-cell, .pincode-cell').forEach(cell => {
            const originalValue = cell.dataset.originalValue || cell.textContent.trim();
            cell.dataset.originalValue = originalValue; // Store original value in data attribute
            
            if (originalValue !== 'N/A') {
                const toggle = document.createElement('span');
                toggle.className = 'password-toggle';
                toggle.innerHTML = `
                    <svg class="eye-icon" viewBox="0 0 24 24">
                        <path class="eye-open" d="M12 9a3 3 0 0 1 3 3 3 3 0 0 1-3 3 3 3 0 0 1-3-3 3 3 0 0 1 3-3m0-4.5c5 0 9.27 3.11 11 7.5-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12c1.73-4.39 6-7.5 11-7.5z"/>
                        <path class="eye-closed" d="M11.83 9L15 12.16V12a3 3 0 0 0-3-3h-.17m-4.3.8l1.55 1.55c-.05.21-.08.42-.08.65a3 3 0 0 0 3 3c.22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53a5 5 0 0 1-5-5c0-.79.2-1.53.53-2.2M2 4.27l2.28 2.28.45.45C3.08 8.3 1.78 10 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.43.42L19.73 22 21 20.73 3.27 3M12 7a5 5 0 0 1 5 5c0 .64-.13 1.26-.36 1.82l2.93 2.93c1.5-1.25 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-4 .7l2.17 2.15C10.74 7.13 11.35 7 12 7z" style="display:none"/>
                    </svg>
                `;
                
                // Initially show masked value
                cell.textContent = cell.classList.contains('password-cell') ? '••••••••' : '••••••';
                
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const icon = this.querySelector('.eye-icon');
                    const eyeOpen = icon.querySelector('.eye-open');
                    const eyeClosed = icon.querySelector('.eye-closed');
                    
                    if (this.classList.contains('visible')) {
                        // Hide the password
                        eyeOpen.style.display = 'block';
                        eyeClosed.style.display = 'none';
                        this.classList.remove('visible');
                        cell.textContent = cell.classList.contains('password-cell') ? '••••••••' : '••••••';
                    } else {
                        // Show the password
                        eyeOpen.style.display = 'none';
                        eyeClosed.style.display = 'block';
                        this.classList.add('visible');
                        cell.textContent = originalValue;
                        
                        // Auto-hide after 5 seconds
                        setTimeout(() => {
                            if (this.classList.contains('visible')) {
                                eyeOpen.style.display = 'block';
                                eyeClosed.style.display = 'none';
                                this.classList.remove('visible');
                                cell.textContent = cell.classList.contains('password-cell') ? '••••••••' : '••••••';
                            }
                        }, 500);
                    }
                });
                
                cell.appendChild(toggle);
            }
        });
    }
    
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

    // Manage Users Section
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
    if (createUserBtn || editUserForm) {
        loadUsers();
        setupEventListeners();
    }

    // Handle position "Other" option in create modal
    if (createPositionSelect && createOtherPositionInput) {
        createPositionSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                createOtherPositionInput.style.display = 'block';
                createOtherPositionInput.required = true;
            } else {
                createOtherPositionInput.style.display = 'none';
                createOtherPositionInput.required = false;
                createOtherPositionInput.value = '';
            }
        });
    }

    // Handle position "Other" option in edit modal
    if (editPositionSelect && editOtherPositionInput) {
        editPositionSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                editOtherPositionInput.style.display = 'block';
                editOtherPositionInput.required = true;
            } else {
                editOtherPositionInput.style.display = 'none';
                editOtherPositionInput.required = false;
                editOtherPositionInput.value = '';
            }
        });
    }

    function setupEventListeners() {
        // Create User Modal
        if (createUserBtn) {
            createUserBtn.addEventListener('click', openCreateUserModal);
        }
        if (createUserForm) {
            createUserForm.addEventListener('submit', handleCreateUser);
            document.querySelector('#createUserModal .close')?.addEventListener('click', () => createUserModal.style.display = 'none');
            document.getElementById('createCancelBtn')?.addEventListener('click', () => createUserModal.style.display = 'none');
        }
        
        // Edit User Modal
        if (editUserForm) {
            editUserForm.addEventListener('submit', handleEditUser);
            document.querySelector('#editUserModal .close')?.addEventListener('click', () => editUserModal.style.display = 'none');
            document.getElementById('editCancelBtn')?.addEventListener('click', () => editUserModal.style.display = 'none');
            
            // Add save container event listeners
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
                            
                            if (!formData.get('employee_no') || !formData.get('first_name') || 
                                !formData.get('last_name') || !formData.get('email')) {
                                showErrorModal('All profile fields are required');
                                isValid = false;
                            }
                            break;

                        case 'designation':
                            const position = formData.get('position');
                            const otherPosition = formData.get('other_position');
                            
                            // Use other position if "Other" is selected
                            if (position === 'Other') {
                                if (!otherPosition) {
                                    showErrorModal('Please specify the position');
                                    isValid = false;
                                    break;
                                }
                                containerData.append('position', otherPosition);
                            } else {
                                containerData.append('position', position);
                            }
                            
                            containerData.append('role', formData.get('role'));
                            
                            if (!position || !formData.get('role')) {
                                showErrorModal('Both position and role are required');
                                isValid = false;
                            }
                            break;

                       // In the containerType === 'password' case:
                            case 'password':
                                const currentPassword = formData.get('current_password');
                                const newPassword = formData.get('new_password');
                                const confirmPassword = formData.get('confirm_password');
                                
                                if (newPassword || confirmPassword) {
                                    if (newPassword !== confirmPassword) {
                                        showErrorModal('New password and confirmation do not match');
                                        isValid = false;
                                    }
                                    
                                    // Only validate current password if not Super Admin
                                    if (!isSuperAdmin && !currentPassword) {
                                        showErrorModal('Current password is required to change password');
                                        isValid = false;
                                    } else if (!newPassword) {
                                        showErrorModal('New password is required');
                                        isValid = false;
                                    } else if (!confirmPassword) {
                                        showErrorModal('Please confirm your new password');
                                        isValid = false;
                                    } else {
                                        if (!isSuperAdmin) {
                                            containerData.append('current_password', currentPassword);
                                        }
                                        containerData.append('new_password', newPassword);
                                        containerData.append('confirm_password', confirmPassword);
                                    }
                                } else {
                                    isValid = true;
                                }
                                break;

                        // In the containerType === 'pincode' case:
                            case 'pincode':
                                const currentPin = formData.get('current_pin');
                                const newPin = formData.get('new_pin');
                                const confirmPin = formData.get('confirm_pin');
                                
                                if (newPin || confirmPin) {
                                    if (newPin !== confirmPin) {
                                        showErrorModal('New PIN and confirmation do not match');
                                        isValid = false;
                                    } else if (newPin && (newPin.length !== 6 || !/^\d+$/.test(newPin))) {
                                        showErrorModal('PIN code must be exactly 6 digits');
                                        isValid = false;
                                    }
                                    
                                    // Only validate current PIN if not Super Admin
                                    if (!isSuperAdmin && !currentPin) {
                                        showErrorModal('Current PIN is required to change PIN');
                                        isValid = false;
                                    } else if (!newPin) {
                                        showErrorModal('New PIN is required');
                                        isValid = false;
                                    } else if (!confirmPin) {
                                        showErrorModal('Please confirm your new PIN');
                                        isValid = false;
                                    } else {
                                        if (!isSuperAdmin) {
                                            containerData.append('current_pin', currentPin);
                                        }
                                        containerData.append('new_pin', newPin);
                                        containerData.append('confirm_pin', confirmPin);
                                    }
                                } else {
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
                                if (adminLimitMessage) {
                                    adminLimitMessage.style.display = 'block';
                                    adminLimitMessage.textContent = data.message;
                                    
                                    setTimeout(() => {
                                        adminLimitMessage.style.display = 'none';
                                    }, 500);
                                }
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
        }

                    
                    // Add this to your setupEventListeners function
                    document.querySelectorAll('.update-pin-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const userId = this.dataset.userId;
                            openPinModal(userId);
                        });
                    });

                    // Add this to your createActionButtons function (where you create the action buttons for each user)
                    const updatePinBtn = document.createElement('button');
                    updatePinBtn.className = 'update-pin-btn';
                    updatePinBtn.dataset.userId = user.id;
                    updatePinBtn.innerHTML = '<i class="fas fa-lock"></i> Update PIN';
                    updatePinBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        openPinModal(this.dataset.userId);
                    });
                    actionCell.appendChild(updatePinBtn);
        
        // Delete Modal
        if (deleteModal) {
            document.querySelector('#deleteModal .close')?.addEventListener('click', () => deleteModal.style.display = 'none');
            document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => deleteModal.style.display = 'none');
            document.getElementById('confirmDeleteBtn')?.addEventListener('click', confirmDelete);
        }
        
        // Search functionality
        if (searchBtn && searchInput) {
            searchBtn.addEventListener('click', searchUsers);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') searchUsers();
            });
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (createUserModal && event.target === createUserModal) createUserModal.style.display = 'none';
            if (editUserModal && event.target === editUserModal) editUserModal.style.display = 'none';
            if (deleteModal && event.target === deleteModal) deleteModal.style.display = 'none';
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
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (users.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `<td colspan="8" class="no-users">No users found</td>`;
            tbody.appendChild(row);
            return;
        }
        
        users.forEach(user => {
            const row = document.createElement('tr');
            
            // Add special class for Super Admin
            const rowClass = user.role === 'Super Admin' ? 'super-admin-row' : '';
            row.className = rowClass;
            
            row.innerHTML = `
                <td>${user.employee_no}</td>
                <td>${user.name} ${user.role === 'Super Admin' ? '<span class="super-admin-badge">Super Admin</span>' : ''}</td>
                <td>${user.position}</td>
                <td>${user.role}</td>
                <td>${user.email}</td>
                <td class="password-cell">••••••••</td>
                <td class="pincode-cell">••••••</td>
                <td></td>
            `;
            
            // Store original values in data attributes
            const passwordCell = row.querySelector('.password-cell');
            const pinCell = row.querySelector('.pincode-cell');
            passwordCell.dataset.password = user.password || 'N/A';
            pinCell.dataset.pincode = user.pin_code || 'N/A';
            
            const actionCell = row.querySelector('td:last-child');
            actionCell.appendChild(createActionButtons(user.id, user.role));
            
            tbody.appendChild(row);
        });
    }

    function createActionButtons(userId, userRole) {
        // Create container for both buttons
        const container = document.createElement('div');
        container.className = 'action-buttons-container';
        
        // Add show credentials button
        const showCredsBtn = document.createElement('button');
        showCredsBtn.className = 'show-creds-btn';
        showCredsBtn.title = 'Show Credentials';
        showCredsBtn.innerHTML = '<i class="fas fa-key"></i>';
        
        showCredsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const row = this.closest('tr');
            const passwordCell = row.querySelector('.password-cell');
            const pinCell = row.querySelector('.pincode-cell');
            
            if (passwordCell.classList.contains('visible')) {
                // Hide credentials
                passwordCell.textContent = '••••••••';
                pinCell.textContent = '••••••';
                passwordCell.classList.remove('visible');
                pinCell.classList.remove('visible');
                this.innerHTML = '<i class="fas fa-key"></i>';
                this.title = 'Show Credentials';
            } else {
                // Show credentials
                passwordCell.textContent = passwordCell.dataset.password;
                pinCell.textContent = pinCell.dataset.pincode;
                passwordCell.classList.add('visible');
                pinCell.classList.add('visible');
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                this.title = 'Hide Credentials';
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    if (passwordCell.classList.contains('visible')) {
                        passwordCell.textContent = '••••••••';
                        pinCell.textContent = '••••••';
                        passwordCell.classList.remove('visible');
                        pinCell.classList.remove('visible');
                        this.innerHTML = '<i class="fas fa-key"></i>';
                        this.title = 'Show Credentials';
                    }
                }, 5000);
            }
        });
        
        container.appendChild(showCredsBtn);
        
        // Add kebab menu
        container.appendChild(createKebabMenu(userId, userRole));
        
        return container;
    }

    function createKebabMenu(userId, userRole) {
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
        
        // Disable delete for Super Admin unless current user is Super Admin
        if (userRole === 'Super Admin' && !isSuperAdmin) {
            deleteButton.disabled = true;
            deleteButton.title = 'Only Super Admin can delete Super Admin accounts';
            deleteButton.classList.add('disabled-action');
        }
        
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
            if (!deleteButton.disabled) {
                confirmDeleteUser(userId);
                dropdownMenu.classList.remove('show');
            }
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
        if (createUserForm) createUserForm.reset();
        
        // Set role options based on current user's role
        const roleSelect = document.getElementById('create_role');
        if (roleSelect) {
            // Clear existing options
            roleSelect.innerHTML = '';
            
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Choose role...';
            defaultOption.selected = true;
            defaultOption.disabled = true;
            roleSelect.appendChild(defaultOption);
            
            // Add Super Admin option only if current user is Super Admin
            if (isSuperAdmin) {
                const superAdminOption = document.createElement('option');
                superAdminOption.value = 'Super Admin';
                superAdminOption.textContent = 'Super Admin';
                roleSelect.appendChild(superAdminOption);
            }
            
            // Add regular Admin and User options
            const adminOption = document.createElement('option');
            adminOption.value = 'Admin';
            adminOption.textContent = 'Admin';
            roleSelect.appendChild(adminOption);
            
            const userOption = document.createElement('option');
            userOption.value = 'User';
            userOption.textContent = 'User';
            roleSelect.appendChild(userOption);
        }
        
        if (createOtherPositionInput) {
            createOtherPositionInput.style.display = 'none';
            createOtherPositionInput.required = false;
            createOtherPositionInput.value = '';
        }
        if (createUserModal) createUserModal.style.display = 'block';
    }

    function editUser(userId) {
        const user = allUsers.find(u => u.id == userId);
        if (!user) return;
        
        // Split name into first and last name properly
        let firstName = '';
        let lastName = '';
        
        // Check if the name contains a comma (last, first format)
        if (user.name.includes(',')) {
            const nameParts = user.name.split(',').map(part => part.trim());
            lastName = nameParts[0];
            firstName = nameParts[1] || '';
        } 
        // Otherwise split by spaces
        else {
            const nameParts = user.name.split(' ');
            // Assume last part is last name, everything else is first name
            if (nameParts.length > 1) {
                lastName = nameParts.pop(); // Remove last element and assign to lastName
                firstName = nameParts.join(' '); // Join remaining parts as firstName
            } else {
                firstName = nameParts[0] || '';
                lastName = '';
            }
            const isSuperAdmin = document.querySelector('meta[name="is-super-admin"]')?.content === 'true';
    
            if (isSuperAdmin) {
                // Remove current password/pincode fields
                document.getElementById('current_password').closest('.form-group').style.display = 'none';
                document.getElementById('current_pin').closest('.form-group').style.display = 'none';
                
                // Update labels
                document.querySelector('label[for="new_password"]').textContent = 'New Password (force update)';
                document.querySelector('label[for="new_pin"]').textContent = 'New 6-Digit PIN (force update)';
            } else {
                // Show current password/pincode fields for non-Super Admin
                document.getElementById('current_password').closest('.form-group').style.display = 'block';
                document.getElementById('current_pin').closest('.form-group').style.display = 'block';
                
                // Restore original labels
                document.querySelector('label[for="new_password"]').textContent = 'New Password';
                document.querySelector('label[for="new_pin"]').textContent = 'New 6-Digit PIN';
            }
        }
        
        // Fill the form
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_employee_no').value = user.employee_no;
        document.getElementById('edit_first_name').value = firstName;
        document.getElementById('edit_last_name').value = lastName;
        document.getElementById('edit_email').value = user.email;
        
        // Handle position field
        if (user.position !== 'Head' && user.position !== 'Supervisor' && user.position !== 'Employee' && user.position !== 'Other') {
            document.getElementById('edit_position').value = 'Other';
            if (editOtherPositionInput) {
                editOtherPositionInput.value = user.position;
                editOtherPositionInput.style.display = 'block';
                editOtherPositionInput.required = true;
            }
        } else {
            document.getElementById('edit_position').value = user.position;
            if (editOtherPositionInput) {
                editOtherPositionInput.style.display = 'none';
                editOtherPositionInput.required = false;
                editOtherPositionInput.value = '';
            }
        }
        
        // Set role options based on current user's role
        const roleSelect = document.getElementById('edit_role');
        if (roleSelect) {
            // Clear existing options
            roleSelect.innerHTML = '';
            
            // Add Super Admin option only if current user is Super Admin
            if (isSuperAdmin) {
                const superAdminOption = document.createElement('option');
                superAdminOption.value = 'Super Admin';
                superAdminOption.textContent = 'Super Admin';
                roleSelect.appendChild(superAdminOption);
            }
            
            // Add regular Admin and User options
            const adminOption = document.createElement('option');
            adminOption.value = 'Admin';
            adminOption.textContent = 'Admin';
            roleSelect.appendChild(adminOption);
            
            const userOption = document.createElement('option');
            userOption.value = 'User';
            userOption.textContent = 'User';
            roleSelect.appendChild(userOption);
            
            // Set the current role
            roleSelect.value = user.role;
        }
        
            // Clear password and pin fields
    document.getElementById('current_password').value = '';
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
    document.getElementById('current_pin').value = '';
    document.getElementById('new_pin').value = '';
    document.getElementById('confirm_pin').value = '';
        
        // Check admin limit
        checkAdminLimit('edit', user.role);
        
        if (editUserModal) editUserModal.style.display = 'block';
    }

    function confirmDeleteUser(userId) {
        const user = allUsers.find(u => u.id == userId);
        if (!user) return;
        
        // Check if trying to delete the last Super Admin
        if (user.role === 'Super Admin') {
            const superAdminCount = allUsers.filter(u => u.role === 'Super Admin').length;
            if (superAdminCount <= 1) {
                showErrorModal('Cannot delete the last Super Admin');
                return;
            }
        }
        
        currentUserId = userId;
        if (deleteModal) deleteModal.style.display = 'block';
    }

    function handleCreateUser(e) {
        e.preventDefault();
        
        const formData = new FormData(createUserForm);
        const firstName = formData.get('first_name');
        const lastName = formData.get('last_name');
        const role = formData.get('role');
        const position = formData.get('position');
        const otherPosition = formData.get('other_position');
        
        // Combine first and last name with a space
        formData.set('name', `${firstName} ${lastName}`.trim());
        
        // Use other position if "Other" is selected
        if (position === 'Other') {
            if (!otherPosition) {
                showErrorModal('Please specify the position');
                return;
            }
            formData.set('position', otherPosition);
        }
        
        // Client-side validation
        if (role === 'Admin') {
            const adminCount = allUsers.filter(user => user.role === 'Admin').length;
            if (adminCount >= 5) {
                showErrorModal('Maximum of 5 admin users allowed');
                return;
            }
        }
        
        // PIN code validation
        const pinCode = formData.get('pin_code');
        if (pinCode && (pinCode.length !== 6 || !/^\d+$/.test(pinCode))) {
            showErrorModal('PIN code must be exactly 6 digits');
            return;
        }
        
        fetch('../AdminBackEnd/ManageUserBE.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showSuccessModal(
                    'editSuccessModal', 
                    'uploadSuccessMessage', 
                    'User created successfully'
                );
                if (createUserModal) createUserModal.style.display = 'none';
                loadUsers();
            } else {
                showErrorModal(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('An error occurred while creating user');
        });
    }

    function handleEditUser(e) {
        e.preventDefault();
        console.log("Edit form submitted"); // Debug log
        
        const formData = new FormData(editUserForm);
        const firstName = formData.get('first_name');
        const lastName = formData.get('last_name');
        const userId = formData.get('id');
        const role = formData.get('role');
        const position = formData.get('position');
        const otherPosition = formData.get('other_position');
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');
        const newPin = formData.get('new_pin');
        const confirmPin = formData.get('confirm_pin');
        
        // Combine first and last name with a space
        formData.set('name', `${firstName} ${lastName}`.trim());
        
        // Use other position if "Other" is selected
        if (position === 'Other') {
            if (!otherPosition) {
                showErrorModal('Please specify the position');
                return;
            }
            formData.set('position', otherPosition);
        }
        
        // Get the user being edited
        const user = allUsers.find(u => u.id == userId);
        if (!user) return;
        
        // Check admin limit if changing to admin (excluding Super Admin)
        if (role === 'Admin' && user.role !== 'Admin') {
            const adminCount = allUsers.filter(u => u.role === 'Admin').length;
            if (adminCount >= 5) {
                showErrorModal('Maximum of 5 admin users allowed');
                return;
            }
        }
        
        // Password validation
        // Password validation - skip current password check for Super Admin
    if (newPassword || confirmPassword) {
        if (newPassword !== confirmPassword) {
            showErrorModal('New password and confirmation do not match');
            return;
        }
        
        // Only validate current password if not Super Admin
        if (!isSuperAdmin && !formData.get('current_password')) {
            showErrorModal('Current password is required');
            return;
        }
    }
        
        // PIN code validation
        if (newPin || confirmPin) {
            if (newPin !== confirmPin) {
                showErrorModal('New PIN and confirmation do not match');
                return;
            }
            if (newPin && (newPin.length !== 6 || !/^\d+$/.test(newPin))) {
                showErrorModal('PIN code must be exactly 6 digits');
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
                showSuccessModal(
                    'editSuccessModal', 
                    'uploadSuccessMessage', 
                    'User updated successfully'
                );
                if (editUserModal) editUserModal.style.display = 'none';
                loadUsers();
            } else {
                showErrorModal(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('An error occurred while updating user');
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
                showSuccessModal(
                    'deleteSuccessModal', 
                    'deleteSuccessMessage', 
                    'User deleted successfully'
                );
                loadUsers();
            } else {
                showErrorModal(data.message || 'An error occurred');
            }
            if (deleteModal) deleteModal.style.display = 'none';
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            showErrorModal('Error deleting user');
            if (deleteModal) deleteModal.style.display = 'none';
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
        // Count only Admin users (excluding Super Admin)
        const adminCount = allUsers.filter(user => user.role === 'Admin').length;
        const limitReached = adminCount >= 5;
        
        if (context === 'create') {
            const adminLimitMessage = document.getElementById('createAdminLimitMessage');
            const saveBtn = createUserForm?.querySelector('.btn.save');
            const roleSelect = document.getElementById('create_role');
            
            if (limitReached && adminLimitMessage && saveBtn && roleSelect) {
                // Don't change role if Super Admin is selected
                if (roleSelect.value !== 'Super Admin') {
                    roleSelect.value = 'User';
                }
                adminLimitMessage.style.display = 'block';
                saveBtn.disabled = (roleSelect.value === 'Admin');
            } else if (adminLimitMessage && saveBtn) {
                adminLimitMessage.style.display = 'none';
                saveBtn.disabled = false;
            }
        } else if (context === 'edit') {
            const adminLimitMessage = document.getElementById('editAdminLimitMessage');
            const saveBtn = editUserForm?.querySelector('.btn.save');
            const roleSelect = document.getElementById('edit_role');
            
            if (limitReached && currentRole !== 'Admin' && adminLimitMessage && saveBtn && roleSelect) {
                // Don't change role if Super Admin is selected
                if (roleSelect.value !== 'Super Admin') {
                    roleSelect.value = currentRole;
                }
                adminLimitMessage.style.display = 'block';
                saveBtn.disabled = (roleSelect.value === 'Admin');
            } else if (adminLimitMessage && saveBtn) {
                adminLimitMessage.style.display = 'none';
                saveBtn.disabled = false;
            }
        }
    }

    // Function to show success modal
    function showSuccessModal(modalId, messageId, message) {
        const modal = document.getElementById(modalId);
        const messageElement = document.getElementById(messageId);
        if (!modal || !messageElement) return;
        
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
        }, 1000);
    }

    // Function to show error modal
    function showErrorModal(message) {
        const modal = document.getElementById('errorModal');
        const messageElement = document.getElementById('errorMessage');
        if (!modal || !messageElement) return;
        
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
        }, 1000);
    }
});

// Password validation function
function validatePasswordFields() {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!currentPassword) {
        showErrorModal('Current password is required');
        return false;
    }

    if (newPassword || confirmPassword) {
        if (!newPassword) {
            showErrorModal('New password is required');
            return false;
        }

        if (!confirmPassword) {
            showErrorModal('Please confirm your new password');
            return false;
        }

        if (newPassword !== confirmPassword) {
            showErrorModal('New password and confirmation do not match');
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
        showErrorModal('Current PIN is required');
        return false;
    }

    if (newPin || confirmPin) {
        if (!newPin) {
            showErrorModal('New PIN is required');
            return false;
        }

        if (!confirmPin) {
            showErrorModal('Please confirm your new PIN');
            return false;
        }

        if (newPin !== confirmPin) {
            showErrorModal('New PIN and confirmation do not match');
            return false;
        }

        if (newPin.length !== 6 || !/^\d+$/.test(newPin)) {
            showErrorModal('PIN code must be exactly 6 digits');
            return false;
        }
    }

    return true;
}

// Function to close modals
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.style.opacity = '0';
    modal.style.transform = 'translate(-50%, -45%) scale(0.9)';
    setTimeout(() => modal.style.display = 'none', 300);
}