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

// 6 digit PIN code
document.addEventListener("DOMContentLoaded", function () {
    const pinForm = document.querySelector(".delete-passkey-container form");

    pinForm.addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent page reload

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
        pinDisplay.style.color = "#28a745"; // Green color

        // Append below the form
        const formContainer = document.querySelector(".delete-passkey-container");
        formContainer.appendChild(pinDisplay);

        // Clear the input fields
        pinForm.reset();
    });
});
