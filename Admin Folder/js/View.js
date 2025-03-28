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

// Handling Modal Viewing
document.addEventListener("DOMContentLoaded", function() {
    // Configuration - Set your base upload path here
    const BASE_UPLOADS_PATH = '../../uploads/';
    const ABSOLUTE_UPLOADS_PATH = '/uploads/'; // Alternative absolute path

    // Handle all file link clicks
    document.querySelectorAll('.file-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const fileType = this.getAttribute('data-type').toLowerCase();
            const fileName = this.getAttribute('data-filename') || '';
            let fileUrl = this.getAttribute('href');
            
            // Normalize file paths (only if needed)
            if (!fileUrl.startsWith('http') && !fileUrl.startsWith('/')) {
                if (fileUrl.startsWith('../uploads/')) {
                    fileUrl = fileUrl.replace('../uploads/', BASE_UPLOADS_PATH);
                }
                else if (fileUrl.startsWith('uploads/')) {
                    // Choose one of these path options:
                    fileUrl = BASE_UPLOADS_PATH + fileUrl.substring('uploads/'.length);
                    // OR for absolute paths:
                    // fileUrl = ABSOLUTE_UPLOADS_PATH + fileUrl.substring('uploads/'.length);
                }
            }

            // Handle different file types
            if (fileType.match(/^image\//) || 
                fileType === 'application/pdf' || 
                fileType.match(/^video\//)) {
                // Previewable files - open in modal
                e.preventDefault();
                openFilePreview(fileUrl, fileType, fileName);
            }
            else if (fileType.includes('msword') || 
                     fileType.includes('wordprocessingml') ||
                     fileType.includes('ms-excel') || 
                     fileType.includes('spreadsheetml')) {
                // Office files - force download
                e.preventDefault();
                forceFileDownload(fileUrl, fileName);
            }
            // Other files will follow default behavior
        });
    });
});

/**
 * Opens a file preview modal
 * @param {string} fileUrl - File URL
 * @param {string} fileType - File MIME type
 * @param {string} fileName - Display name
 */
function openFilePreview(fileUrl, fileType, fileName) {
    // Create modal container
    const modal = document.createElement('div');
    modal.id = 'file-preview-modal';
    Object.assign(modal.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        backgroundColor: 'rgba(0,0,0,0.95)',
        zIndex: '10000',
        display: 'flex',
        flexDirection: 'column'
    });

    // Create modal header
    const header = document.createElement('div');
    Object.assign(header.style, {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: '15px',
        backgroundColor: '#222',
        color: 'white'
    });

    // Create title
    const title = document.createElement('h3');
    title.textContent = fileName;
    Object.assign(title.style, {
        margin: '0',
        fontSize: '18px',
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        maxWidth: '60%'
    });

    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times; Close';
    Object.assign(closeBtn.style, {
        background: 'none',
        border: 'none',
        color: 'white',
        fontSize: '16px',
        cursor: 'pointer',
        padding: '5px 10px'
    });
    closeBtn.addEventListener('click', () => closeModal(modal));

    // Create action buttons container
    const actions = document.createElement('div');
    actions.style.display = 'flex';
    actions.style.gap = '10px';

    // Create download button
    const downloadBtn = document.createElement('a');
    downloadBtn.href = fileUrl;
    downloadBtn.download = fileName;
    downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download';
    Object.assign(downloadBtn.style, {
        padding: '5px 10px',
        backgroundColor: '#4CAF50',
        color: 'white',
        borderRadius: '4px',
        textDecoration: 'none',
        display: 'inline-flex',
        alignItems: 'center',
        gap: '5px'
    });

    // Create content area
    const content = document.createElement('div');
    Object.assign(content.style, {
        flex: '1',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        padding: '20px',
        overflow: 'auto'
    });

    // Handle different file types
    if (fileType.match(/^image\//)) {
        const img = document.createElement('img');
        img.src = fileUrl;
        Object.assign(img.style, {
            maxWidth: '100%',
            maxHeight: '90vh',
            objectFit: 'contain'
        });
        content.appendChild(img);
    } 
    else if (fileType === 'application/pdf') {
        const embed = document.createElement('embed');
        embed.src = fileUrl + '#toolbar=1&navpanes=0';
        embed.type = 'application/pdf';
        Object.assign(embed.style, {
            width: '100%',
            height: '100%',
            minHeight: '80vh'
        });
        content.appendChild(embed);
    }
    else if (fileType.match(/^video\//)) {
        const video = document.createElement('video');
        video.src = fileUrl;
        video.controls = true;
        video.autoplay = true;
        Object.assign(video.style, {
            width: '100%',
            maxHeight: '90vh'
        });
        content.appendChild(video);
    }

    // Assemble modal
    actions.appendChild(downloadBtn);
    header.appendChild(title);
    header.appendChild(actions);
    header.appendChild(closeBtn);
    modal.appendChild(header);
    modal.appendChild(content);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Add keyboard and clickaway listeners
    const keyHandler = (e) => e.key === 'Escape' && closeModal(modal);
    document.addEventListener('keydown', keyHandler);
    modal.addEventListener('click', (e) => e.target === modal && closeModal(modal));

    // Cleanup function
    function closeModal() {
        document.body.removeChild(modal);
        document.body.style.overflow = '';
        document.removeEventListener('keydown', keyHandler);
    }
}

/**
 * Forces file download
 * @param {string} url - File URL
 * @param {string} filename - Suggested filename
 */
function forceFileDownload(url, filename) {
    // Create hidden iframe for download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    
    // Fallback for browsers that block iframe downloads
    iframe.onload = function() {
        setTimeout(() => {
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => document.body.removeChild(a), 100);
        }, 100);
    };
    
    document.body.appendChild(iframe);
    setTimeout(() => document.body.removeChild(iframe), 5000);
}

// Your existing modal functions
function openEditModal(fileId, fileName, temperature, waterLevel, airQuality) {
    // Existing implementation
}

function openDeleteModal(fileId, fileName) {
    // Existing implementation
}