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
    let isSuperAdmin = false;

    // Initialize
    if (createUserBtn || editUserForm) {
        // Check if current user is Super Admin
        isSuperAdmin = document.querySelector('.admin-text')?.dataset?.role === 'Super Admin';
        
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
                                alert('All profile fields are required');
                                isValid = false;
                            }
                            break;

                        case 'designation':
                            const position = formData.get('position');
                            const otherPosition = formData.get('other_position');
                            
                            // Use other position if "Other" is selected
                            if (position === 'Other') {
                                if (!otherPosition) {
                                    alert('Please specify the position');
                                    isValid = false;
                                    break;
                                }
                                containerData.append('position', otherPosition);
                            } else {
                                containerData.append('position', position);
                            }
                            
                            containerData.append('role', formData.get('role'));
                            
                            if (!position || !formData.get('role')) {
                                alert('Both position and role are required');
                                isValid = false;
                            }
                            break;

                        case 'password':
                            const currentPassword = formData.get('current_password');
                            const newPassword = formData.get('new_password');
                            const confirmPassword = formData.get('confirm_password');
                            
                            if (newPassword || confirmPassword) {
                                if (!isSuperAdmin && !currentPassword) {
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

                        case 'pincode':
                            const currentPin = formData.get('current_pin');
                            const newPin = formData.get('new_pin');
                            const confirmPin = formData.get('confirm_pin');
                            
                            if (newPin || confirmPin) {
                                if (!isSuperAdmin && !currentPin) {
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
                                    }, 5000);
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
            row.innerHTML = `
                <td>${user.employee_no}</td>
                <td>${user.name}</td>
                <td>${user.position}</td>
                <td>${user.role}</td>
                <td>${user.email}</td>
                <td class="password-cell">
                    ${isSuperAdmin && user.password !== '••••••••' ? 
                        `<span class="password-text">${user.password}</span>
                         <button class="toggle-password" data-password="${user.password}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                            </svg>
                         </button>` : 
                        user.password}
                </td>
                <td class="pincode-cell">
                    ${isSuperAdmin && user.pin_code !== '••••••' ? 
                        `<span class="pincode-text">${user.pin_code}</span>
                         <button class="toggle-pincode" data-pincode="${user.pin_code}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                            </svg>
                         </button>` : 
                        user.pin_code}
                </td>
                <td></td>
            `;
            
            const actionCell = row.querySelector('td:last-child');
            actionCell.appendChild(createKebabMenu(user.id));
            
            tbody.appendChild(row);
        });

        // Add event listeners for password toggles
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const passwordText = this.parentElement.querySelector('.password-text');
                if (passwordText) {
                    if (passwordText.textContent === this.dataset.password) {
                        passwordText.textContent = '••••••••';
                    } else {
                        passwordText.textContent = this.dataset.password;
                    }
                }
            });
        });

        // Add event listeners for pincode toggles
        document.querySelectorAll('.toggle-pincode').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const pincodeText = this.parentElement.querySelector('.pincode-text');
                if (pincodeText) {
                    if (pincodeText.textContent === this.dataset.pincode) {
                        pincodeText.textContent = '••••••';
                    } else {
                        pincodeText.textContent = this.dataset.pincode;
                    }
                }
            });
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
        if (createUserForm) createUserForm.reset();
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
        
        document.getElementById('edit_role').value = user.role;
        
        // Clear password and pin fields
        if (document.getElementById('current_password')) {
            document.getElementById('current_password').value = '';
        }
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
        if (document.getElementById('current_pin')) {
            document.getElementById('current_pin').value = '';
        }
        document.getElementById('new_pin').value = '';
        document.getElementById('confirm_pin').value = '';
        
        // Check admin limit
        checkAdminLimit('edit', user.role);
        
        // Hide current password/pin fields for Super Admin
        if (isSuperAdmin) {
            const currentPasswordField = document.getElementById('current_password');
            const currentPinField = document.getElementById('current_pin');
            
            if (currentPasswordField) {
                currentPasswordField.closest('.form-group').style.display = 'none';
            }
            if (currentPinField) {
                currentPinField.closest('.form-group').style.display = 'none';
            }
        }
        
        if (editUserModal) editUserModal.style.display = 'block';
    }

    function confirmDeleteUser(userId) {
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
                alert('Please specify the position');
                return;
            }
            formData.set('position', otherPosition);
        }
        
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
                alert('Please specify the position');
                return;
            }
            formData.set('position', otherPosition);
        }
        
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
        const adminCount = allUsers.filter(user => user.role === 'Admin').length;
        const limitReached = adminCount >= 5;
        
        if (context === 'create') {
            const adminLimitMessage = document.getElementById('createAdminLimitMessage');
            const saveBtn = createUserForm?.querySelector('.btn.save');
            
            if (limitReached && adminLimitMessage && saveBtn) {
                document.getElementById('create_role').value = 'User';
                adminLimitMessage.style.display = 'block';
                saveBtn.disabled = true;
            } else if (adminLimitMessage && saveBtn) {
                adminLimitMessage.style.display = 'none';
                saveBtn.disabled = false;
            }
        } else if (context === 'edit') {
            const adminLimitMessage = document.getElementById('editAdminLimitMessage');
            const saveBtn = editUserForm?.querySelector('.btn.save');
            const roleSelect = document.getElementById('edit_role');
            
            if (limitReached && currentRole !== 'Admin' && adminLimitMessage && saveBtn && roleSelect) {
                roleSelect.value = currentRole;
                adminLimitMessage.style.display = 'block';
                saveBtn.disabled = true;
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
        }, 3000);
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
        }, 5000);
    }
});

// Password validation function
function validatePasswordFields() {
    const currentPassword = document.getElementById('current_password')?.value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    // Skip current password validation for Super Admin
    const isSuperAdmin = document.querySelector('.admin-text')?.textContent?.includes('Super Admin') || false;
    
    if (!isSuperAdmin && !currentPassword) {
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
    const currentPin = document.getElementById('current_pin')?.value;
    const newPin = document.getElementById('new_pin').value;
    const confirmPin = document.getElementById('confirm_pin').value;

    // Skip current pin validation for Super Admin
    const isSuperAdmin = document.querySelector('.admin-text')?.textContent?.includes('Super Admin') || false;
    
    if (!isSuperAdmin && !currentPin) {
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

// Function to close modals
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.style.opacity = '0';
    modal.style.transform = 'translate(-50%, -45%) scale(0.9)';
    setTimeout(() => modal.style.display = 'none', 300);
}