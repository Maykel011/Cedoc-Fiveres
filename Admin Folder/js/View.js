// Handle Profile Dropdown
document.addEventListener("DOMContentLoaded", function () {
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");

    if (userContainer && userDropdown) {
        userContainer.addEventListener("click", function (event) {
            event.stopPropagation();
            userDropdown.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {
            if (!userContainer.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.remove("show");
            }
        });
    }
});

// Handle Search Filter
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.querySelector(".search-input");
    const filterSelect = document.querySelector(".filter-select");
    const tableBody = document.querySelector("tbody");

    if (!tableBody) {
        console.error("Error: Table body element not found.");
        return;
    }

    // 🔍 Handle Search Functionality
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const searchValue = this.value.toLowerCase();
            document.querySelectorAll("tbody tr").forEach(row => {
                const folderName = row.children[0].textContent.toLowerCase();
                row.style.display = folderName.includes(searchValue) ? "" : "none";
            });
        });
    }

    // 📂 Handle Sorting Functionality
    if (filterSelect) {
        filterSelect.addEventListener("change", function () {
            const selectedFilter = this.value; // "name" or "date"
            let rows = Array.from(tableBody.querySelectorAll("tr"));

            if (selectedFilter === "name") {
                // Sort alphabetically by folder name
                rows.sort((a, b) => {
                    const nameA = a.children[0].textContent.trim().toLowerCase();
                    const nameB = b.children[0].textContent.trim().toLowerCase();
                    return nameA.localeCompare(nameB);
                });
            } else if (selectedFilter === "date") {
                // Sort by Date Modified (newest to oldest)
                rows.sort((a, b) => {
                    const dateA = parseDate(a.children[1].textContent);
                    const dateB = parseDate(b.children[1].textContent);
                    return dateB - dateA; // Descending order
                });
            }

            // Clear and re-append sorted rows
            tableBody.innerHTML = "";
            rows.forEach(row => tableBody.appendChild(row));
        });
    }

    // 🗓️ Function to Parse Date
    function parseDate(dateString) {
        const date = new Date(dateString);
        return isNaN(date) ? new Date(0) : date; // Default to old date if invalid
    }
});


// Handle Create Folder
document.addEventListener("DOMContentLoaded", function () {
    const createFolderBtn = document.querySelector(".create-folder-btn");

    if (createFolderBtn) {
        createFolderBtn.addEventListener("click", function () {
            const folderInput = document.querySelector(".folder-name-input");
            const folderName = folderInput.value.trim();

            if (folderName === "") {
                showModal("errorModal", "Please enter a folder name.");
                return;
            }

            fetch("../AdminBackEnd/MediaFilesBE.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=create&folder_name=${encodeURIComponent(folderName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    setTimeout(() => location.reload(), 300); // Quick reload after success
                } else {
                    showModal("errorModal", data.message);
                }
            });
        });
    }
});

// Function to show error modal with dynamic message
function showModal(modalId, message) {
    const modal = document.getElementById(modalId);
    const modalMessage = modal.querySelector(".modal-message");

    if (modalMessage) {
        modalMessage.textContent = message;
    }

    modal.style.display = "flex";
}

// Function to close modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}


// Function to show modal with dynamic message
function showModal(modalId, message) {
    const modal = document.getElementById(modalId);
    const modalMessage = modal.querySelector(".modal-message");

    if (modalMessage) {
        modalMessage.textContent = message;
    }

    modal.style.display = "flex";
}

// Function to close modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}


// Function to show modal
function showModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
}

// Function to close modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}



//handling file upload
document.getElementById("uploadBtn").addEventListener("click", function() {
    document.getElementById("uploadModal").style.display = "block";
});

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

function showModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}

document.getElementById("uploadForm")?.addEventListener("submit", function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    
    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            closeModal('uploadModal');
            document.getElementById('uploadSuccessMessage').textContent = data.message;
            showModal('uploadSuccessModal');

            // Auto-close after 1.5 seconds and refresh
            setTimeout(() => {
                closeModal('uploadSuccessModal');
                location.reload();
            }, 1000);
        } else {
            document.getElementById('uploadErrorMessage').textContent = data.message;
            showModal('uploadErrorModal');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('uploadErrorMessage').textContent = 'An error occurred during upload.';
        showModal('uploadErrorModal');
    });
});





//Handling rename & Deleting
// Open edit modal
function openEditModal(id, fileName, temp, water, air) {
    document.getElementById("editFileId").value = id;
    document.getElementById("editFileName").value = fileName;
    document.getElementById("editTemperature").value = temp || '';
    document.getElementById("editWaterLevel").value = water || '';
    document.getElementById("editAirQuality").value = air || '';
    document.getElementById("editModal").style.display = "flex";
}

// Close any modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

// Show success modal, auto-close after 500ms, then refresh
function showSuccessModal(message) {
    document.getElementById("deleteSuccessMessage").innerText = message;
    const successModal = document.getElementById("deleteSuccessModal");
    successModal.style.display = "block";

    // Auto-close after 500ms and refresh the page
    setTimeout(() => {
        closeModal("deleteSuccessModal");
        location.reload(); // Refresh the page
    }, 1000);
}

// Open delete confirmation modal
function openDeleteModal(itemId, itemName, isFolder = false, element) {
    document.getElementById("deleteFileName").innerText = isFolder ? `Folder: ${itemName}` : `File: ${itemName}`;
    document.getElementById("deleteFileBtn").setAttribute("data-id", itemId);
    document.getElementById("deleteFileBtn").setAttribute("data-type", isFolder ? "folder" : "file");
    document.getElementById("deleteFileBtn").setAttribute("data-element", element);
    document.getElementById("deleteModal").style.display = "block";
}

// Delete file function
function deleteFile(fileId, element) {
    fetch(window.location.href, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=deleteFile&file_id=${fileId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            closeModal("deleteModal");
            showSuccessModal("File deleted successfully");
        } else {
            alert(data.message);
        }
    });
}


// Handle delete button click
document.getElementById("deleteFileBtn").addEventListener("click", function () {
    let itemId = this.getAttribute("data-id");
    let itemType = this.getAttribute("data-type");

    if (itemType === "folder") {
        deleteFolder(itemId);
    } else {
        deleteFile(itemId);
    }
});


// Global variables to track selections
let selectedFiles = [];
let currentFolderName = '';

// Handle checkbox selection
document.addEventListener("DOMContentLoaded", function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected_files[]"]');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    // Update delete button state based on selections
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('input[type="checkbox"][name="selected_files[]"]:checked').length;
            deleteSelectedBtn.disabled = checkedCount === 0;
        });
    });
    
    // Handle delete selected button click
    deleteSelectedBtn.addEventListener('click', function() {
        selectedFiles = [];
        document.querySelectorAll('input[type="checkbox"][name="selected_files[]"]:checked').forEach(checkbox => {
            selectedFiles.push(checkbox.value);
        });
        
        currentFolderName = document.querySelector('.main-title').textContent.trim();
        
        if (selectedFiles.length === 0) {
            return; // Simply return without any notification
        }
        
        // Show confirmation modal
        document.getElementById('multipleDeleteMessage').textContent = 
            `Are you sure you want to delete ${selectedFiles.length} selected file(s)?`;
        showModal('multipleDeleteModal');
    });
});

// Handle confirmed multiple delete
document.getElementById('confirmMultipleDelete')?.addEventListener('click', function() {
    closeModal('multipleDeleteModal');
    
    fetch(window.location.href, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=deleteMultipleFiles&selected_files[]=${selectedFiles.join('&selected_files[]=')}&folder_name=${encodeURIComponent(currentFolderName)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            showSuccessModal(data.message);
            setTimeout(() => location.reload(), 1000);
        }
        // Silently ignore errors
    })
    .catch(error => {
        console.error('Error:', error);
        // Silently ignore errors
    });
});

// Modal control functions
function showModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// View
document.addEventListener("DOMContentLoaded", function() {
    // Base path to uploads directory (adjust according to your structure)
    const BASE_UPLOADS_PATH = '../../uploads/'; // Or '/Admin Folder/uploads/' if that's your structure
    
    // Handle file link clicks
    document.querySelectorAll('.file-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const fileType = this.getAttribute('data-type');
            let fileUrl = this.getAttribute('href');
            
            // Fix the path if it's using relative notation
            if (fileUrl.startsWith('../uploads/')) {
                fileUrl = fileUrl.replace('../uploads/', BASE_UPLOADS_PATH);
            }
            else if (fileUrl.startsWith('uploads/')) {
                fileUrl = BASE_UPLOADS_PATH + fileUrl.substring('uploads/'.length);
            }
            
            // Prevent default for certain file types we want to handle specially
            if (fileType.match(/^image\//) || 
                fileType === 'application/pdf' || 
                fileType.match(/^video\//)) {
                e.preventDefault();
                openFilePreview(fileUrl, fileType);
            }
            // Other files (Word, Excel, etc.) will open normally
        });
    });
});

function openFilePreview(fileUrl, fileType) {
    // Create preview modal
    const previewModal = document.createElement('div');
    previewModal.id = 'filePreviewModal';
    previewModal.style.display = 'block';
    previewModal.style.position = 'fixed';
    previewModal.style.zIndex = '1000';
    previewModal.style.left = '0';
    previewModal.style.top = '0';
    previewModal.style.width = '100%';
    previewModal.style.height = '100%';
    previewModal.style.backgroundColor = 'rgba(0,0,0,0.8)';
    previewModal.style.overflow = 'auto';
    
    // Create close button
    const closeBtn = document.createElement('span');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.position = 'absolute';
    closeBtn.style.right = '35px';
    closeBtn.style.top = '15px';
    closeBtn.style.color = 'white';
    closeBtn.style.fontSize = '40px';
    closeBtn.style.fontWeight = 'bold';
    closeBtn.style.cursor = 'pointer';
    closeBtn.addEventListener('click', () => {
        document.body.removeChild(previewModal);
    });
    
    // Create content container
    const content = document.createElement('div');
    content.style.display = 'flex';
    content.style.justifyContent = 'center';
    content.style.alignItems = 'center';
    content.style.height = '100%';
    
    // Handle different file types
    if (fileType.match(/^image\//)) {
        // Image preview
        const img = document.createElement('img');
        img.src = fileUrl;
        img.style.maxWidth = '90%';
        img.style.maxHeight = '90%';
        img.style.objectFit = 'contain';
        content.appendChild(img);
    } 
    else if (fileType === 'application/pdf') {
        // PDF preview using PDF.js or embed
        const embed = document.createElement('embed');
        embed.src = fileUrl;
        embed.type = 'application/pdf';
        embed.style.width = '90%';
        embed.style.height = '90%';
        content.appendChild(embed);
    }
    else if (fileType.match(/^video\//)) {
        // Video preview
        const video = document.createElement('video');
        video.src = fileUrl;
        video.controls = true;
        video.autoplay = true;
        video.style.maxWidth = '90%';
        video.style.maxHeight = '90%';
        content.appendChild(video);
    }
    
    // Add elements to modal
    previewModal.appendChild(closeBtn);
    previewModal.appendChild(content);
    document.body.appendChild(previewModal);
    
    // Close when clicking outside content
    previewModal.addEventListener('click', (e) => {
        if (e.target === previewModal) {
            document.body.removeChild(previewModal);
        }
    });
}