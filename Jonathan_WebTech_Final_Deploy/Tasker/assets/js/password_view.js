document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"]');

    passwordFields.forEach(passwordField => {
        // Find the label associated with this password field
        const label = passwordField.closest('div').querySelector('label');
        
        if (label) {
            // Create toggle button
            const toggleButton = document.createElement('button');
            toggleButton.type = 'button';
            toggleButton.textContent = 'Show';
            toggleButton.classList.add(
                'text-blue-500', 
                'text-sm', 
                'ml-2', 
                'hover:text-blue-700',
                'focus:outline-none'
            );

            // Insert the toggle button next to the label
            label.appendChild(toggleButton);

            // Toggle password visibility
            toggleButton.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleButton.textContent = 'Hide';
                } else {
                    passwordField.type = 'password';
                    toggleButton.textContent = 'Show';
                }
            });
        }
    });
});