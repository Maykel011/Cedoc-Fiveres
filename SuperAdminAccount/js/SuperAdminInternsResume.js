document.addEventListener("DOMContentLoaded", function() {
    // User dropdown and logout functionality
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const logoutCancel = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');
    
    // New modal elements for saving changes
    const savingModal = document.getElementById("savingModal");
    const savingMessage = document.getElementById("savingMessage");
    const successModal = document.getElementById("successModal");
    const successMessage = document.getElementById("successMessage");

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
        if (savingModal) {
            savingModal.style.display = 'none';
        }
        if (successModal) {
            successModal.style.display = 'none';
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

    // =================================================================
    // Application Management Functionality
    // =================================================================

    // Modal elements
    const notesModal = document.getElementById("notesModal");
    const questionsModal = document.getElementById("questionsModal");
    const deleteModal = document.getElementById("deleteModal");
    const editModal = document.getElementById("editModal");
    
    // Close buttons
    const closeButtons = document.getElementsByClassName("close");
    
    // Action buttons
    const viewNotesBtns = document.getElementsByClassName("view-notes-btn");
    const viewQuestionsBtns = document.getElementsByClassName("view-questions-btn");
    const viewDocumentsBtns = document.getElementsByClassName("view-documents-btn");
    const deleteBtns = document.getElementsByClassName("delete-btn");
    const editBtns = document.getElementsByClassName("edit-btn");
    
    // Current applicant ID for actions
    let currentApplicantId = null;

    // Close modals when clicking X
    Array.from(closeButtons).forEach(button => {
        button.addEventListener("click", function() {
            notesModal.style.display = "none";
            questionsModal.style.display = "none";
            deleteModal.style.display = "none";
            editModal.style.display = "none";
        });
    });

    // Close modals when clicking outside
    window.addEventListener("click", function(event) {
        if (event.target === notesModal) notesModal.style.display = "none";
        if (event.target === questionsModal) questionsModal.style.display = "none";
        if (event.target === deleteModal) deleteModal.style.display = "none";
        if (event.target === editModal) editModal.style.display = "none";
    });

    // View Notes button functionality
    Array.from(viewNotesBtns).forEach(button => {
        button.addEventListener("click", function() {
            currentApplicantId = this.getAttribute("data-id");
            const notes = this.getAttribute("data-notes");
            
            // Set notes content
            document.getElementById("notesText").textContent = notes || "No notes available.";
            document.getElementById("newNotes").value = notes || "";
            
            // Set current status in select
            const statusSelect = document.getElementById("statusSelect");
            const row = this.closest("tr");
            const status = row.querySelector("td:nth-child(8)").textContent;
            statusSelect.value = status;
            
            // Show modal
            notesModal.style.display = "block";
        });
    });

    // Save Notes changes with new modal feedback
    document.getElementById("saveNotes").addEventListener("click", function() {
        const newNotes = document.getElementById("newNotes").value;
        const newStatus = document.getElementById("statusSelect").value;
        
        // Show saving modal
        savingModal.style.display = "block";
        savingMessage.textContent = "Saving changes...";
        
        fetch('../AdminBackEnd/InternResumeBE.php?action=updateStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${currentApplicantId}&status=${encodeURIComponent(newStatus)}&notes=${encodeURIComponent(newNotes)}`
        })
        .then(response => response.json())
        .then(data => {
            // Hide saving modal
            savingModal.style.display = "none";
            
            if (data.success) {
                // Show success modal
                successModal.style.display = "block";
                successMessage.textContent = "Changes saved successfully!";
                
                // Close success modal after 2 seconds and reload
                setTimeout(() => {
                    successModal.style.display = "none";
                    location.reload();
                }, 500);
            } else {
                alert("Error saving changes: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            savingModal.style.display = "none";
            alert("An error occurred while saving changes.");
        });
    });

    // View Questions button functionality
    Array.from(viewQuestionsBtns).forEach(button => {
        button.addEventListener("click", function() {
            // Set question answers
            document.getElementById("q1Answer").textContent = this.getAttribute("data-q1");
            document.getElementById("q2Answer").textContent = this.getAttribute("data-q2");
            document.getElementById("q3Answer").textContent = this.getAttribute("data-q3");
            document.getElementById("q4Answer").textContent = this.getAttribute("data-q4");
            document.getElementById("q5Answer").textContent = this.getAttribute("data-q5");
            
            // Show modal
            questionsModal.style.display = "block";
        });
    });

    // View Documents button functionality - Modified to use the enhanced preview
    Array.from(viewDocumentsBtns).forEach(button => {
        button.addEventListener("click", function() {
            currentApplicantId = this.getAttribute("data-id");
            
            // Fetch applicant data via AJAX
            fetch(`../AdminBackEnd/InternResumeBE.php?action=getApplicantData&id=${currentApplicantId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Create a documents modal with tabs for each document type
                        createDocumentsModal(data.applicant);
                    } else {
                        alert("Error loading applicant data: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred while loading applicant data.");
                });
        });
    });

    // Delete button functionality
    Array.from(deleteBtns).forEach(button => {
        button.addEventListener("click", function() {
            currentApplicantId = this.getAttribute("data-id");
            deleteModal.style.display = "block";
        });
    });

    // Confirm Delete
    document.getElementById("confirmDelete").addEventListener("click", function() {
        fetch('../AdminBackEnd/InternResumeBE.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${currentApplicantId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Applicant deleted successfully!");
                location.reload();
            } else {
                alert("Error deleting applicant: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while deleting the applicant.");
        });
    });

    // Cancel Delete
    document.getElementById("cancelDelete").addEventListener("click", function() {
        deleteModal.style.display = "none";
    });

    // Edit button functionality
    Array.from(editBtns).forEach(button => {
        button.addEventListener("click", function() {
            currentApplicantId = this.getAttribute("data-id");
            const row = this.closest("tr");
            const status = row.querySelector("td:nth-child(8)").textContent;
            const notes = row.querySelector("td:nth-child(9) button").getAttribute("data-notes") || "";
            
            // Set form values
            document.getElementById("editStatusSelect").value = status;
            document.getElementById("editNotes").value = notes;
            
            // Show modal
            editModal.style.display = "block";
        });
    });

    // Save Edit changes with new modal feedback
    document.getElementById("saveEdit").addEventListener("click", function() {
        const newStatus = document.getElementById("editStatusSelect").value;
        const newNotes = document.getElementById("editNotes").value;
        
        // Show saving modal
        savingModal.style.display = "block";
        savingMessage.textContent = "Saving changes...";
        
        fetch('../AdminBackEnd/InternResumeBE.php?action=updateStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${currentApplicantId}&status=${encodeURIComponent(newStatus)}&notes=${encodeURIComponent(newNotes)}`
        })
        .then(response => response.json())
        .then(data => {
            // Hide saving modal
            savingModal.style.display = "none";
            
            if (data.success) {
                // Show success modal
                successModal.style.display = "block";
                successMessage.textContent = "Changes saved successfully!";
                
                // Close success modal after 2 seconds and reload
                setTimeout(() => {
                    successModal.style.display = "none";
                    location.reload();
                }, 500);
            } else {
                alert("Error saving changes: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            savingModal.style.display = "none";
            alert("An error occurred while saving changes.");
        });
    });

    // Enhanced Documents Modal Creation
    function createDocumentsModal(applicant) {
        const modal = document.createElement('div');
        modal.className = 'documents-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #222;
            color: white;
        `;

        const title = document.createElement('h2');
        title.textContent = 'Applicant Documents';
        title.style.cssText = 'margin: 0;';

        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            font-size: 28px;
            cursor: pointer;
        `;
        closeBtn.addEventListener('click', () => document.body.removeChild(modal));

        header.appendChild(title);
        header.appendChild(closeBtn);

        const content = document.createElement('div');
        content.style.cssText = `
            display: flex;
            flex: 1;
            overflow: hidden;
        `;

        // Create sidebar for document navigation
        const sidebar = document.createElement('div');
        sidebar.style.cssText = `
            width: 250px;
            background-color: #333;
            padding: 15px;
            overflow-y: auto;
            border-left: 1px solid #555;
        `;

        // Create main content area - now full width
        const mainContent = document.createElement('div');
        mainContent.style.cssText = `
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        `;

        // Document list items
        const documents = [
            { 
                name: 'Resume', 
                path: applicant.resume_path,
                type: getFileType(applicant.resume_path)
            },
            { 
                name: 'MOA', 
                path: applicant.moa_path || null,
                type: applicant.moa_path ? getFileType(applicant.moa_path) : null
            },
            { 
                name: 'Recommendation Letter', 
                path: applicant.recom_path || null,
                type: applicant.recom_path ? getFileType(applicant.recom_path) : null,
                downloadBtn: true
            }
        ];

        // Create document list in sidebar
        documents.forEach(doc => {
            if (!doc.path) return;

            const docContainer = document.createElement('div');
            docContainer.style.cssText = `
                margin-bottom: 15px;
            `;

            const docItem = document.createElement('div');
            docItem.className = 'document-item';
            docItem.textContent = doc.name;
            docItem.style.cssText = `
                padding: 10px;
                margin-bottom: 5px;
                background-color: #444;
                color: white;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.2s;
            `;
            docItem.addEventListener('click', () => {
                // Highlight selected document
                document.querySelectorAll('.document-item').forEach(item => {
                    item.style.backgroundColor = '#444';
                });
                docItem.style.backgroundColor = '#666';
                
                // Show the document in the main content area
                showDocument(doc.path, doc.type, mainContent);
            });

            docContainer.appendChild(docItem);

            // Add download button specifically for Recommendation Letter
            if (doc.downloadBtn) {
                const downloadBtn = document.createElement('a');
                downloadBtn.href = '../../../' + doc.path;
                downloadBtn.download = doc.name;
                downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download';
                downloadBtn.style.cssText = `
                    padding: 8px 15px;
                    background-color:rgb(17, 118, 144);
                    color: white;
                    border-radius: 4px;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    width: 88%;
                    justify-content: center;
                    margin-top: 5px;
                    transition: background-color 0.2s;
                `;
                
                downloadBtn.addEventListener('mouseenter', () => {
                    downloadBtn.style.backgroundColor = '#3e8e41';
                });
                downloadBtn.addEventListener('mouseleave', () => {
                    downloadBtn.style.backgroundColor = '#4CAF50';
                });
                docContainer.appendChild(downloadBtn);
            }

            sidebar.appendChild(docContainer);
        });

        // Initial document display
        if (applicant.resume_path) {
            showDocument(applicant.resume_path, getFileType(applicant.resume_path), mainContent);
            // Highlight the first item
            if (sidebar.firstChild && sidebar.firstChild.firstChild) {
                sidebar.firstChild.firstChild.style.backgroundColor = '#666';
            }
        } else {
            mainContent.innerHTML = '<p style="color: white;">No documents available for this applicant.</p>';
        }

        content.appendChild(mainContent);
        content.appendChild(sidebar);
        
        modal.appendChild(header);
        modal.appendChild(content);
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        // Close modal when clicking outside or pressing ESC
        const keyHandler = (e) => e.key === 'Escape' && document.body.removeChild(modal);
        document.addEventListener('keydown', keyHandler);
        modal.addEventListener('click', (e) => e.target === modal && document.body.removeChild(modal));
    }

    // Modified showDocument function
    function showDocument(filePath, fileType, container) {
        container.innerHTML = ''; // Clear previous content
        
        const fullPath = '../../../' + filePath;
        
        // SIMPLE PDF VIEWER
        if (fileType === 'pdf') {
            // Create a simple iframe to display the PDF directly
            const pdfViewer = document.createElement('iframe');
            pdfViewer.src = fullPath;
            pdfViewer.style.cssText = `
                width: 100%;
                height: 80vh;
                border: none;
                margin-top: 10px;
                background-color: white;
            `;
            pdfViewer.setAttribute('type', 'application/pdf');
            
            // Fallback message for browsers that can't display PDFs
            const fallback = document.createElement('div');
            fallback.innerHTML = `
                <p>Your browser doesn't support PDF preview. 
                   <a href="${fullPath}" target="_blank">Click here to download the PDF</a>.
                </p>
            `;
            fallback.style.cssText = `
                display: none;
                padding: 20px;
                background: #f8f8f8;
                border-radius: 5px;
                margin-top: 10px;
                color: #333;
            `;
            
            // Check if PDF is loaded successfully
            pdfViewer.onerror = function() {
                pdfViewer.style.display = 'none';
                fallback.style.display = 'block';
            };
            
            container.appendChild(pdfViewer);
            container.appendChild(fallback);
        } else {
            // For Office files, show the simplified message
            const officeMessage = document.createElement('div');
            officeMessage.style.cssText = `
                text-align: center;
                color: white;
                padding: 20px;
                max-width: 600px;
            `;
            officeMessage.innerHTML = `
                <h3>Office File Preview Not Available Locally</h3>
                <p>For security reasons, Office files cannot be previewed directly when running on localhost.</p>
                <p>Please download the file to view it, or deploy the application to your hosting server for full preview functionality.</p>
            `;
            container.appendChild(officeMessage);
        }
    }

    function getFileType(filename) {
        if (!filename) return '';
        const extension = filename.split('.').pop().toLowerCase();
        if (extension === 'pdf') return 'pdf';
        if (['doc', 'docx'].includes(extension)) return 'word';
        if (['xls', 'xlsx'].includes(extension)) return 'excel';
        if (['ppt', 'pptx'].includes(extension)) return 'powerpoint';
        return '';
    }
});