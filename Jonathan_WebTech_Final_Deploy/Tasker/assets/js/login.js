function validateLogin() {
    let isValid = true;
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    // Reset error messages
    emailError.classList.add('hidden');
    passwordError.classList.add('hidden');

    // Email validation
    if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        emailError.textContent = 'Please enter a valid email address';
        emailError.classList.remove('hidden');
        isValid = false;
    }

    // Password validation
    if (password.value.length < 6) {
        passwordError.textContent = 'Password must be at least 6 characters';
        passwordError.classList.remove('hidden');
        isValid = false;
    }

    return isValid;
}