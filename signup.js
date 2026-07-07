const signupForm = document.getElementById('signup-form');
const verificationUi = document.getElementById('verification-ui');
const passwordInput = document.getElementById('password');
const displayEmail = document.getElementById('display-email');

// Password Validation Rules
const rules = {
    length: document.getElementById('rule-length'),
    upper: document.getElementById('rule-upper'),
    number: document.getElementById('rule-number'),
    special: document.getElementById('rule-special')
};

if (passwordInput) {
    passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        
        // Length check
        updateRule(rules.length, val.length >= 8);
        // Uppercase check
        updateRule(rules.upper, /[A-Z]/.test(val));
        // Number check
        updateRule(rules.number, /[0-9]/.test(val));
        // Special char check
        updateRule(rules.special, /[!@#$%^&*(),.?":{}|<>]/.test(val));
    });
}

function updateRule(element, isValid) {
    if (!element) return;
    if (isValid) {
        element.classList.remove('invalid');
        element.classList.add('valid');
        element.innerText = '● ' + element.innerText.substring(2);
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
        element.innerText = '○ ' + element.innerText.substring(2);
    }
}

if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Basic validation check before "submitting"
        const password = passwordInput ? passwordInput.value : '';
        const isPasswordValid = 
            password.length >= 8 && 
            /[A-Z]/.test(password) && 
            /[0-9]/.test(password) && 
            /[!@#$%^&*(),.?":{}|<>]/.test(password);

        if (!isPasswordValid) {
            alert('Please ensure your password meets all security requirements.');
            return;
        }

        // Create FormData from the form
        const formData = new FormData(signupForm);

        try {
            // Send to PHP backend
            const response = await fetch('auth/signup.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show verification message
                const email = document.getElementById('email') ? document.getElementById('email').value : '';
                if (displayEmail) displayEmail.innerText = email;

                // Transition to verification UI
                signupForm.classList.add('hidden');
                if (verificationUi) verificationUi.classList.add('active');

                // Redirect after delay
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 5000);
            } else {
                // Show errors
                const errorMsg = Array.isArray(data.errors) ? data.errors.join('\n') : (data.message || 'Signup failed');
                alert('Signup failed:\n' + errorMsg);
            }
        } catch (error) {
            alert('An error occurred. Please try again. Error: ' + error.message);
            console.error('Signup error:', error);
        }
    });
}
