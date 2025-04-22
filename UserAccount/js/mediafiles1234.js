document.addEventListener("DOMContentLoaded", function() {
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const logoutCancel = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');

        // Close all modals/dropdowns
        function closeAll() {
            if (logoutModal) {
                logoutModal.style.display = 'none';
                document.body.style.overflow = '';
            }
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
});

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

    // Handle Search Filter
    const searchInput = document.querySelector(".search-input");
    const filterSelect = document.querySelector(".filter-select");
    const tableBody = document.querySelector("tbody");

    if (!tableBody) {
        console.error("Error: Table body element not found.");
        return;
    }

    // Search Functionality
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const searchValue = this.value.toLowerCase();
            document.querySelectorAll("tbody tr").forEach(row => {
                const folderName = row.children[0].textContent.toLowerCase();
                row.style.display = folderName.includes(searchValue) ? "" : "none";
            });
        });
    }

    // Sorting Functionality
    if (filterSelect) {
        filterSelect.addEventListener("change", function () {
            const selectedFilter = this.value;
            let rows = Array.from(tableBody.querySelectorAll("tr"));

            if (selectedFilter === "name") {
                rows.sort((a, b) => {
                    const nameA = a.children[0].textContent.trim().toLowerCase();
                    const nameB = b.children[0].textContent.trim().toLowerCase();
                    return nameA.localeCompare(nameB);
                });
            } else if (selectedFilter === "date") {
                rows.sort((a, b) => {
                    const dateA = parseDate(a.children[1].textContent);
                    const dateB = parseDate(b.children[1].textContent);
                    return dateB - dateA;
                });
            }

            tableBody.innerHTML = "";
            rows.forEach(row => tableBody.appendChild(row));
        });
    }

    function parseDate(dateString) {
        const date = new Date(dateString);
        return isNaN(date) ? new Date(0) : date;
    }

    // Handle Create Folder
    const createFolderBtn = document.querySelector(".create-folder-btn");

    if (createFolderBtn) {
        createFolderBtn.addEventListener("click", function () {
            const folderInput = document.querySelector(".folder-name-input");
            const folderName = folderInput.value.trim();

            if (folderName === "") {
                showModal("errorModal", "Please enter a folder name.");
                return;
            }

            fetch("../userBackEnd/MediaFilesBE.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=create&folder_name=${encodeURIComponent(folderName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    setTimeout(() => location.reload(), 300);
                } else {
                    showModal("errorModal", data.message);
                }
            });
        });
    }

    // Modal Manager with Admin PIN Verification
    const ModalManager = {
        currentFolderId: null,

        openModal: function (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = "flex";
            }
        },

        closeModal: function (modalId) {
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
                errorMsg.style.display = "block";
                return;
            }

            errorMsg.style.display = "none";

            fetch("../userBackEnd/MediaFilesBE.php", {
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
                });
        },

        deleteFolder: function () {
            const pinCode = document.getElementById("deletePinCode").value.trim();
            const errorElement = document.getElementById("deletePinError");
            
            if (!pinCode || pinCode.length !== 6) {
                errorElement.textContent = "Please enter a valid 6-digit PIN code";
                errorElement.style.display = "block";
                return;
            }
            
            errorElement.style.display = "none";
            
            fetch("../userBackEnd/MediaFilesBE.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=delete&folder_id=${this.currentFolderId}&pin_code=${encodeURIComponent(pinCode)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        this.closeModal("deleteModal");
                        this.showSuccessModal("deleteSuccessModal");
                        document.getElementById("deletePinCode").value = "";
                    } else {
                        errorElement.textContent = data.message;
                        errorElement.style.display = "block";
                    }
                })
                .catch(error => {
                    errorElement.textContent = "An error occurred. Please try again.";
                    errorElement.style.display = "block";
                });
        },

        clearErrorMessages: function (modalId) {
            if (modalId === "renameModal") {
                const errorMsg = document.getElementById("renameError");
                errorMsg.style.display = "none";
                errorMsg.textContent = "";
            } else if (modalId === "deleteModal") {
                const errorMsg = document.getElementById("deletePinError");
                errorMsg.style.display = "none";
                errorMsg.textContent = "";
                document.getElementById("deletePinCode").value = "";
            }
        },

        attachEventListeners: function () {
            const renameFolderBtn = document.getElementById("renameFolderBtn");
            if (renameFolderBtn) {
                renameFolderBtn.addEventListener("click", () => this.renameFolder());
            }

            const deleteFolderBtn = document.getElementById("deleteFolderBtn");
            if (deleteFolderBtn) {
                deleteFolderBtn.addEventListener("click", () => this.deleteFolder());
            }

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

            document.querySelectorAll(".close").forEach(button => {
                button.addEventListener("click", () => this.closeModal(button.parentElement.parentElement.id));
            });

            document.querySelectorAll(".custom-modal button").forEach(button => {
                if (button.textContent.trim() === "Cancel") {
                    button.addEventListener("click", () => this.closeModal(button.closest(".custom-modal").id));
                }
            });
        }
    };

    ModalManager.attachEventListeners();
});

function showModal(modalId, message) {
    const modal = document.getElementById(modalId);
    const modalMessage = modal?.querySelector(".modal-message");

    if (modalMessage) {
        modalMessage.textContent = message;
    }

    if (modal) {
        modal.style.display = "flex";
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = "none";
    }
}