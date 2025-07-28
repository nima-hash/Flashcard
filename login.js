// login.js
import { showSystemMessage, fetchData, renderNavbar, validatePassword } from './utils.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initial navbar render 
    renderNavbar(window.appConfig.isGuest, window.appConfig.userName);

    const loginForm = document.getElementById('login__form');
    const userNameInput = document.getElementById('userName');
    const passwordInput = document.getElementById('password');
    const togglePasswordCheckbox = document.getElementById('togglePassword');
    const loginNotice = document.getElementById('login_notice');
    const passwordResetModalEl = document.getElementById('passwordResetModal');
    const passwordResetModal = new bootstrap.Modal(passwordResetModalEl); 
    const passwordResetRequestForm = document.getElementById('passwordResetRequestForm');
    const resetIdentifierInput = document.getElementById('resetIdentifier');

    // Show/Hide Password Toggle
    if (togglePasswordCheckbox && passwordInput) {
        togglePasswordCheckbox.addEventListener('change', function () {
            const type = this.checked ? 'text' : 'password';
            passwordInput.type = type;
        });
    }

    // Hide login notice after 5 seconds if it exists
    if (loginNotice) {
        setTimeout(() => {
            loginNotice.classList.add('d-none'); 
        }, 5000);
    }

    // Login Submission Handler
    if (loginForm) {
        loginForm.addEventListener('submit', async function(event) {
            event.preventDefault(); 

            // Clear previous validation messages
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

            const userName = userNameInput.value.trim();
            const password = passwordInput.value.trim();

            let isValid = true;

            if (!userName) {
                displayValidationError('userName', 'Username or Email is required.');
                isValid = false;
            }
            if (!password) {
                displayValidationError('password', 'Password is required.');
                isValid = false;
            }

            if (!isValid) {
                showSystemMessage('Please fill in all required fields.', 'error', true);
                return;
            }

            try {
                const result = await fetchData('/api/users/login', 'POST', {
                    email: userName,
                    password: password
                });

                if (result.success) {
                    showSystemMessage(result.message || 'Login successful!', 'info', false);
                    window.location.replace("index.php"); 
                } else {
                    showSystemMessage(result.message || 'Login failed. Please check your credentials.', 'error', false);
                    if (result.errors) {
                        for (const field in result.errors) {
                            displayValidationError(field, result.errors[field]);
                        }
                    }
                }
            } catch (error) {
                showSystemMessage(error.message || 'Login failed. Please check your credentials.', 'error', false);
            }
        });
    }

    // Reset Password Submission Handler
    if (passwordResetRequestForm) {
        passwordResetRequestForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            // Clear previous validation messages
            document.querySelectorAll('#passwordResetModal .invalid-feedback').forEach(el => el.textContent = '');
            document.querySelectorAll('#passwordResetModal .form-control').forEach(el => el.classList.remove('is-invalid'));

            const identifier = resetIdentifierInput.value.trim();

            if (!identifier) {
                displayValidationError('resetIdentifier', 'Username or Email is required.', passwordResetModalEl);
                showSystemMessage('Please enter your username or email.', 'error', true);
                return;
            }

            try {
                // Send request to backend 
                const result = await fetchData('/api/users/passwordResetEmailRequest', 'POST', { identifier: identifier });

                if (result.success) {
                    showSystemMessage(result.message || 'A password reset link has been sent to your email.', 'info', false);
                    passwordResetModal.hide(); 
                } else {

                    showSystemMessage(result.message || 'Failed to send reset link. Please try again.', 'error', false);
                    if (result.errors) {
                        for (const field in result.errors) {

                            displayValidationError(field, result.errors[field], passwordResetModalEl);
                        }
                    }
                }
            } catch (error) {
                showSystemMessage(error.message || 'Password reset request failed:', 'error', false);
            }
        });
    }

    // Utility function for validation feedback
    function displayValidationError(fieldId, message) {
        const inputElement = document.getElementById(fieldId);
        if (inputElement) {
            inputElement.classList.add('is-invalid');
            const errorElement = document.getElementById(fieldId + 'Error');
            if (errorElement) {
                errorElement.textContent = message;
            }
        }
    }

    // Logout button handler
    document.body.addEventListener('click', async function(event) {
        if (event.target.classList.contains('logoutBtn')) {
            event.preventDefault();
            try {
                const result = await fetchData('/api/users/logout', 'POST');
                
                if (result.success) {
                    showSystemMessage(result.message, 'info');
                    window.location.href = 'index.php';
                } else {
                    showSystemMessage(result.message, 'error', false);
                }
            } catch (error) {
                console.error('Logout error:', error);
            }
        }
    });
});
