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
document.getElementById("uploadForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    fetch("", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === "success") {
            location.reload();
        }
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

// Delete folder function
function deleteFolder(folderName, element) {
    fetch(window.location.href, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=deleteFolder&folder_name=${folderName}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            closeModal("deleteModal");
            showSuccessModal("Folder deleted successfully");
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
