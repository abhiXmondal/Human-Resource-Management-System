const loginForm = document.getElementById('login-form');

if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        const email = emailInput ? emailInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value.trim() : '';

        if (email === '' || password === '') {
            alert('Please fill in all fields.');
            return;
        }

        // Create FormData
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        try {
            // Send to PHP backend
            const response = await fetch('auth/login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Login Successful!');
                
                // Redirect based on role
                if (data.user && data.user.role === 'admin') {
                    window.location.href = 'admindashboard.html';
                } else {
                    window.location.href = 'employee_dashboard.html';
                }
            } else {
                const errorMsg = Array.isArray(data.errors) ? data.errors.join('\n') : (data.message || 'Login failed');
                alert('Login failed:\n' + errorMsg);
            }
        } catch (error) {
            alert('An error occurred. Please try again. Error: ' + error.message);
            console.error('Login error:', error);
        }
    });
}
