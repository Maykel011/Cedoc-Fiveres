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
function clearFilters() {
    document.getElementById("filter-vehicle").value = "";
    document.getElementById("filter-case").value = "";
    document.getElementById("filter-start-date").value = "";
    document.getElementById("filter-end-date").value = "";
    filterTable(); // Apply the "All" filter
}

function openUploadModal() {
let uploadModal = new bootstrap.Modal(document.getElementById('uploadCaseModal'), {
backdrop: 'static',
keyboard: false
});
uploadModal.show();
}

function editCase(data) {
document.getElementById('edit-id').value = data.id;
document.getElementById('edit-vehicle_team').value = data.vehicle_team;
document.getElementById('edit-case_type').value = data.case_type;
document.getElementById('edit-transport_officer').value = data.transport_officer;
document.getElementById('edit-emergency_responders').value = data.emergency_responders;
document.getElementById('edit-location').value = data.location;

let previewImage = document.getElementById('edit-preview-image');
if (data.case_image) {
previewImage.src = data.case_image;
previewImage.style.display = "block";
} else {
previewImage.style.display = "none";
}

document.getElementById('edit-case_image').value = "";

let editModal = new bootstrap.Modal(document.getElementById('editModal'), {
backdrop: 'static',
keyboard: false
});
editModal.show();
}

document.addEventListener("DOMContentLoaded", function() {
let editModal = document.getElementById('editModal');
editModal.classList.remove("show");
editModal.style.display = "none";
document.body.classList.remove("modal-open");

let uploadCaseModal = document.getElementById('uploadCaseModal');
uploadCaseModal.classList.remove("show");
uploadCaseModal.style.display = "none";
document.body.classList.remove("modal-open");
});

function searchFiles() {
let input = document.getElementById("searchBar");
let filter = input.value.toLowerCase();
let table = document.querySelector("table");
let tr = table.getElementsByTagName("tr");

for (let i = 1; i < tr.length; i++) {
let td = tr[i].getElementsByTagName("td");
let found = false;

for (let j = 1; j < td.length - 1; j++) {
    if (td[j]) {
        let textValue = td[j].textContent || td[j].innerText;
        if (textValue.toLowerCase().indexOf(filter) > -1) {
            found = true;
            break;
        }
    }
}

if (found) {
    tr[i].style.display = "";
} else {
    tr[i].style.display = "none";
}
}
}

document.getElementById("searchBar").addEventListener("keyup", searchFiles);

function filterTable() {
let vehicleFilter = document.getElementById("filter-vehicle").value.toLowerCase();
let caseFilter = document.getElementById("filter-case").value.toLowerCase();
let startDate = document.getElementById("filter-start-date").value;
let endDate = document.getElementById("filter-end-date").value;

let table = document.querySelector("table tbody");
let rows = table.getElementsByTagName("tr");

for (let i = 0; i < rows.length; i++) {
let cells = rows[i].getElementsByTagName("td");
if (cells.length > 0) {
    let vehicle = cells[1].textContent.toLowerCase();
    let caseType = cells[2].textContent.toLowerCase();
    let dispatchDate = cells[6].textContent.split(" ")[0];

    let matchVehicle = vehicleFilter === "" || vehicle.includes(vehicleFilter);
    let matchCase = caseFilter === "" || caseType.includes(caseFilter);
    let matchDate = true;

    if (startDate && endDate) {
        matchDate = dispatchDate >= startDate && dispatchDate <= endDate;
    }

    if (matchVehicle && matchCase && matchDate) {
        rows[i].style.display = "";
    } else {
        rows[i].style.display = "none";
    }
}
}
}