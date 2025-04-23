document.addEventListener("DOMContentLoaded", function() {
    // User dropdown and logout functionality
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

    // =================================================================
    // Application Management Functionality
    // =================================================================

    // Modal elements
    const notesModal = document.getElementById("notesModal");
    const questionsModal = document.getElementById("questionsModal");
    const documentsModal = document.getElementById("documentsModal");
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
            documentsModal.style.display = "none";
            deleteModal.style.display = "none";
            editModal.style.display = "none";
        });
    });

    // Close modals when clicking outside
    window.addEventListener("click", function(event) {
        if (event.target === notesModal) notesModal.style.display = "none";
        if (event.target === questionsModal) questionsModal.style.display = "none";
        if (event.target === documentsModal) documentsModal.style.display = "none";
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

    // Save Notes changes
    document.getElementById("saveNotes").addEventListener("click", function() {
        const newNotes = document.getElementById("newNotes").value;
        const newStatus = document.getElementById("statusSelect").value;
        
        fetch('InternResumeBE.php?action=updateStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${currentApplicantId}&status=${encodeURIComponent(newStatus)}&notes=${encodeURIComponent(newNotes)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Changes saved successfully!");
                location.reload();
            } else {
                alert("Error saving changes: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
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

    // View Documents button functionality
    Array.from(viewDocumentsBtns).forEach(button => {
        button.addEventListener("click", function() {
            currentApplicantId = this.getAttribute("data-id");
            const resumePath = this.getAttribute("data-resume");
            const moaPath = this.getAttribute("data-moa");
            const recomPath = this.getAttribute("data-recom");
            
            // Get viewer and download elements
            const resumeViewer = document.getElementById("resumeViewer");
            const moaViewer = document.getElementById("moaViewer");
            const recomViewer = document.getElementById("recomViewer");
            
            const resumeDownload = document.getElementById("resumeDownload");
            const moaDownload = document.getElementById("moaDownload");
            const recomDownload = document.getElementById("recomDownload");
            
            // Handle resume document
            if (resumePath) {
                const fullResumePath = '../../../InternDocuments/' + resumePath;
                openOfficePreview(fullResumePath, "Resume", getFileType(resumePath));
                resumeDownload.href = fullResumePath;
            } else {
                resumeDownload.href = '#';
                resumeDownload.style.display = 'none';
            }
            
            // Handle MOA document
            if (moaPath) {
                const fullMoaPath = '../../../InternDocuments/' + moaPath;
                openOfficePreview(fullMoaPath, "MOA", getFileType(moaPath));
                moaDownload.href = fullMoaPath;
            } else {
                moaDownload.href = '#';
                moaDownload.style.display = 'none';
            }
            
            // Handle Recommendation document
            if (recomPath) {
                const fullRecomPath = '../../../InternDocuments/' + recomPath;
                openOfficePreview(fullRecomPath, "Recommendation", getFileType(recomPath));
                recomDownload.href = fullRecomPath;
            } else {
                recomDownload.href = '#';
                recomDownload.style.display = 'none';
            }
            
            // Show modal
            documentsModal.style.display = "block";
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
        fetch('InternResumeBE.php?action=delete', {
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

    // Save Edit changes
    document.getElementById("saveEdit").addEventListener("click", function() {
        const newStatus = document.getElementById("editStatusSelect").value;
        const newNotes = document.getElementById("editNotes").value;
        
        fetch('InternResumeBE.php?action=updateStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${currentApplicantId}&status=${encodeURIComponent(newStatus)}&notes=${encodeURIComponent(newNotes)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Changes saved successfully!");
                location.reload();
            } else {
                alert("Error saving changes: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while saving changes.");
        });
    });

    // Enhanced Office Preview Functionality
    function openOfficePreview(fileUrl, fileName, fileType) {
        const isLocal = window.location.hostname === "localhost" || 
                       window.location.hostname === "127.0.0.1" ||
                       fileUrl.startsWith("file://");
        
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

        const content = document.createElement('div');
        content.style.cssText = `
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow: auto;
        `;

        if (isLocal) {
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
            `;
            content.appendChild(localMessage);
        } else {
            // Create a container for the preview options
            const previewContainer = document.createElement('div');
            previewContainer.style.cssText = `
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
            `;

            // Create a tab system for different preview options
            const tabContainer = document.createElement('div');
            tabContainer.style.cssText = `
                display: flex;
                background: #333;
                padding: 0 10px;
            `;

            const viewerTabs = [
                { name: 'Office Online', id: 'office-tab', active: true },
                { name: 'Google Viewer', id: 'google-tab', active: false },
                { name: 'PDF.js (for PDFs)', id: 'pdfjs-tab', active: false }
            ];

            const tabContentContainer = document.createElement('div');
            tabContentContainer.style.cssText = `
                flex: 1;
                position: relative;
            `;

            // Create tab buttons and content areas
            viewerTabs.forEach((tab, index) => {
                const tabButton = document.createElement('button');
                tabButton.textContent = tab.name;
                tabButton.id = tab.id;
                tabButton.style.cssText = `
                    padding: 8px 15px;
                    background: ${tab.active ? '#555' : 'transparent'};
                    color: white;
                    border: none;
                    cursor: pointer;
                    font-size: 14px;
                    border-radius: 4px 4px 0 0;
                    margin-right: 5px;
                `;
                
                const tabContent = document.createElement('div');
                tabContent.id = `${tab.id}-content`;
                tabContent.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    display: ${tab.active ? 'block' : 'none'};
                `;
                
                tabButton.addEventListener('click', () => {
                    // Update active tab
                    viewerTabs.forEach(t => t.active = false);
                    tab.active = true;
                    
                    // Update UI
                    document.querySelectorAll('#office-tab, #google-tab, #pdfjs-tab').forEach(btn => {
                        btn.style.background = 'transparent';
                    });
                    tabButton.style.background = '#555';
                    
                    document.querySelectorAll('#office-tab-content, #google-tab-content, #pdfjs-tab-content').forEach(content => {
                        content.style.display = 'none';
                    });
                    tabContent.style.display = 'block';
                });
                
                tabContainer.appendChild(tabButton);
                tabContentContainer.appendChild(tabContent);
                
                // Initialize the content based on tab type
                if (tab.id === 'office-tab') {
                    // Microsoft Office Online Viewer
                    const officeViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
                    const iframe = createIframe(officeViewerUrl);
                    tabContent.appendChild(iframe);
                } 
                else if (tab.id === 'google-tab') {
                    // Google Docs Viewer
                    if (fileType === 'pdf') {
                        const googleViewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(fileUrl)}&embedded=true`;
                        const iframe = createIframe(googleViewerUrl);
                        tabContent.appendChild(iframe);
                    } else {
                        const message = document.createElement('div');
                        message.style.cssText = `
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            height: 100%;
                            color: white;
                        `;
                        message.textContent = 'Google Viewer only supports PDF files for external URLs';
                        tabContent.appendChild(message);
                    }
                }
                else if (tab.id === 'pdfjs-tab' && fileType === 'pdf') {
                    // PDF.js implementation
                    const pdfjsContainer = document.createElement('div');
                    pdfjsContainer.style.cssText = `
                        width: 100%;
                        height: 100%;
                        display: flex;
                        flex-direction: column;
                    `;
                    
                    const pdfjsToolbar = document.createElement('div');
                    pdfjsToolbar.style.cssText = `
                        background: #333;
                        padding: 5px;
                        display: flex;
                        gap: 5px;
                    `;
                    
                    const prevPageBtn = document.createElement('button');
                    prevPageBtn.textContent = 'Previous';
                    prevPageBtn.style.cssText = `
                        padding: 3px 8px;
                        background: #4CAF50;
                        color: white;
                        border: none;
                        border-radius: 3px;
                        cursor: pointer;
                    `;
                    
                    const nextPageBtn = document.createElement('button');
                    nextPageBtn.textContent = 'Next';
                    nextPageBtn.style.cssText = `
                        padding: 3px 8px;
                        background: #4CAF50;
                        color: white;
                        border: none;
                        border-radius: 3px;
                        cursor: pointer;
                    `;
                    
                    const pageInfo = document.createElement('span');
                    pageInfo.style.cssText = `
                        color: white;
                        margin-left: 10px;
                        display: flex;
                        align-items: center;
                    `;
                    
                    const canvas = document.createElement('canvas');
                    canvas.style.cssText = `
                        margin: 10px auto;
                        box-shadow: 0 0 5px rgba(0,0,0,0.3);
                    `;
                    
                    pdfjsToolbar.appendChild(prevPageBtn);
                    pdfjsToolbar.appendChild(nextPageBtn);
                    pdfjsToolbar.appendChild(pageInfo);
                    
                    pdfjsContainer.appendChild(pdfjsToolbar);
                    pdfjsContainer.appendChild(canvas);
                    tabContent.appendChild(pdfjsContainer);
                    
                    // Initialize PDF.js
                    let pdfDoc = null,
                        pageNum = 1,
                        pageRendering = false,
                        pageNumPending = null,
                        scale = 1.5;
                    
                    // Load PDF.js library dynamically
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js';
                    script.onload = function() {
                        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.worker.min.js';
                        
                        // Load the PDF
                        pdfjsLib.getDocument(fileUrl).promise.then(function(pdfDoc_) {
                            pdfDoc = pdfDoc_;
                            pageInfo.textContent = `Page ${pageNum} of ${pdfDoc.numPages}`;
                            
                            // Initial/first page rendering
                            renderPage(pageNum);
                            
                            // Previous page button
                            prevPageBtn.addEventListener('click', function() {
                                if (pageNum <= 1) return;
                                pageNum--;
                                renderPage(pageNum);
                            });
                            
                            // Next page button
                            nextPageBtn.addEventListener('click', function() {
                                if (pageNum >= pdfDoc.numPages) return;
                                pageNum++;
                                renderPage(pageNum);
                            });
                        }).catch(function(error) {
                            console.error('PDF.js error:', error);
                            canvas.parentNode.innerHTML = `
                                <div style="color: white; text-align: center; padding: 20px;">
                                    Could not load PDF. Please download the file to view it.
                                </div>
                            `;
                        });
                    };
                    document.head.appendChild(script);
                    
                    function renderPage(num) {
                        pageRendering = true;
                        pdfDoc.getPage(num).then(function(page) {
                            const viewport = page.getViewport({ scale: scale });
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            
                            const renderContext = {
                                canvasContext: canvas.getContext('2d'),
                                viewport: viewport
                            };
                            
                            const renderTask = page.render(renderContext);
                            
                            renderTask.promise.then(function() {
                                pageRendering = false;
                                if (pageNumPending !== null) {
                                    renderPage(pageNumPending);
                                    pageNumPending = null;
                                }
                            });
                        });
                        
                        pageInfo.textContent = `Page ${num} of ${pdfDoc.numPages}`;
                    }
                }
                else {
                    const message = document.createElement('div');
                    message.style.cssText = `
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100%;
                        color: white;
                    `;
                    message.textContent = 'This viewer is only available for PDF files';
                    tabContent.appendChild(message);
                }
            });
            
            previewContainer.appendChild(tabContainer);
            previewContainer.appendChild(tabContentContainer);
            content.appendChild(previewContainer);
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

    function createIframe(src) {
        const iframe = document.createElement('iframe');
        iframe.src = src;
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
            iframe.src = src + '&t=' + Date.now();
            fallback.style.display = 'none';
            iframe.style.display = 'block';
        });

        const container = document.createElement('div');
        container.style.cssText = `
            width: 100%;
            height: 100%;
            position: relative;
        `;
        container.appendChild(iframe);
        container.appendChild(fallback);
        
        return container;
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

    // Handle document viewer errors
    const iframes = document.querySelectorAll('.documents-content iframe');
    iframes.forEach(iframe => {
        iframe.addEventListener('error', function() {
            this.contentDocument.body.innerHTML = '<div style="padding:20px;color:red;">Could not load document preview. Please download the file to view it.</div>';
        });
    });
});