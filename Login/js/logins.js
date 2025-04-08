            document.getElementById("login-form").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent actual form submission

            let loginBtn = document.getElementById("login-buton");
            let loading = document.getElementById("loading");
            let welcomeMessage = document.getElementById("welcomeMessage");

            // Disable button & show loading animation
            loginBtn.disabled = true;
            loading.style.display = "block";

            // Simulate login process (loading for 3 seconds)
            setTimeout(() => {
                loading.style.display = "none"; // Hide loading
                welcomeMessage.style.display = "block"; // Show welcome message

                // Redirect to homepage after 2 seconds
                setTimeout(() => {
                    window.location.href = "userDashboard.php"; // Change this to your actual homepage
                }, 2000);
            }, 3000);
        });