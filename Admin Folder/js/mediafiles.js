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


//modal rename & Deleting
document.addEventListener("DOMContentLoaded", function () {
    const ModalManager = {
        currentFolderId: null,

        openModal: function (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = "flex";
            }
        },

        closeModal: function (modalId) {
            console.log("Closing modal:", modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = "none";
                this.clearErrorMessages(modalId);
            }
        },

        openRenameModal: function (folderId, folderName) {
            this.currentFolderId = folderId;
            document.getElementById("renameFolderName").textContent = `Folder: ${folderName}`;
            this.openModal("renameModal");
        },

        openDeleteModal: function (folderId, folderName) {
            this.currentFolderId = folderId;
            document.getElementById("deleteFolderName").textContent = `Folder: ${folderName}`;
            this.openModal("deleteModal");
        },

        showSuccessModal: function (modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.style.display = "flex";
            setTimeout(() => {
                modal.style.display = "none";
                location.reload();
            }, 1000);
        },

        renameFolder: function () {
            const newName = document.getElementById("newFolderName").value.trim();
            const errorMsg = document.getElementById("renameError");

            if (!newName) {
                errorMsg.textContent = "Please enter a new folder name.";
                errorMsg.style.display = "block"; // Show the error message
                return;
            }

            errorMsg.style.display = "none"; // Hide error if input is valid

            fetch("../AdminBackEnd/MediaFilesBE.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=rename&folder_id=${this.currentFolderId}&new_name=${encodeURIComponent(newName)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        this.closeModal("renameModal");
                        this.showSuccessModal("renameSuccessModal");
                    } else {
                        errorMsg.textContent = data.message; // Show server error message
                        errorMsg.style.display = "block";
                    }
                });
        },

        deleteFolder: function () {
            fetch("../AdminBackEnd/MediaFilesBE.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=delete&folder_id=${this.currentFolderId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        this.closeModal("deleteModal");
                        this.showSuccessModal("deleteSuccessModal");
                    } else {
                        alert(data.message);
                    }
                });
        },

        clearErrorMessages: function (modalId) {
            if (modalId === "renameModal") {
                const errorMsg = document.getElementById("renameError");
                errorMsg.style.display = "none"; // Hide error when modal is closed
                errorMsg.textContent = "";
            }
        },

        attachEventListeners: function () {
            // Rename Button Event
            const renameFolderBtn = document.getElementById("renameFolderBtn");
            if (renameFolderBtn) {
                renameFolderBtn.addEventListener("click", () => this.renameFolder());
            }

            // Delete Button Event
            const deleteFolderBtn = document.getElementById("deleteFolderBtn");
            if (deleteFolderBtn) {
                deleteFolderBtn.addEventListener("click", () => this.deleteFolder());
            }

            // Attach modal openers
            document.querySelectorAll(".rename-btn").forEach(button => {
                button.addEventListener("click", () => {
                    const folderId = button.getAttribute("data-id");
                    const folderName = button.closest("tr").children[0].textContent.replace("📁", "").trim();
                    this.openRenameModal(folderId, folderName);
                });
            });

            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", () => {
                    const folderId = button.getAttribute("data-id");
                    const folderName = button.closest("tr").children[0].textContent.replace("📁", "").trim();
                    this.openDeleteModal(folderId, folderName);
                });
            });

            // Global close modal functionality
            document.querySelectorAll(".close").forEach(button => {
                button.addEventListener("click", () => this.closeModal(button.parentElement.parentElement.id));
            });

            // Attach Cancel Button Functionality
            document.querySelectorAll(".custom-modal button").forEach(button => {
                if (button.textContent.trim() === "Cancel") {
                    button.addEventListener("click", () => this.closeModal(button.closest(".custom-modal").id));
                }
            });
        }
    };

    ModalManager.attachEventListeners();
});

// Pagination

