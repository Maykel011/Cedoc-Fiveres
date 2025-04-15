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
            showSuccessModal('Case deleted successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
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
    }, 1500);
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
            showSuccessModal(`${data.deletedCount} ${data.deletedCount > 1 ? 'cases' : 'case'} deleted successfully!`);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
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
        if (e.target === uploadModal) {
            closeUploadModal();
        }
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
                fileNameDisplay.textContent = selectedFile.name;
            } else {
                selectedFile = null;
                fileNameDisplay.textContent = 'No file chosen';
            }
        });
        
        document.body.appendChild(fileInput);
        fileInput.click();
        
        setTimeout(() => {
            document.body.removeChild(fileInput);
        }, 1000);
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
                formData.append('caseImage', selectedFile);
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
                }, 1500);
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
        if (e.target === editModal) {
            closeEditModal();
        }
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
            } else {
                editSelectedFile = null;
                editFileNameDisplay.textContent = 'No file chosen';
            }
        });
        
        document.body.appendChild(fileInput);
        fileInput.click();
        
        setTimeout(() => {
            document.body.removeChild(fileInput);
        }, 1000);
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