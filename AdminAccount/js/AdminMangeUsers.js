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
    
    // Pagination variables
    let currentPage = 1;
    let totalPages = 1;
    const limit = 10;
    let allUsers = [];
    let currentUserId = null;

    function setupPasswordVisibilityToggles() {
        const isSuperAdmin = document.querySelector('meta[name="is-super-admin"]')?.content === 'true';
        if (!isSuperAdmin) return;
    
        document.querySelectorAll('.password-cell, .pincode-cell').forEach(cell => {
            const originalValue = cell.dataset.originalValue || cell.textContent.trim();
            cell.dataset.originalValue = originalValue; // Store original value in data attribute
            
            if (originalValue !== 'N/A') {
                const toggle = document.createElement('span');
              ;
                
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
                                loadUsers(currentPage);
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

        // Delete Modal
        if (deleteModal) {
            document.querySelector('#deleteModal .close')?.addEventListener('click', () => deleteModal.style.display = 'none');
            document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => deleteModal.style.display = 'none');
            document.getElementById('confirmDeleteBtn')?.addEventListener('click', confirmDelete);
        }
        
        // Search functionality
      // Search functionality - automatic with debounce
let searchTimeout;
if (searchInput) {
    searchInput.addEventListener('input', function() {
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Set new timeout with debounce (300ms delay)
        searchTimeout = setTimeout(() => {
            const searchTerm = searchInput.value.trim().toLowerCase();
            
            if (!searchTerm) {
                loadUsers(currentPage);
                // Show pagination again when search is cleared
                const paginationContainer = document.getElementById('paginationControls');
                if (paginationContainer) {
                    paginationContainer.style.display = 'flex';
                }
                return;
            }
            
            const filteredUsers = allUsers.filter(user => 
                (user.employee_no && user.employee_no.toLowerCase().includes(searchTerm)) ||
                (user.name && user.name.toLowerCase().includes(searchTerm)) ||
                (user.position && user.position.toLowerCase().includes(searchTerm)) ||
                (user.role && user.role.toLowerCase().includes(searchTerm)) ||
                (user.email && user.email.toLowerCase().includes(searchTerm))
            );
            
            renderUsers(filteredUsers);
            
            // Hide pagination when searching
            const paginationContainer = document.getElementById('paginationControls');
            if (paginationContainer) {
                paginationContainer.style.display = 'none';
            }
        }, 300); // 300ms debounce delay
    });
    
    // Clear search when clicking the 'x' that appears in some browsers
    searchInput.addEventListener('search', function() {
        if (this.value === '') {
            loadUsers(currentPage);
            // Show pagination again when search is cleared
            const paginationContainer = document.getElementById('paginationControls');
            if (paginationContainer) {
                paginationContainer.style.display = 'flex';
            }
        }
    });
}
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (createUserModal && event.target === createUserModal) createUserModal.style.display = 'none';
            if (editUserModal && event.target === editUserModal) editUserModal.style.display = 'none';
            if (deleteModal && event.target === deleteModal) deleteModal.style.display = 'none';
        });
    }

    function loadUsers(page = 1) {
        currentPage = page;
        fetch(`../AdminBackEnd/ManageUserBE.php?get_users=1&page=${page}&limit=${limit}`)
            .then(response => response.json())
            .then(data => {
                allUsers = data.users;
                totalPages = data.pagination.total_pages;
                renderUsers(data.users);
                updatePaginationControls(data.pagination);
                setupPasswordVisibilityToggles();
            })
            .catch(error => console.error('Error loading users:', error));
    }

    function updatePaginationControls(pagination) {
        const paginationContainer = document.getElementById('paginationControls');
        if (!paginationContainer) {
            // Create the container if it doesn't exist
            const tableContainer = document.querySelector('.table-container');
            if (!tableContainer) return;
            
            const newPaginationContainer = document.createElement('div');
            newPaginationContainer.id = 'paginationControls';
            newPaginationContainer.className = 'pagination-controls';
            tableContainer.appendChild(newPaginationContainer);
        } else {
            paginationContainer.innerHTML = '';
        }

        // Previous button
        const prevButton = document.createElement('button');
        prevButton.className = 'pagination-btn';
        prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                loadUsers(currentPage - 1);
            }
        });
        paginationContainer.appendChild(prevButton);

        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        // Adjust if we're at the end
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // Always show first page
        if (startPage > 1) {
            const firstPageButton = document.createElement('button');
            firstPageButton.className = 'pagination-btn';
            firstPageButton.textContent = '1';
            firstPageButton.addEventListener('click', () => loadUsers(1));
            paginationContainer.appendChild(firstPageButton);

            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'pagination-ellipsis';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }
        }

        // Visible page range
        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('button');
            pageButton.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
            pageButton.textContent = i;
            pageButton.addEventListener('click', () => loadUsers(i));
            paginationContainer.appendChild(pageButton);
        }

        // Always show last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'pagination-ellipsis';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            const lastPageButton = document.createElement('button');
            lastPageButton.className = 'pagination-btn';
            lastPageButton.textContent = totalPages;
            lastPageButton.addEventListener('click', () => loadUsers(totalPages));
            paginationContainer.appendChild(lastPageButton);
        }

        // Next button
        const nextButton = document.createElement('button');
        nextButton.className = 'pagination-btn';
        nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextButton.disabled = currentPage === totalPages;
        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                loadUsers(currentPage + 1);
            }
        });
        paginationContainer.appendChild(nextButton);

        // Add page info
        const pageInfo = document.createElement('span');
        pageInfo.className = 'pagination-info';
        pageInfo.textContent = `Page ${currentPage} of ${totalPages} | ${pagination.total_users} users`;
        paginationContainer.appendChild(pageInfo);
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
        
        // Sort users - admins first, then others
        const sortedUsers = [...users].sort((a, b) => {
            if (a.role === 'Admin' || a.role === 'Super Admin') return -1;
            if (b.role === 'Admin' || b.role === 'Super Admin') return 1;
            return 0;
        });
        
        sortedUsers.forEach(user => {
            const row = document.createElement('tr');
    
            // Add special class for Super Admin
            const rowClass = user.role === 'Super Admin' ? 'super-admin-row' : '';
            row.className = rowClass;
    
            // Generate badge HTML
            let badgeHTML = '';
            if (user.role === 'Super Admin') {
                badgeHTML = `<div class="super-admin-badge">Super Admin</div>`;
            } else if (user.role === 'Admin') {
                badgeHTML = `<div class="admin-badge">Admin</div>`;
            }
    
            // Create name cell with badge below
            const nameCellContent = `
                <div class="name-cell-wrapper">
                    <div class="user-name">${user.name}</div>
                    ${badgeHTML}
                </div>
            `;
    
            row.innerHTML = `
                <td>${user.employee_no}</td>
                <td>${nameCellContent}</td>
                <td>${user.position}</td>
                <td>${user.role}</td>
                <td>${user.email}</td>
                <td class="password-cell">••••••••</td>
                <td class="pincode-cell">••••••</td>
                <td></td>
            `;
    
            const passwordCell = row.querySelector('.password-cell');
            const pinCell = row.querySelector('.pincode-cell');
            passwordCell.dataset.originalValue = user.password || 'N/A';
            pinCell.dataset.originalValue = user.pin_code || 'N/A';
    
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
                passwordCell.textContent = passwordCell.dataset.originalValue;
                pinCell.textContent = pinCell.dataset.originalValue;
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
                }, 1000);
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
        
        // Set role options - only show User role
        const roleSelect = document.getElementById('create_role');
        if (roleSelect) {
            roleSelect.innerHTML = '';
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Choose role...';
            defaultOption.selected = true;
            defaultOption.disabled = true;
            roleSelect.appendChild(defaultOption);
            
            const userOption = document.createElement('option');
            userOption.value = 'User';
            userOption.textContent = 'User';
            roleSelect.appendChild(userOption);
        }
        
        // Set position options - only Employee and Other
        const positionSelect = document.getElementById('create_position');
        if (positionSelect) {
            positionSelect.innerHTML = '';
            
            const defaultPosOption = document.createElement('option');
            defaultPosOption.value = '';
            defaultPosOption.textContent = 'Choose position...';
            defaultPosOption.selected = true;
            defaultPosOption.disabled = true;
            positionSelect.appendChild(defaultPosOption);
            
            const employeeOption = document.createElement('option');
            employeeOption.value = 'Employee';
            employeeOption.textContent = 'Employee';
            positionSelect.appendChild(employeeOption);
            
            const otherOption = document.createElement('option');
            otherOption.value = 'Other';
            otherOption.textContent = 'Other';
            positionSelect.appendChild(otherOption);
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
        
        if (user.name.includes(',')) {
            const nameParts = user.name.split(',').map(part => part.trim());
            lastName = nameParts[0];
            firstName = nameParts[1] || '';
        } else {
            const nameParts = user.name.split(' ');
            if (nameParts.length > 1) {
                lastName = nameParts.pop();
                firstName = nameParts.join(' ');
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
        
        // Handle position field based on user role
        const positionSelect = document.getElementById('edit_position');
        const otherPositionInput = document.getElementById('edit_other_position');
        
        if (user.role === 'Admin' || user.role === 'Super Admin') {
            // For admin users, make position and role read-only
            positionSelect.disabled = true;
            positionSelect.innerHTML = `<option value="${user.position}" selected>${user.position}</option>`;
            
            const roleSelect = document.getElementById('edit_role');
            roleSelect.disabled = true;
            roleSelect.innerHTML = `<option value="${user.role}" selected>${user.role}</option>`;
        } else {
            // For regular users, only show Employee and Other options
            positionSelect.disabled = false;
            positionSelect.innerHTML = '';
            
            const employeeOption = document.createElement('option');
            employeeOption.value = 'Employee';
            employeeOption.textContent = 'Employee';
            positionSelect.appendChild(employeeOption);
            
            const otherOption = document.createElement('option');
            otherOption.value = 'Other';
            otherOption.textContent = 'Other';
            positionSelect.appendChild(otherOption);
            
            // Set current position
            if (user.position !== 'Employee' && user.position !== 'Other') {
                positionSelect.value = 'Other';
                otherPositionInput.value = user.position;
                otherPositionInput.style.display = 'block';
                otherPositionInput.required = true;
            } else {
                positionSelect.value = user.position;
                otherPositionInput.style.display = 'none';
                otherPositionInput.required = false;
                otherPositionInput.value = '';
            }
            
            // Set role options - only show User role
            const roleSelect = document.getElementById('edit_role');
            roleSelect.innerHTML = '';
            const userOption = document.createElement('option');
            userOption.value = 'User';
            userOption.textContent = 'User';
            roleSelect.appendChild(userOption);
            roleSelect.value = user.role;
        }
        
        // Clear password and pin fields
        document.getElementById('current_password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
        document.getElementById('current_pin').value = '';
        document.getElementById('new_pin').value = '';
        document.getElementById('confirm_pin').value = '';
        
        // Always show current password/pin fields for non-Super Admins
        document.getElementById('current_password').closest('.form-group').style.display = 'block';
        document.getElementById('current_pin').closest('.form-group').style.display = 'block';
        
        // Update labels
        document.querySelector('label[for="new_password"]').textContent = 'New Password';
        document.querySelector('label[for="new_pin"]').textContent = 'New 6-Digit PIN';
        
        if (editUserModal) editUserModal.style.display = 'block';
    }

    function confirmDeleteUser(userId) {
        const user = allUsers.find(u => u.id == userId);
        if (!user) return;
        
        // Check if trying to delete the last Super Admin
        if (user.role === 'Super Admin') {
            const superAdminCount = allUsers.filter(u => u.role === 'Super Admin').length;
            if (superAdminCount <= 1) {
                showErrorModal('Cannot delete the last Super Admin account');
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
                loadUsers(currentPage);
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
                loadUsers(currentPage);
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
        
        const deletePinCodeInput = document.getElementById('deletePinCode');
        const pinError = document.getElementById('pinError');
        const pinCode = deletePinCodeInput.value;
        
        // Strict validation - don't proceed if PIN is empty
        if (!pinCode) {
            pinError.textContent = 'PIN code is required to delete a user';
            pinError.style.display = 'block';
            return;
        }
        
        // Validate PIN format
        if (pinCode.length !== 6 || !/^\d+$/.test(pinCode)) {
            pinError.textContent = 'PIN code must be 6 digits';
            pinError.style.display = 'block';
            return;
        }
        
        // Verify PIN code via AJAX
        const formData = new FormData();
        formData.append('action', 'verify_pin');
        formData.append('pin_code', pinCode);
        
        // Show loading state
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        
        fetch('../AdminBackEnd/ManageUserBE.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                throw new Error(data.message || 'Invalid PIN code');
            }
            
            // Only proceed with deletion if PIN verification succeeded
            const deleteFormData = new FormData();
            deleteFormData.append('action', 'delete_user');
            deleteFormData.append('id', currentUserId);
            deleteFormData.append('verified_pin', pinCode); // Send verified PIN again for server-side validation
            
            return fetch('../AdminBackEnd/ManageUserBE.php', {
                method: 'POST',
                body: deleteFormData
            });
        })
        .then(response => response.json())
        .then(data => {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
            
            if (data.status === 'success') {
                showSuccessModal(
                    'deleteSuccessModal', 
                    'deleteSuccessMessage', 
                    'User deleted successfully'
                );
                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal) deleteModal.style.display = 'none';
                loadUsers(currentPage);
            } else {
                throw new Error(data.message || 'An error occurred during deletion');
            }
        })
        .catch(error => {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
            pinError.textContent = error.message;
            pinError.style.display = 'block';
            console.error('Error:', error);
        });
    }

    function searchUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        if (!searchTerm) {
            loadUsers(currentPage);
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
        
        // Hide pagination when searching
        const paginationContainer = document.getElementById('paginationControls');
        if (paginationContainer) {
            paginationContainer.style.display = 'none';
        }
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