// Handle Profile Dropdown
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

        renameFolder: function() {
            const newName = document.getElementById("newFolderName").value.trim();
            const errorMsg = document.getElementById("renameError");
        
            if (!newName) {
                errorMsg.textContent = "Please enter a new folder name.";
                errorMsg.style.display = "block";
                return;
            }
        
            errorMsg.style.display = "none";
        
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
                    errorMsg.textContent = data.message;
                    errorMsg.style.display = "block";
                }
            })
            .catch(error => {
                errorMsg.textContent = "An error occurred while renaming the folder.";
                errorMsg.style.display = "block";
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

