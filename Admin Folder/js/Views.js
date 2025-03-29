// ========== MODAL CONTROL FUNCTIONS ==========
function showModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
    document.body.style.overflow = "hidden";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
    document.body.style.overflow = "";
}

function showSuccessMessage(message) {
    document.getElementById("deleteSuccessMessage").textContent = message;
    showModal("deleteSuccessModal");
    setTimeout(() => closeModal("deleteSuccessModal"), 2000);
}

// ========== ENHANCED SEARCH FUNCTIONALITY ==========
function setupSearch() {
    const searchInput = document.querySelector(".search-input");
    if (!searchInput) return;

    searchInput.addEventListener("input", function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll("tbody tr");

        rows.forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const type = row.cells[3].textContent.toLowerCase();
            const temp = row.cells[4].textContent.toLowerCase();
            const water = row.cells[5].textContent.toLowerCase();
            const air = row.cells[6].textContent.toLowerCase();

            const matchFound = name.includes(searchValue) || 
                             type.includes(searchValue) ||
                             temp.includes(searchValue) || 
                             water.includes(searchValue) || 
                             air.includes(searchValue);

            row.style.display = matchFound ? "" : "none";
        });
    });
}

// ========== SORTING FUNCTIONALITY ==========
function setupSorting() {
    const filterSelect = document.querySelector(".filter-select");
    if (!filterSelect) return;

    filterSelect.addEventListener("change", function() {
        const selectedFilter = this.value;
        const tbody = document.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        if (selectedFilter === "name") {
            rows.sort((a, b) => {
                const nameA = a.cells[1].textContent.trim().toLowerCase();
                const nameB = b.cells[1].textContent.trim().toLowerCase();
                return nameA.localeCompare(nameB);
            });
        } else if (selectedFilter === "date") {
            rows.sort((a, b) => {
                const dateA = new Date(a.cells[2].textContent.trim());
                const dateB = new Date(b.cells[2].textContent.trim());
                return dateB - dateA;
            });
        }

        tbody.innerHTML = "";
        rows.forEach(row => tbody.appendChild(row));
    });
}

// ========== EDIT MODAL FUNCTIONALITY ==========
function openEditModal(id, fileName, temp, water, air) {
    document.getElementById("editFileId").value = id;
    document.getElementById("editFileName").value = fileName;
    document.getElementById("editTemperature").value = temp || '';
    document.getElementById("editWaterLevel").value = water || '';
    document.getElementById("editAirQuality").value = air || '';
    showModal("editModal");
}

// ========== DELETE MODAL FUNCTIONALITY ==========
function openDeleteModal(id, fileName) {
    document.getElementById("deleteFileName").textContent = `File: ${fileName}`;
    document.getElementById("deleteFileBtn").dataset.id = id;
    showModal("deleteModal");
}

// ========== DELETE FUNCTIONALITY ==========
function setupDeleteHandlers() {
    // Single file delete
    document.getElementById("deleteFileBtn")?.addEventListener("click", function() {
        const fileId = this.dataset.id;
        
        fetch(window.location.href, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=deleteFile&file_id=${fileId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                closeModal("deleteModal");
                showSuccessMessage("File deleted successfully");
                setTimeout(() => location.reload(), 1000);
            } else {
                alert("Error: " + data.message);
            }
        });
    });

    // Multiple files delete
    const deleteSelectedBtn = document.getElementById("deleteSelectedBtn");
    const checkboxes = document.querySelectorAll('input[name="selected_files[]"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            const checked = document.querySelectorAll('input[name="selected_files[]"]:checked');
            deleteSelectedBtn.disabled = checked.length === 0;
        });
    });
    
    deleteSelectedBtn?.addEventListener("click", function() {
        const selected = Array.from(document.querySelectorAll('input[name="selected_files[]"]:checked'))
                            .map(checkbox => checkbox.value);
        
        if (selected.length === 0) return;
        
        document.getElementById("multipleDeleteMessage").textContent = 
            `Are you sure you want to delete ${selected.length} selected file(s)?`;
        showModal("multipleDeleteModal");
    });

    document.getElementById("confirmMultipleDelete")?.addEventListener("click", function() {
        const selected = Array.from(document.querySelectorAll('input[name="selected_files[]"]:checked'))
                            .map(checkbox => checkbox.value);
        const folderName = document.querySelector(".main-title").textContent.trim();
        
        fetch(window.location.href, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=deleteMultipleFiles&selected_files[]=${selected.join('&selected_files[]=')}&folder_name=${encodeURIComponent(folderName)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                closeModal("multipleDeleteModal");
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                alert("Error: " + data.message);
            }
        });
    }, { once: true });
}

// ========== FILE PREVIEW FUNCTIONALITY ==========
function setupFilePreview() {
    const BASE_UPLOADS_PATH = '../../uploads/';

    document.querySelectorAll('.file-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const fileType = this.getAttribute('data-type').toLowerCase();
            const fileName = this.getAttribute('data-filename') || '';
            let fileUrl = this.getAttribute('href');
            
            if (!fileUrl.startsWith('http') && !fileUrl.startsWith('/')) {
                if (fileUrl.startsWith('../uploads/')) {
                    fileUrl = fileUrl.replace('../uploads/', BASE_UPLOADS_PATH);
                }
                else if (fileUrl.startsWith('uploads/')) {
                    fileUrl = BASE_UPLOADS_PATH + fileUrl.substring('uploads/'.length);
                }
            }

            if (fileType.match(/^image\//) || 
                fileType === 'application/pdf' || 
                fileType.match(/^video\//)) {
                e.preventDefault();
                openFilePreview(fileUrl, fileType, fileName);
            }
            else if (fileType.includes('msword') || 
                     fileType.includes('wordprocessingml') ||
                     fileType.includes('ms-excel') || 
                     fileType.includes('spreadsheetml')) {
                e.preventDefault();
                openOfficePreview(fileUrl, fileName, fileType);
            }
        });
    });
}

function openFilePreview(fileUrl, fileType, fileName) {
    const modal = document.createElement('div');
    modal.id = 'file-preview-modal';
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

    const header = document.createElement('div');
    header.style.cssText = `
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        padding: 15px;
        background-color: #222;
        color: white;
        position: relative;
    `;

    const title = document.createElement('h3');
    title.textContent = fileName;
    title.style.cssText = `
        margin: 0;
        font-size: 18px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        grid-column: 1;
        justify-self: start;
    `;

    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = 'Close';
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        padding: 5px 10px;
        grid-column: 3;
        justify-self: end;
    `;
    closeBtn.addEventListener('click', () => document.body.removeChild(modal));

    const downloadBtn = document.createElement('a');
    downloadBtn.href = fileUrl;
    downloadBtn.download = fileName;
    downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download';
    downloadBtn.style.cssText = `
        padding: 5px 10px;
        background-color: #4CAF50;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        grid-column: 2;
        justify-self: center;
    `;

    const content = document.createElement('div');
    content.style.cssText = `
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        overflow: auto;
    `;

    if (fileType.match(/^image\//)) {
        const img = document.createElement('img');
        img.src = fileUrl;
        img.style.cssText = `
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        `;
        content.appendChild(img);
    } 
    else if (fileType === 'application/pdf') {
        const embed = document.createElement('embed');
        embed.src = fileUrl + '#toolbar=1&navpanes=1';
        embed.type = 'application/pdf';
        embed.style.cssText = `
            width: 100%;
            height: 100%;
            min-height: 80vh;
        `;
        content.appendChild(embed);
    }
    else if (fileType.match(/^video\//)) {
        const video = document.createElement('video');
        video.src = fileUrl;
        video.controls = true;
        video.autoplay = true;
        video.style.cssText = `
            width: 100%;
            max-height: 90vh;
        `;
        content.appendChild(video);
    }

    header.appendChild(title);
    header.appendChild(downloadBtn);
    header.appendChild(closeBtn);
    modal.appendChild(header);
    modal.appendChild(content);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    const keyHandler = (e) => e.key === 'Escape' && document.body.removeChild(modal);
    document.addEventListener('keydown', keyHandler);
    modal.addEventListener('click', (e) => e.target === modal && document.body.removeChild(modal));
}

function openOfficePreview(fileUrl, fileName, fileType) {
    // Check if we're on localhost or 127.0.0.1
    const isLocal = window.location.hostname === "localhost" || 
                   window.location.hostname === "127.0.0.1" ||
                   fileUrl.startsWith("file://");
    
    // Create modal
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

    // Create modal header
    const header = document.createElement('div');
    header.style.cssText = `
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        padding: 15px;
        background-color: #222;
        color: white;
    `;

    const title = document.createElement('h3');
    title.textContent = fileName;
    title.style.cssText = `
        margin: 0;
        font-size: 18px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        grid-column: 1;
        justify-self: start;
    `;

    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = 'Close';
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        padding: 5px 10px;
        grid-column: 3;
        justify-self: end;
    `;
    closeBtn.addEventListener('click', () => document.body.removeChild(modal));

    const downloadBtn = document.createElement('a');
    downloadBtn.href = fileUrl;
    downloadBtn.download = fileName;
    downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download';
    downloadBtn.style.cssText = `
        padding: 5px 10px;
        background-color: #4CAF50;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        grid-column: 2;
        justify-self: center;
    `;

    // Create modal content area
    const content = document.createElement('div');
    content.style.cssText = `
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        overflow: auto;
    `;

    // Create different content based on environment
    if (isLocal) {
        // Local development message
        const localMessage = document.createElement('div');
        localMessage.style.cssText = `
            text-align: center;
            color: white;
            padding: 20px;
            max-width: 600px;
        `;
        localMessage.innerHTML = `
            <h3>Office File Preview Not Available Locally</h3>
            <p>For security reasons, Office files cannot be previewed directly when running on localhost.</p>
            <p>Please download the file to view it, or deploy the application to your hosting server for full preview functionality.</p>
            <p>When deployed, this system will use Microsoft Office Online Viewer for seamless previews.</p>
        `;
        content.appendChild(localMessage);
    } else {
        // Hosted solution - use Office Online Viewer
        const officeViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
        
        const iframe = document.createElement('iframe');
        iframe.src = officeViewerUrl;
        iframe.style.cssText = `
            width: 100%;
            height: 100%;
            min-height: 500px;
            border: none;
        `;

        const fallback = document.createElement('div');
        fallback.style.cssText = `
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: white;
            color: #333;
        `;
        fallback.innerHTML = `
            <p>Could not load document preview</p>
            <button style="padding: 8px 16px; background: #4CAF50; color: white; border: none; border-radius: 4px; margin-top: 10px; cursor: pointer;">
                Try Again
            </button>
            <p style="margin-top: 20px; font-size: 14px;">If the preview continues to fail, please download the file to view it.</p>
        `;

        iframe.onerror = function() {
            iframe.style.display = 'none';
            fallback.style.display = 'flex';
        };

        fallback.querySelector('button').addEventListener('click', function() {
            iframe.src = officeViewerUrl + '&t=' + Date.now();
            fallback.style.display = 'none';
            iframe.style.display = 'block';
        });

        content.appendChild(iframe);
        content.appendChild(fallback);
    }

    // Assemble modal
    header.appendChild(title);
    header.appendChild(downloadBtn);
    header.appendChild(closeBtn);
    modal.appendChild(header);
    modal.appendChild(content);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Close modal on ESC or click outside
    const keyHandler = (e) => e.key === 'Escape' && document.body.removeChild(modal);
    document.addEventListener('keydown', keyHandler);
    modal.addEventListener('click', (e) => e.target === modal && document.body.removeChild(modal));
}


// ========== PROFILE DROPDOWN & LOGOUT FUNCTIONALITY ==========
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

    // Initialize dropdown
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

    // ========== INITIALIZATION ==========
    // Setup all functionality
    setupSearch();
    setupSorting();
    setupDeleteHandlers();
    setupFilePreview();

    // Upload button
    document.getElementById("uploadBtn")?.addEventListener("click", function() {
        showModal("uploadModal");
    });

    // Upload form
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
                setTimeout(() => {
                    closeModal('uploadSuccessModal');
                    location.reload();
                }, 1000);
            } else {
                alert(data.message);
            }
        });
    });
});