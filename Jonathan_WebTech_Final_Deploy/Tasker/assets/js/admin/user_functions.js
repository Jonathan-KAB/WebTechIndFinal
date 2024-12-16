document.addEventListener('DOMContentLoaded', function() {
    // Show Add User Modal
    window.showAddUserModal = function() {
        console.log('Add New User button clicked');
        
        const userModal = document.getElementById('userModal');
        const modalTitle = document.getElementById('modalTitle');
        const userId = document.getElementById('userId');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const roleInput = document.getElementById('role');
        const passwordInput = document.getElementById('password');
        const passwordFields = document.querySelector('.password-fields');

        // Validate modal elements exist
        if (!userModal || !modalTitle || !userId || !usernameInput || 
            !emailInput || !roleInput || !passwordInput || !passwordFields) {
            console.error('One or more modal elements not found');
            return;
        }

        // Reset modal for adding new user
        modalTitle.textContent = 'Add New User';
        userId.value = '';
        usernameInput.value = '';
        emailInput.value = '';
        roleInput.value = 'user';
        passwordInput.value = '';
        
        // Show password field for new users
        passwordFields.style.display = 'block';
        
        // Show the modal
        userModal.classList.remove('hidden');
    }

    // Edit User Function
    window.editUser = function(id) {
        console.log('Editing user with ID:', id);
    
        // Forcibly select modal elements
        const userModal = document.querySelector('#userModal');
        const modalTitle = document.querySelector('#modalTitle');
        const userId = document.querySelector('#userId');
        const usernameInput = document.querySelector('#username');
        const emailInput = document.querySelector('#email');
        const roleInput = document.querySelector('#role');
        const passwordFields = document.querySelector('.password-fields');
    
        // Log each element to verify its existence
        console.log('Modal:', userModal);
        console.log('Modal Title:', modalTitle);
        console.log('User ID Input:', userId);
        console.log('Username Input:', usernameInput);
        console.log('Email Input:', emailInput);
        console.log('Role Input:', roleInput);
        console.log('Password Fields:', passwordFields);
    
        // Validate modal elements exist
        if (!userModal) {
            console.error('User modal not found');
            alert('Modal element not found');
            return;
        }
    
        // Fetch user details
        fetch(`../../functions/admin/get_user.php?id=${id}`)
            .then(response => {
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(user => {
                console.log('Retrieved user data:', user);
    
                // Populate modal with user data
                modalTitle.textContent = 'Edit User';
                userId.value = user.id;
                usernameInput.value = user.username;
                emailInput.value = user.email;
                roleInput.value = user.role;
                
                // Hide password field for editing
                if (passwordFields) {
                    passwordFields.style.display = 'none';
                }
                
                // Force modal to be visible using multiple methods
                userModal.style.display = 'flex';
                userModal.classList.remove('hidden');
                userModal.classList.add('block');
            })
            .catch(error => {
                console.error('Full error details:', error);
                alert(`Failed to fetch user details: ${error.message}`);
            });
    }

    // Delete User Function
    window.deleteUser = function(id) {
        // Create delete confirmation modal
        const deleteModal = document.createElement('div');
        deleteModal.innerHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-8 rounded-lg max-w-md w-full">
                    <h3 class="text-xl font-bold mb-4 text-red-600">Confirm User Deletion</h3>
                    <p class="mb-4">To permanently delete this user, type <strong>DELETE</strong> in all capital letters:</p>
                    <input type="text" id="deleteConfirmInput" class="w-full p-2 border rounded mb-4" />
                    <div class="flex justify-end gap-4">
                        <button id="cancelDeleteBtn" class="admin-button admin-button-secondary">Cancel</button>
                        <button id="confirmDeleteBtn" class="admin-button admin-button-danger" disabled>Delete</button>
                    </div>
                </div>
            </div>
        `;

        // Append to body
        document.body.appendChild(deleteModal);

        const deleteConfirmInput = deleteModal.querySelector('#deleteConfirmInput');
        const confirmDeleteBtn = deleteModal.querySelector('#confirmDeleteBtn');
        const cancelDeleteBtn = deleteModal.querySelector('#cancelDeleteBtn');

        // Enable/disable delete button based on input
        deleteConfirmInput.addEventListener('input', function() {
            confirmDeleteBtn.disabled = this.value !== 'DELETE';
        });

        // Cancel deletion
        cancelDeleteBtn.addEventListener('click', () => {
            document.body.removeChild(deleteModal);
        });

        // Confirm deletion
        confirmDeleteBtn.addEventListener('click', () => {
            fetch(`../../functions/admin/delete_user.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Reload page or remove row from table
                    location.reload();
                } else {
                    alert(result.message || 'Failed to delete user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the user');
            });
        });
    }

    // Close Modal Function
    window.closeModal = function() {
        const userModal = document.getElementById('userModal');
        if (userModal) {
            userModal.classList.add('hidden');
        }
    }

    // Handle User Form Submission
    const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(userForm);
            
            fetch('../../functions/admin/save_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Failed to save user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    }
});