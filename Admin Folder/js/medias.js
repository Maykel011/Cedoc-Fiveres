document.getElementById("login-form").addEventListener("submit", async function(event) {
    event.preventDefault();
    
    const loginBtn = document.getElementById("login-button");
    const loading = document.getElementById("loading");
    const welcomeMessage = document.getElementById("welcomeMessage");
    const errorMessage = document.getElementById("error-message");
    
    // Reset UI states
    errorMessage.style.display = "none";
    welcomeMessage.style.display = "none";
    loginBtn.disabled = true;
    loading.style.display = "block";
    
    try {
        // Get form data
        const formData = {
            employee_no: this.employee_no.value.trim(),
            password: this.password.value
        };
        
        // Send login request
        const response = await fetch('loginBE.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (!response.ok || data.error) {
            throw new Error(data.error || 'Login failed');
        }
        
        // Login success
        loading.style.display = "none";
        welcomeMessage.style.display = "block";
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = data.redirect;
        }, 1500);
        
    } catch (error) {
        console.error('Login error:', error);
        loading.style.display = "none";
        errorMessage.textContent = error.message || 'Login failed. Please try again.';
        errorMessage.style.display = "block";
        loginBtn.disabled = false;
    }
});