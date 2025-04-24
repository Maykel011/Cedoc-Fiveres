document.addEventListener("DOMContentLoaded", function() {
    // First declare all variables at the top
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const logoutCancel = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');
    const statusModal = document.getElementById('statusModal');
    const modalCloseButton = document.getElementById('modalCloseButton');

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
        if (statusModal) {
            statusModal.classList.remove('active');
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

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        // User dropdown
        if (userDropdown && userDropdown.classList.contains("show")) {
            if (!userContainer.contains(e.target) && !userDropdown.contains(e.target)) {
                toggleDropdown(false);
            }
        }
        
        // Logout modal
        if (logoutModal && e.target === logoutModal) {
            closeAll();
        }
        
        // Status modal
        if (statusModal && e.target === statusModal) {
            closeAll();
        }
    });

    // Close with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAll();
        }
    });

    // Close status modal button
    if (modalCloseButton) {
        modalCloseButton.addEventListener('click', closeAll);
    }
});

// 6 digit PIN code (if still needed)
document.addEventListener("DOMContentLoaded", function () {
    const pinForm = document.querySelector(".delete-passkey-container form");

    if (pinForm) {
        pinForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const newPin = document.getElementById("new-passkey").value;
            const confirmNewPin = document.getElementById("confirm-new-passkey").value;

            // Validate 6-digit PIN
            if (newPin.length !== 6 || confirmNewPin.length !== 6) {
                alert("PIN code must be exactly 6 digits!");
                return;
            }

            if (newPin !== confirmNewPin) {
                alert("New PIN and Confirm PIN do not match!");
                return;
            }

            // Display the masked PIN (e.g., "******")
            const pinDisplay = document.createElement("p");
            pinDisplay.textContent = "New PIN Code: ******";
            pinDisplay.style.fontWeight = "bold";
            pinDisplay.style.color = "#28a745";

            // Append below the form
            const formContainer = document.querySelector(".delete-passkey-container");
            formContainer.appendChild(pinDisplay);

            // Clear the input fields
            pinForm.reset();
        });
    }
});