document.addEventListener("DOMContentLoaded", function() {
    initializeUserDropdown();
    initializeLogoutModal();
    initializeTableControls();
    initializeUploadCaseModal();
    initializeEditModal();
    initializeTransportOfficerAutocomplete();
    initializeDeleteModals();
});

/////////////////////////User Dropdown Functionality/////////////////////////
function initializeUserDropdown() {
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");

    if (!userContainer || !userDropdown) return;

    function toggleDropdown(show = null) {
        if (show === null) {
            userDropdown.classList.toggle("show");
        } else {
            userDropdown.classList.toggle("show", show);
        }
    }

    userContainer.addEventListener("click", function(event) {
        event.stopPropagation();
        toggleDropdown();
    });

    document.addEventListener("click", function(event) {
        if (userDropdown.classList.contains("show")) {
            if (!userContainer.contains(event.target) && !userDropdown.contains(event.target)) {
                toggleDropdown(false);
            }
        }
    });
}

/////////////////////////Logout Modal Functionality/////////////////////////
function initializeLogoutModal() {
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const logoutCancel = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');

    if (!logoutLink || !logoutModal || !logoutCancel || !logoutConfirm) return;

    function closeAll() {
        const userDropdown = document.getElementById("userDropdown");
        if (userDropdown) userDropdown.classList.remove("show");
        
        logoutModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    logoutLink.addEventListener('click', function(e) {
        e.preventDefault();
        closeAll();
        logoutModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    logoutCancel.addEventListener('click', closeAll);

    logoutConfirm.addEventListener('click', function() {
        window.location.href = '../../../login/logout.php';
    });

    logoutModal.addEventListener('click', function(e) {
        if (e.target === logoutModal) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAll();
        }
    });
}

/////////////////////////Table Controls Functionality/////////////////////////
function initializeTableControls() {
    initializeSelectAll();
    initializeSearch();
    initializeVehicleTeamFilter();
    initializeCaseTypeFilter();
    initializeDateFilters();
    initializeDeleteSelected();
}

function initializeSelectAll() {
    const selectAll = document.getElementById('selectAll');
    if (!selectAll) return;

    selectAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
}

function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        filterTable();
    });
}

function initializeVehicleTeamFilter() {
    const vehicleTeamFilter = document.getElementById('vehicleTeamFilter');
    if (!vehicleTeamFilter) return;

    vehicleTeamFilter.addEventListener('change', function() {
        filterTable();
    });
}

function initializeCaseTypeFilter() {
    const caseTypeFilter = document.getElementById('caseTypeFilter');
    if (!caseTypeFilter) return;

    caseTypeFilter.addEventListener('change', function() {
        filterTable();
    });
}

function initializeDateFilters() {
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');

    if (!dateFrom || !dateTo) return;

    dateFrom.addEventListener('change', function() {
        if (dateTo.value && dateFrom.value > dateTo.value) {
            dateTo.value = dateFrom.value;
        }
        filterTable();
    });

    dateTo.addEventListener('change', function() {
        if (dateFrom.value && dateFrom.value > dateTo.value) {
            dateFrom.value = dateTo.value;
        }
        filterTable();
    });
}

/////////////////////////Table Filtering Functionality/////////////////////////
function filterTable() {
    const searchTerm = document.querySelector('.search-input')?.value.toLowerCase() || '';
    const selectedTeam = document.getElementById('vehicleTeamFilter')?.value || '';
    const selectedCaseType = document.getElementById('caseTypeFilter')?.value || '';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';

    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const teamCell = row.querySelector('td:nth-child(2)');
        const caseTypeCell = row.querySelector('td:nth-child(3)');
        const dispatchTimeCell = row.querySelector('td:nth-child(7)');
        const rowText = row.textContent.toLowerCase();

        const teamMatch = selectedTeam === '' || teamCell.textContent.trim() === selectedTeam;
        const caseTypeMatch = selectedCaseType === '' || caseTypeCell.textContent.trim() === selectedCaseType;
        const searchMatch = searchTerm === '' || rowText.includes(searchTerm);
        
        let dateMatch = true;
        if (dateFrom || dateTo) {
            const dispatchDate = new Date(dispatchTimeCell.textContent.trim());
            const fromDate = dateFrom ? new Date(dateFrom) : null;
            const toDate = dateTo ? new Date(dateTo + 'T23:59:59') : null;
            
            if (fromDate && dispatchDate < fromDate) dateMatch = false;
            if (toDate && dispatchDate > toDate) dateMatch = false;
        }

        row.style.display = (teamMatch && caseTypeMatch && searchMatch && dateMatch) ? '' : 'none';
    });
}

/////////////////////////
// CLEAR FILTERS BUTTON //
/////////////////////////
document.getElementById('clearFilters').addEventListener('click', function() {
    document.getElementById('vehicleTeamFilter').value = '';
    document.getElementById('caseTypeFilter').value = '';
    document.querySelector('.search-input').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    
    filterTable();
    
    this.classList.add('clicked');
    setTimeout(() => this.classList.remove('clicked'), 300);
});

/////////////////////////
// DELETE FUNCTIONALITY //
/////////////////////////

function initializeDeleteModals() {
    // Single delete button handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn')) {
            const caseId = e.target.closest('.delete-btn').getAttribute('data-id');
            showDeleteModal(caseId);
        }
    });

    // Initialize delete modals
    const deleteModal = document.getElementById('deleteModal');
    const multipleDeleteModal = document.getElementById('multipleDeleteModal');
    
    // Single delete confirmation
    document.getElementById('deleteFileBtn')?.addEventListener('click', function() {
        const caseId = deleteModal.dataset.caseId;
        const pinCode = document.getElementById('deletePinCode').value;
        
        if (!pinCode || pinCode.length !== 6) {
            document.getElementById('deletePinError').textContent = 'Please enter a valid 6-digit PIN';
            document.getElementById('deletePinError').style.display = 'block';
            return;
        }
        
        deleteCase(caseId, pinCode);
    });

    // Multiple delete confirmation
    document.getElementById('confirmMultipleDelete')?.addEventListener('click', function() {
        const selectedCases = Array.from(document.querySelectorAll('tbody input[type="checkbox"]:checked'))
            .map(checkbox => checkbox.value);
        const pinCode = document.getElementById('multipleDeletePinCode').value;
        
        if (!pinCode || pinCode.length !== 6) {
            document.getElementById('multipleDeletePinError').textContent = 'Please enter a valid 6-digit PIN';
            document.getElementById('multipleDeletePinError').style.display = 'block';
            return;
        }
        
        deleteSelectedCases(selectedCases, pinCode);
    });

    // Close modals when clicking on close buttons
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.deletecustom-modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // Close modals when clicking outside
    document.querySelectorAll('.deletecustom-modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    // Close success modal
    document.querySelectorAll('.successclose').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.deletesuccess-modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
}

function showDeleteModal(caseId) {
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.dataset.caseId = caseId;
    document.getElementById('deletePinCode').value = '';
    document.getElementById('deletePinError').style.display = 'none';
    deleteModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function showBulkDeleteModal(selectedCount) {
    const multipleDeleteModal = document.getElementById('multipleDeleteModal');
    document.getElementById('multipleDeleteMessage').textContent = 
        `Are you sure you want to delete ${selectedCount} selected ${selectedCount > 1 ? 'cases' : 'case'}?`;
    document.getElementById('multipleDeletePinCode').value = '';
    document.getElementById('multipleDeletePinError').style.display = 'none';
    multipleDeleteModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function deleteCase(caseId, pinCode) {
    fetch('../AdminBackEnd/VehicleRunsBE.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            ids: [caseId],
            pinCode: pinCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close the delete modal
            closeModal('deleteModal');
            // Show the success modal
            showSuccessModal('Case deleted successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            document.getElementById('deletePinError').textContent = data.message || 'Failed to delete case';
            document.getElementById('deletePinError').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('deletePinError').textContent = 'An error occurred while deleting the case';
        document.getElementById('deletePinError').style.display = 'block';
    });
}

function showSuccessModal(message) {
    const successModal = document.getElementById('deleteSuccessModal');
    document.getElementById('deleteSuccessMessage').textContent = message;
    successModal.style.display = 'flex';
    
    // Auto-close after 1.5 seconds
    setTimeout(() => {
        closeModal('deleteSuccessModal');
    }, 500);
}

function initializeDeleteSelected() {
    const deleteSelectedBtn = document.getElementById('deleteSelected');
    if (!deleteSelectedBtn) return;

    deleteSelectedBtn.addEventListener('click', function() {
        const selectedCases = Array.from(document.querySelectorAll('tbody input[type="checkbox"]:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedCases.length === 0) {
            alert("Please select at least one case to delete");
            return;
        }
        
        showBulkDeleteModal(selectedCases.length);
    });
}

function deleteSelectedCases(ids, pinCode) {
    fetch('../AdminBackEnd/VehicleRunsBE.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            ids: ids,
            pinCode: pinCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close the multiple delete modal
            closeModal('multipleDeleteModal');
            // Show the success modal
            showSuccessModal(`${data.deletedCount} ${data.deletedCount > 1 ? 'cases' : 'case'} deleted successfully!`);
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            document.getElementById('multipleDeletePinError').textContent = data.message || 'Failed to delete cases';
            document.getElementById('multipleDeletePinError').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('multipleDeletePinError').textContent = 'An error occurred while deleting cases';
        document.getElementById('multipleDeletePinError').style.display = 'block';
    });
}

/////////////////////////
// UPLOAD CASE MODAL //
/////////////////////////
function initializeUploadCaseModal() {
    const uploadCaseBtn = document.getElementById('uploadCase');
    const uploadModal = document.getElementById('uploadCaseModal');
    const closeModal = document.querySelector('.upload-modal-close');
    const cancelBtn = document.querySelector('.cancel-btn');
    const fileNameDisplay = document.querySelector('.file-upload-filename');
    const caseUploadForm = document.getElementById('caseUploadForm');
    const fileUploadLabel = document.querySelector('.file-upload-label');

    if (!uploadCaseBtn || !uploadModal) return;

    uploadCaseBtn.addEventListener('click', function() {
        document.querySelector('.upload-modal-header h3').textContent = 'Upload Case';
        document.querySelector('.submit-btn').textContent = 'Upload Case';
        document.getElementById('caseUploadForm').reset();
        document.querySelector('.file-upload-filename').textContent = 'No file chosen';
        delete document.getElementById('caseUploadForm').dataset.editId;
        uploadModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    function closeUploadModal() {
        uploadModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    closeModal.addEventListener('click', closeUploadModal);
    cancelBtn.addEventListener('click', closeUploadModal);

    uploadModal.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent closing when clicking inside modal
    });

    let selectedFile = null;

    fileUploadLabel.addEventListener('click', function(e) {
        e.preventDefault();
        
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                selectedFile = this.files[0];
                // Store the original filename in a data attribute
                fileNameDisplay.textContent = selectedFile.name;
                fileNameDisplay.dataset.originalName = selectedFile.name;
            } else {
                selectedFile = null;
                fileNameDisplay.textContent = 'No file chosen';
                delete fileNameDisplay.dataset.originalName;
            }
        });
        
        document.body.appendChild(fileInput);
        fileInput.click();
        
        setTimeout(() => {
            document.body.removeChild(fileInput);
        }, 500);
    });

    if (caseUploadForm) {
        caseUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const requiredFields = ['vehicleTeam', 'caseType', 'emergencyResponders', 'location', 'dispatchTime', 'backToBaseTime'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.style.borderColor = 'red';
                    isValid = false;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields');
                return;
            }
            
            const dispatchTime = new Date(document.getElementById('dispatchTime').value);
            const backToBaseTime = new Date(document.getElementById('backToBaseTime').value);
            
            if (dispatchTime >= backToBaseTime) {
                alert('Dispatch time must be before back to base time');
                return;
            }
            
            const formData = new FormData();
            
            formData.append('vehicleTeam', document.getElementById('vehicleTeam').value);
            formData.append('caseType', document.getElementById('caseType').value);
            formData.append('transportOfficer', document.getElementById('transportOfficer').value);
            formData.append('emergencyResponders', document.getElementById('emergencyResponders').value);
            formData.append('location', document.getElementById('location').value);
            formData.append('dispatchTime', document.getElementById('dispatchTime').value);
            formData.append('backToBaseTime', document.getElementById('backToBaseTime').value);
            
            if (selectedFile) {
                // Create a new File object with the original name if it was modified
                const originalName = fileNameDisplay.dataset.originalName || selectedFile.name;
                const renamedFile = new File([selectedFile], originalName, {
                    type: selectedFile.type,
                    lastModified: selectedFile.lastModified
                });
                formData.append('caseImage', renamedFile);
            }
            
            if (this.dataset.editId) {
                formData.append('id', this.dataset.editId);
            }
            
            submitCaseForm(formData, this.dataset.editId ? 'update' : 'create');
        });
    }
}

function submitCaseForm(formData, action) {
    const submitBtn = document.querySelector('.submit-btn');
    const originalBtnText = submitBtn.textContent;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (action === 'update' ? 'Updating...' : 'Uploading...');
    submitBtn.disabled = true;
    
    fetch(`../AdminBackEnd/VehicleRunsBE.php?action=${action}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close the appropriate modal
            const modalToClose = action === 'update' ? 'editCaseModal' : 'uploadCaseModal';
            closeModal(modalToClose);
            
            // Show the success modal
            const successModal = document.getElementById('uploadSuccessModal');
            if (successModal) {
                document.getElementById('uploadSuccessMessage').textContent = 
                    `Case ${action === 'update' ? 'updated' : 'uploaded'} successfully!`;
                successModal.style.display = 'flex';
                
                // Close success modal and reload after 1.5 seconds
                setTimeout(() => {
                    closeModal('uploadSuccessModal');
                    window.location.reload();
                }, 500);
            }
        } else {
            alert('Error: ' + (data.message || `Failed to ${action} case`));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`An error occurred while ${action === 'update' ? 'updating' : 'uploading'} the case`);
    })
    .finally(() => {
        submitBtn.textContent = originalBtnText;
        submitBtn.disabled = false;
    });
}

/////////////////////////
// EDIT CASE MODAL //
/////////////////////////
function initializeEditModal() {
    const editModal = document.getElementById('editCaseModal');
    const editCloseModal = document.querySelector('.edit-modal-close');
    const editCancelBtn = document.querySelector('.edit-cancel-btn');
    const editFileNameDisplay = document.querySelector('#editCaseModal .file-upload-filename');
    const editCaseForm = document.getElementById('caseEditForm');
    const editFileUploadLabel = document.querySelector('#editCaseModal .file-upload-label');

    if (!editModal) return;

    function closeEditModal() {
        editModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    editCloseModal.addEventListener('click', closeEditModal);
    editCancelBtn.addEventListener('click', closeEditModal);

    editModal.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent closing when clicking inside modal
    });

    let editSelectedFile = null;

    editFileUploadLabel.addEventListener('click', function(e) {
        e.preventDefault();
        
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                editSelectedFile = this.files[0];
                editFileNameDisplay.textContent = editSelectedFile.name;
                editFileNameDisplay.dataset.originalName = editSelectedFile.name;
            } else {
                editSelectedFile = null;
                editFileNameDisplay.textContent = 'No file chosen';
                delete editFileNameDisplay.dataset.originalName;
            }
        });
        
        document.body.appendChild(fileInput);
        fileInput.click();
        
        setTimeout(() => {
            document.body.removeChild(fileInput);
        }, 500);
    });

    if (editCaseForm) {
        editCaseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const requiredFields = ['editVehicleTeam', 'editCaseType', 'editEmergencyResponders', 'editLocation', 'editDispatchTime', 'editBackToBaseTime'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.style.borderColor = 'red';
                    isValid = false;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields');
                return;
            }
            
            const dispatchTime = new Date(document.getElementById('editDispatchTime').value);
            const backToBaseTime = new Date(document.getElementById('editBackToBaseTime').value);
            
            if (dispatchTime >= backToBaseTime) {
                alert('Dispatch time must be before back to base time');
                return;
            }
            
            const formData = new FormData();
            
            formData.append('vehicleTeam', document.getElementById('editVehicleTeam').value);
            formData.append('caseType', document.getElementById('editCaseType').value);
            formData.append('transportOfficer', document.getElementById('editTransportOfficer').value);
            formData.append('emergencyResponders', document.getElementById('editEmergencyResponders').value);
            formData.append('location', document.getElementById('editLocation').value);
            formData.append('dispatchTime', document.getElementById('editDispatchTime').value);
            formData.append('backToBaseTime', document.getElementById('editBackToBaseTime').value);
            formData.append('id', document.getElementById('editCaseId').value);
            
            if (editSelectedFile) {
                formData.append('caseImage', editSelectedFile);
            }
            
            submitCaseForm(formData, 'update');
        });
    }

    // Initialize edit buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-btn')) {
            const caseId = e.target.closest('.edit-btn').getAttribute('data-id');
            editCase(caseId);
        }
    });
}

function editCase(caseId) {
    fetch(`../AdminBackEnd/VehicleRunsBE.php?action=get&id=${caseId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const caseData = data.caseData;
            const editModal = document.getElementById('editCaseModal');
            
            document.getElementById('editCaseId').value = caseData.id;
            document.getElementById('editVehicleTeam').value = caseData.vehicle_team;
            document.getElementById('editCaseType').value = caseData.case_type;
            document.getElementById('editTransportOfficer').value = caseData.transport_officer || '';
            document.getElementById('editEmergencyResponders').value = caseData.emergency_responders;
            document.getElementById('editLocation').value = caseData.location;
            
            const dispatchTime = new Date(caseData.dispatch_time);
            const backToBaseTime = new Date(caseData.back_to_base_time);
            
            document.getElementById('editDispatchTime').value = dispatchTime.toISOString().slice(0, 16);
            document.getElementById('editBackToBaseTime').value = backToBaseTime.toISOString().slice(0, 16);
            
            const currentImageContainer = document.getElementById('currentImageContainer');
            currentImageContainer.innerHTML = '';
            
            if (caseData.case_image) {
                currentImageContainer.innerHTML = `
                    <div class="current-image">
                        <p>Current Image:</p>
                        <img src="../../${caseData.case_image}" alt="Case Image" style="max-width: 100px; max-height: 100px;">
                        <button type="button" class="remove-image-btn" data-case-id="${caseData.id}">
                            <i class="fas fa-trash-alt"></i> Remove Image
                        </button>
                    </div>
                `;
                
                document.querySelector('.remove-image-btn')?.addEventListener('click', function() {
                    if (confirm('Are you sure you want to remove this image?')) {
                        removeCaseImage(caseData.id);
                    }
                });
            }
            
            editModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error loading case data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading case data');
    });
}

function removeCaseImage(caseId) {
    if (!caseId) {
        alert('Error: Missing case ID');
        return;
    }

    fetch(`../AdminBackEnd/VehicleRunsBE.php?action=removeImage`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: caseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Image removed successfully');
            document.getElementById('currentImageContainer').innerHTML = '';
            // Instead of reloading, just clear the current image display
            const editModal = document.getElementById('editCaseModal');
            if (editModal.style.display === 'flex') {
                // If modal is open, just update the display
                document.querySelector('#editCaseModal .file-upload-filename').textContent = 'No file chosen';
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to remove image'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing image');
    });
}

/////////////////////////
// TRANSPORT OFFICER AUTOCOMPLETE //
/////////////////////////
function initializeTransportOfficerAutocomplete() {
    const transportOfficerInput = document.getElementById('transportOfficer');
    const officerSuggestions = document.getElementById('officerSuggestions');
    const editTransportOfficerInput = document.getElementById('editTransportOfficer');
    const editOfficerSuggestions = document.getElementById('editOfficerSuggestions');

    function setupAutocomplete(inputElement, suggestionsElement) {
        if (!inputElement || !suggestionsElement) return;

        inputElement.addEventListener('input', function() {
            const query = this.value.trim();
            suggestionsElement.innerHTML = '';
            
            if (query.length > 1) {
                fetch(`../AdminBackEnd/VehicleRunsBE.php?action=searchOfficers&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.officers.length > 0) {
                        data.officers.forEach(officer => {
                            const suggestion = document.createElement('div');
                            suggestion.textContent = officer;
                            suggestion.classList.add('autocomplete-suggestion');
                            suggestion.addEventListener('click', function() {
                                inputElement.value = officer;
                                suggestionsElement.innerHTML = '';
                            });
                            suggestionsElement.appendChild(suggestion);
                        });
                        suggestionsElement.style.display = 'block';
                    } else {
                        suggestionsElement.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    suggestionsElement.style.display = 'none';
                });
            } else {
                suggestionsElement.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            if (!inputElement.contains(e.target)) {
                suggestionsElement.style.display = 'none';
            }
        });
    }

    setupAutocomplete(transportOfficerInput, officerSuggestions);
    setupAutocomplete(editTransportOfficerInput, editOfficerSuggestions);
}

// View Image & Data
function showImageModal(fileUrl, fileName, caseDetails = null) {
    const displayName = fileName || fileUrl.split('/').pop().split('?')[0];
    // Create modal container
    const modal = document.createElement('div');
    modal.className = 'file-preview-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.95);
        z-index: 10000;
        display: flex;
        flex-direction: column;
    `;

    // Create header
    const header = document.createElement('div');
    header.style.cssText = `
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        padding: 15px;
        background-color: #222;
        color: white;
    `;

    // Create title container (empty since we're removing the name)
    const titleContainer = document.createElement('div');
    titleContainer.style.cssText = `
        grid-column: 1;
        justify-self: start;
        max-width: 70%;
    `;

    // Create subtitle for Vehicle Team and Case Type if caseDetails exists
    if (caseDetails) {
        const subtitle = document.createElement('div');
        subtitle.style.cssText = `
            font-size: 14px;
            opacity: 0.8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        `;
        
        // Build subtitle text
        let subtitleText = '';
        if (caseDetails.vehicleTeam) subtitleText += `Team: ${caseDetails.vehicleTeam}`;
        if (caseDetails.caseType) subtitleText += ` | Type: ${caseDetails.caseType}`;
        
        subtitle.textContent = subtitleText;
        titleContainer.appendChild(subtitle);
    }

    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        padding: 0;
        grid-column: 3;
        justify-self: end;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    closeBtn.addEventListener('click', () => document.body.removeChild(modal));

    // Create action buttons container (empty now since we moved buttons to details)
    const actionsContainer = document.createElement('div');
    actionsContainer.style.cssText = `
        display: flex;
        gap: 10px;
        grid-column: 2;
        justify-self: center;
    `;

    // Create content container
    const content = document.createElement('div');
    content.style.cssText = `
        flex: 1;
        display: flex;
        padding: 20px;
        overflow: auto;
        gap: 20px;
    `;

    // Create image container (only if fileUrl exists)
    if (fileUrl) {
        const imageContainer = document.createElement('div');
        imageContainer.style.cssText = `
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 20px;
            max-height: calc(100vh - 180px);
        `;

        const img = document.createElement('img');
        img.src = fileUrl;
        img.style.cssText = `
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        `;
        img.alt = 'Preview Image';
        imageContainer.appendChild(img);
        content.appendChild(imageContainer);
    }

    // Create details container (if caseDetails exists)
    if (caseDetails) {
        const detailsContainer = document.createElement('div');
        detailsContainer.style.cssText = `
            width: ${fileUrl ? '300px' : '100%'};
            background-color: #2c3e50;
            border-radius: 8px;
            padding: 20px;
            color: white;
            overflow-y: auto;
            max-height: calc(100vh - 180px);
            position: relative;
        `;

        const detailsTitle = document.createElement('h3');
        detailsTitle.textContent = 'Case Information';
        detailsTitle.style.cssText = `
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        `;
        detailsContainer.appendChild(detailsTitle);

        // Create details table
        const detailsTable = document.createElement('div');
        detailsTable.style.cssText = `
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 12px 20px;
        `;

        // Helper function to add detail row
        const addDetailRow = (label, value) => {
            if (!value) return;
            
            const labelCell = document.createElement('div');
            labelCell.textContent = label;
            labelCell.style.cssText = `
                font-weight: bold;
                color: #ecf0f1;
                opacity: 0.8;
            `;
            
            const valueCell = document.createElement('div');
            valueCell.textContent = value;
            valueCell.style.cssText = `
                word-break: break-word;
            `;
            
            detailsTable.appendChild(labelCell);
            detailsTable.appendChild(valueCell);
        };

        // Add all case details
        addDetailRow('Vehicle Team:', caseDetails.vehicleTeam);
        addDetailRow('Case Type:', caseDetails.caseType);
        addDetailRow('Transport Officer:', caseDetails.transportOfficer);
        addDetailRow('Emergency Responders:', caseDetails.emergencyResponders);
        addDetailRow('Location:', caseDetails.location);
        addDetailRow('Dispatch Time:', formatDateTime(caseDetails.dispatchTime));
        addDetailRow('Back to Base:', formatDateTime(caseDetails.backToBaseTime));

        detailsContainer.appendChild(detailsTable);
        
        // Add horizontal line
        const horizontalLine = document.createElement('hr');
        horizontalLine.style.cssText = `
            border: none;
            height: 1px;
            background-color: rgba(255,255,255,0.1);
            margin: 15px 0;
        `;
        detailsContainer.appendChild(horizontalLine);

        // Create buttons container
        const buttonsContainer = document.createElement('div');
        buttonsContainer.style.cssText = `
            display: flex;
            gap: 10px;
            margin-top: 20px;
        `;

        // Create download all button
        const downloadAllBtn = document.createElement('button');
        downloadAllBtn.innerHTML = '<i class="fas fa-download"></i> Download All';
        downloadAllBtn.style.cssText = `
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            flex: 1;
        `;
        downloadAllBtn.addEventListener('mouseenter', () => {
            downloadAllBtn.style.backgroundColor = '#2980b9';
        });
        downloadAllBtn.addEventListener('mouseleave', () => {
            downloadAllBtn.style.backgroundColor = '#3498db';
        });
        downloadAllBtn.addEventListener('click', () => {
            downloadAllContentAsPDF(fileUrl, fileName, caseDetails);
        });
        buttonsContainer.appendChild(downloadAllBtn);

        // Create print button
        const printBtn = document.createElement('button');
        printBtn.innerHTML = '<i class="fas fa-print"></i> Print';
        printBtn.style.cssText = `
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            flex: 1;
        `;
        printBtn.addEventListener('mouseenter', () => {
            printBtn.style.backgroundColor = '#2980b9';
        });
        printBtn.addEventListener('mouseleave', () => {
            printBtn.style.backgroundColor = '#3498db';
        });
        printBtn.addEventListener('click', () => {
            printContentAsPDF(fileUrl, fileName, caseDetails);
        });
        buttonsContainer.appendChild(printBtn);

        detailsContainer.appendChild(buttonsContainer);
        content.appendChild(detailsContainer);
    }

    // Helper function to format date/time
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString();
    }


 // Function to download all content as PDF
function downloadAllContentAsPDF(fileUrl, fileName, caseDetails) {
    // Show loading indicator
    const loadingIndicator = document.createElement('div');
    loadingIndicator.textContent = 'Preparing PDF...';
    loadingIndicator.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        z-index: 10001;
    `;
    document.body.appendChild(loadingIndicator);

    // Create HTML content
    const htmlContent = `
    <html>
        <head>
            <title>${fileName || 'Case Details'}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
                .logo-container { width: 150px; }
                .logo { max-width: 100%; height: auto; max-height: 80px; }
                .header-content { flex: 1; text-align: center; }
                .team-type-container { 
                    text-align: center; 
                    margin-bottom: 20px;
                    font-size: 16px;
                    color: #555;
                }
                .generated-on { font-size: 12px; color: #666; text-align: right; margin-top: 10px; }
                .section { margin-bottom: 30px; }
                .image-container { text-align: center; margin-bottom: 20px; }
                .image { max-width: 100%; max-height: 500px; }
                .details { margin-top: 20px; }
                .detail-row { margin-bottom: 8px; }
                .detail-label { font-weight: bold; display: inline-block; width: 180px; }
                hr { border: none; border-top: 1px solid #ccc; margin: 20px 0; }
                .footer { font-size: 12px; color: #666; text-align: center; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-container">
                    <img class="logo" src="../../assets/img/Logo.png" alt="Organization Logo">
                </div>
                <div class="header-content">
                    <!-- Empty now since we moved the title -->
                </div>
                <div style="width: 150px;"></div>
            </div>
            
            <div class="team-type-container">
                ${caseDetails ? `Team: ${caseDetails.vehicleTeam || 'N/A'} | Type: ${caseDetails.caseType || 'N/A'}` : ''}
            </div>
            
            ${fileUrl ? `
            <div class="section">
                <div class="image-container">
                    <img class="image" src="${fileUrl}" alt="Case Image" onerror="this.style.display='none'">
                </div>
            </div>
            ` : ''}
            
            ${caseDetails ? `
            <div class="section details">
                <div class="generated-on">Generated on: ${new Date().toLocaleString()}</div>
                <h3>Case Information</h3>
                <hr>
                <div class="detail-row"><span class="detail-label">Vehicle Team:</span> ${caseDetails.vehicleTeam || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Case Type:</span> ${caseDetails.caseType || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Transport Officer:</span> ${caseDetails.transportOfficer || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Emergency Responders:</span> ${caseDetails.emergencyResponders || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Location:</span> ${caseDetails.location || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Dispatch Time:</span> ${formatDateTime(caseDetails.dispatchTime)}</div>
                <div class="detail-row"><span class="detail-label">Back to Base:</span> ${formatDateTime(caseDetails.backToBaseTime)}</div>
            </div>
            ` : ''}
            
            <div class="footer">
                Generated by San Juan City Disaster Risk Reduction Management
            </div>
        </body>
    </html>
`;

    // Create a temporary element to hold our HTML
    const element = document.createElement('div');
    element.innerHTML = htmlContent;
    document.body.appendChild(element);
    
    // Determine the filename - use caseType if available, otherwise fall back to the provided fileName or default
    const pdfFilename = caseDetails?.caseType ? caseDetails.caseType : (fileName || 'case_details');
    
    // Options for html2pdf
    const opt = {
        margin: 10,
        filename: `${pdfFilename.replace(/\.[^/.]+$/, '')}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            logging: true,
            allowTaint: true
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Generate PDF
    html2pdf().set(opt).from(element).save()
        .then(() => {
            document.body.removeChild(loadingIndicator);
            document.body.removeChild(element);
        })
        .catch(err => {
            console.error('PDF generation failed:', err);
            alert('Failed to generate PDF. Please check console for details.');
            document.body.removeChild(loadingIndicator);
            document.body.removeChild(element);
        });
}

// Function to print content as PDF
function printContentAsPDF(fileUrl, fileName, caseDetails) {
    // Show loading indicator
    const loadingIndicator = document.createElement('div');
    loadingIndicator.textContent = 'Preparing for printing...';
    loadingIndicator.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        z-index: 10001;
    `;
    document.body.appendChild(loadingIndicator);

    // Create HTML content
    const htmlContent = `
    <html>
        <head>
            <title>${fileName || 'Case Details'}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
                .logo-container { width: 150px; }
                .logo { max-width: 100%; height: auto; max-height: 80px; }
                .header-content { flex: 1; text-align: center; }
                .team-type-container { 
                    text-align: center; 
                    margin-bottom: 20px;
                    font-size: 16px;
                    color: #555;
                }
                .section { margin-bottom: 30px; }
                .image-container { text-align: center; margin-bottom: 20px; }
                .image { max-width: 100%; max-height: 500px; }
                .details { margin-top: 20px; position: relative; }
                .detail-row { margin-bottom: 8px; }
                .detail-label { font-weight: bold; display: inline-block; width: 180px; }
                hr { border: none; border-top: 1px solid #ccc; margin: 20px 0; }
                .footer { font-size: 12px; color: #666; text-align: center; margin-top: 30px; }
                .generated-on { 
                    position: absolute;
                    right: 0;
                    top: 0;
                    font-size: 12px; 
                    color: #666; 
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-container">
                    <img class="logo" src="../../assets/img/Logo.png" alt="Organization Logo">
                </div>
                <div class="header-content">
                    <!-- Empty now since we moved the title -->
                </div>
                <div style="width: 150px;"></div>
            </div>
            
            <div class="team-type-container">
                ${caseDetails ? `Team: ${caseDetails.vehicleTeam || 'N/A'} | Type: ${caseDetails.caseType || 'N/A'}` : ''}
            </div>
            
            ${fileUrl ? `
            <div class="section">
                <div class="image-container">
                    <img class="image" src="${fileUrl}" alt="Case Image" onerror="this.style.display='none'">
                </div>
            </div>
            ` : ''}
            
            ${caseDetails ? `
            <div class="section details">
                <div class="generated-on">Generated on: ${new Date().toLocaleString()}</div>
                <h3>Case Information</h3>
                <hr>
                <div class="detail-row"><span class="detail-label">Vehicle Team:</span> ${caseDetails.vehicleTeam || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Case Type:</span> ${caseDetails.caseType || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Transport Officer:</span> ${caseDetails.transportOfficer || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Emergency Responders:</span> ${caseDetails.emergencyResponders || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Location:</span> ${caseDetails.location || 'N/A'}</div>
                <div class="detail-row"><span class="detail-label">Dispatch Time:</span> ${formatDateTime(caseDetails.dispatchTime)}</div>
                <div class="detail-row"><span class="detail-label">Back to Base:</span> ${formatDateTime(caseDetails.backToBaseTime)}</div>
            </div>
            ` : ''}
            
            <div class="footer">
                Generated by San Juan City Disaster Risk Reduction Management
            </div>
        </body>
    </html>
`;

    // Create a temporary element to hold our HTML
    const element = document.createElement('div');
    element.innerHTML = htmlContent;
    document.body.appendChild(element);
    
    // Determine the filename - use caseType if available, otherwise fall back to the provided fileName or default
    const pdfFilename = caseDetails?.caseType ? caseDetails.caseType : (fileName || 'case_details');
    
    // Options for html2pdf
    const opt = {
        margin: 10,
        filename: `${pdfFilename.replace(/\.[^/.]+$/, '')}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            logging: true,
            allowTaint: true
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Generate PDF and open print dialog
    html2pdf().set(opt).from(element).toPdf().get('pdf').then(function(pdf) {
        document.body.removeChild(loadingIndicator);
        document.body.removeChild(element);
        
        // Create a blob URL and open in new window for printing
        const blobUrl = pdf.output('bloburl');
        const printWindow = window.open(blobUrl, '_blank');
        
        // Add a fallback in case popup is blocked
        if (!printWindow) {
            alert('Pop-up was blocked. Please allow pop-ups for this site to print.');
            // Alternative: download the PDF instead
            html2pdf().set(opt).from(element).save();
        }
    }).catch(err => {
        console.error('Print failed:', err);
        alert('Failed to prepare for printing. Please check console for details.');
        document.body.removeChild(loadingIndicator);
        document.body.removeChild(element);
    });
}
    // Assemble the modal
    header.appendChild(titleContainer);
    header.appendChild(actionsContainer);
    header.appendChild(closeBtn);
    
    modal.appendChild(header);
    modal.appendChild(content);

    // Add to document
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Close on ESC key
    document.addEventListener('keydown', function handleKeyDown(e) {
        if (e.key === 'Escape') {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleKeyDown);
        }
    });

    // Close on click outside content
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleKeyDown);
        }
    });
}

// Initialize image preview links with case details
document.addEventListener("DOMContentLoaded", function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.image-preview-link')) {
            e.preventDefault();
            const link = e.target.closest('.image-preview-link');
            const imageUrl = link.getAttribute('href');
            // Get the file name from a data attribute or parse it from the URL
            const fileName = link.dataset.filename || imageUrl.split('/').pop().split('?')[0];
            
            // Get case details from the table row
            const row = link.closest('tr');
            const caseDetails = {
                vehicleTeam: row.querySelector('td:nth-child(2)').textContent,
                caseType: row.querySelector('td:nth-child(3)').textContent,
                transportOfficer: row.querySelector('td:nth-child(4)').textContent,
                emergencyResponders: row.querySelector('td:nth-child(5)').textContent,
                location: row.querySelector('td:nth-child(6)').textContent,
                dispatchTime: row.querySelector('td:nth-child(7)').textContent,
                backToBaseTime: row.querySelector('td:nth-child(8)').textContent
            };
            
            showImageModal(imageUrl, fileName, caseDetails);
        }
    });
});