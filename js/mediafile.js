// Profile Dropdown
document.addEventListener("DOMContentLoaded", function () {
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");

    userContainer.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent dropdown from closing when clicking inside
        userDropdown.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!userContainer.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove("show");
        }
    });
});


//add
document.querySelector(".search-input").addEventListener("input", function () {
    const searchValue = this.value.toLowerCase();
    document.querySelectorAll("tbody tr").forEach(row => {
        const folderName = row.children[0].textContent.toLowerCase();
        row.style.display = folderName.includes(searchValue) ? "" : "none";
    });
});
//
document.querySelector(".filter-select").addEventListener("change", function () {
    const selectedFilter = this.value.toLowerCase();
    document.querySelectorAll("tbody tr").forEach(row => {
        const folderName = row.children[0].textContent.toLowerCase();
        row.style.display = folderName.includes(selectedFilter) ? "" : "none";
    });
});




document.addEventListener("DOMContentLoaded", function () {
    const createBtn = document.querySelector(".create-folder-btn");
    const folderInput = document.querySelector(".folder-name-input");
    
    // Create Folder
    createBtn.addEventListener("click", function () {
        const folderName = folderInput.value.trim();

        if (folderName === "") {
            alert("Please enter a folder name.");
            return;
        }

        fetch("../AdminBackEnd/MediaFilesBE.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=create&folder_name=${encodeURIComponent(folderName)}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") location.reload();
        });
    });

    // Rename Folder
    document.querySelectorAll(".rename-btn").forEach(button => {
        button.addEventListener("click", function () {
            const folderId = this.getAttribute("data-id");
            const newName = prompt("Enter new folder name:");

            if (!newName) return;

            fetch("../AdminBackEnd/MediaFilesBE.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=rename&folder_id=${folderId}&new_name=${encodeURIComponent(newName)}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") location.reload();
            });
        });
    });

    // Delete Folder
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function () {
            const folderId = this.getAttribute("data-id");
            const confirmDelete = confirm("Are you sure you want to delete this folder?");

            if (!confirmDelete) return;

            fetch("../AdminBackEnd/MediaFilesBE.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=delete&folder_id=${folderId}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") location.reload();
            });
        });
    });
});
