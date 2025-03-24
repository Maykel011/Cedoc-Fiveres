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
