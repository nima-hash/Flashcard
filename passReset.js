import { showSystemMessage, fetchData, validatePassword } from './utils.js';

document.addEventListener('DOMContentLoaded', async function() {
    const token = window.resetConfig.token;
    const email = window.resetConfig.email;

    const resetHeader = document.getElementById('resetHeader');
    const messageArea = document.getElementById('messageArea');
    const passwordResetForm = document.getElementById('passwordResetForm');
    const newPasswordInput = document.getElementById('newPassword');
    const confirmNewPasswordInput = document.getElementById('confirmNewPassword');
    const togglePasswordCheckbox = document.getElementById('togglePassword');

    // Show/Hide Password Toggle
    if (togglePasswordCheckbox && newPasswordInput && confirmNewPasswordInput) {
        togglePasswordCheckbox.addEventListener('change', function () {
            const type = this.checked ? 'text' : 'password';
            newPasswordInput.type = type;
            confirmNewPasswordInput.type = type;
        });
    }

    // Function to display validation errors
    function displayValidationError(fieldId, message, container = document) {
        const inputElement = container.querySelector(`#${fieldId}`);
        if (inputElement) {
            inputElement.classList.add('is-invalid');
            const errorElement = container.querySelector(`#${fieldId}Error`); 
            if (errorElement) {
                errorElement.textContent = message;
            }
        }
    }

    // Initial token verification
    if (!token || !email) {
        resetHeader.textContent = 'Invalid Link';
        messageArea.className = 'alert alert-danger text-center';
        messageArea.textContent = 'Invalid password reset link. Please ensure you clicked the full link from your email.';
        return;
    }

    try {
        const result = await fetchData(`/api/users/verifyToken?token=${encodeURIComponent(token)}&email=${encodeURIComponent(email)}`, 'GET');

        if (result.success) {
            resetHeader.textContent = 'Set New Password';
            messageArea.className = 'alert alert-success text-center';
            messageArea.textContent = 'Your reset link is valid. Please enter your new password.';
            passwordResetForm.classList.remove('d-none'); 
        } else {
            resetHeader.textContent = 'Invalid or Expired Link';
            messageArea.className = 'alert alert-danger text-center';
            messageArea.textContent = result.message || 'This password reset link is invalid or has expired. Please request a new one.';
        }
    } catch (error) {
        console.error("Token verification failed:", error);
        resetHeader.textContent = 'Error';
        messageArea.className = 'alert alert-danger text-center';
        messageArea.textContent = 'An error occurred during token verification. Please try again later.';
    }

    // Handle new password submission
    if (passwordResetForm) {
        passwordResetForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            // Clear previous validation messages
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

            const newPassword = newPasswordInput.value.trim();
            const confirmNewPassword = confirmNewPasswordInput.value.trim();

            let isValid = true;

            // --- Password Validation ---
            const passwordError = validatePassword(newPassword);
            if (passwordError) {
                displayValidationError('newPassword', passwordError);
                isValid = false;
            }
            if (newPassword !== confirmNewPassword) {
                displayValidationError('confirmNewPassword', 'Passwords do not match.');
                isValid = false;
            }

            if (!isValid) {
                showSystemMessage('Please correct the errors in the form.', 'error', true);
                return;
            }

            try {
                const result = await fetchData('/api/users/resetByToken', 'POST', {
                    token: token,
                    email: email,
                    newPassword: newPassword
                });

                if (result.success) {
                    showSystemMessage(result.message || 'Your password has been reset successfully! You can now log in.', 'info', false);
                    resetHeader.textContent = 'Password Reset Successful!';
                    messageArea.className = 'alert alert-success text-center';
                    messageArea.textContent = result.message || 'Your password has been reset successfully! You can now log in.';
                    passwordResetForm.classList.add('d-none'); 
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 3000);
                } else {
                    showSystemMessage(result.message || 'Failed to reset password. Please try again.', 'error', false);
                    if (result.errors) {
                        for (const field in result.errors) {
                            displayValidationError(field, result.errors[field]);
                        }
                    }
                }
            } catch (error) {
                showSystemMessage(error.message || 'Failed to reset password. Please try again.', 'error', false);

            }
        });
    }
});
