function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    const errors = [];
    
    if (password.length < minLength) {
        errors.push(`Password must be at least ${minLength} characters long`);
    }
    if (!hasUpperCase) {
        errors.push("Must contain at least one uppercase letter");
    }
    if (!hasLowerCase) {
        errors.push("Must contain at least one lowercase letter");
    }
    if (!hasNumbers) {
        errors.push("Must contain at least one number");
    }
    if (!hasSpecialChar) {
        errors.push("Must contain at least one special character");
    }
    
    return errors;
}

function validateRegister() {
    let isValid = true;
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');

    // Reset error messages
    const errors = document.querySelectorAll('span[id$="Error"]');
    errors.forEach(error => error.classList.add('hidden'));

    // Username validation
    if (username.value.length < 3) {
        document.getElementById('usernameError').textContent = 'Username must be at least 3 characters';
        document.getElementById('usernameError').classList.remove('hidden');
        isValid = false;
    }

    // Email validation
    if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        document.getElementById('emailError').textContent = 'Please enter a valid email address';
        document.getElementById('emailError').classList.remove('hidden');
        isValid = false;
    }

    // Password validation
    const passwordErrors = validatePassword(password.value);
    if (passwordErrors.length > 0) {
        document.getElementById('passwordError').innerHTML = passwordErrors.join('<br>');
        document.getElementById('passwordError').classList.remove('hidden');
        isValid = false;
    }

    // Password match validation
    if (password.value !== confirmPassword.value) {
        document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
        document.getElementById('confirmPasswordError').classList.remove('hidden');
        isValid = false;
    }

    return isValid;
}