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

        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('.login-form');
            
            if(loginForm) {
                loginForm.addEventListener('submit', function(event) {
                    const loginBtn = this.querySelector('.login-button');
                    const employeeNo = this.querySelector('input[name="employee_no"]').value;
                    const password = this.querySelector('input[name="password"]').value;
                    
                    // Simple client-side validation
                    if(!employeeNo || !password) {
                        event.preventDefault();
                        return;
                    }
                    
                    // Disable button during submission
                    loginBtn.disabled = true;
                    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                });
            }
            
            // Close modal when clicking outside
            const modal = document.getElementById('lockoutModal');
            if(modal) {
                modal.addEventListener('click', function(e) {
                    if(e.target === modal) {
                        closeModal();
                    }
                });
            }
        });
        
        function closeModal() {
            const modal = document.getElementById('lockoutModal');
            if(modal) {
                modal.style.display = 'none';
            }
        }   