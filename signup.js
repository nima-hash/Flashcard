// signup.js
import { showSystemMessage, fetchData, renderNavbar, validatePassword } from './utils.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initial navbar render (assuming appConfig is available)
    renderNavbar(window.appConfig.isGuest, window.appConfig.userName);

    const signupForm = document.getElementById('signup__form');
    const submitBtn = document.getElementById('submit_Btn'); 


    const togglePasswordCheckbox = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');

    // Show/Hide Password Toggle
    if (togglePasswordCheckbox && passwordInput && confirmPasswordInput) {
        togglePasswordCheckbox.addEventListener('change', function () {
            const type = this.checked ? 'text' : 'password';
            passwordInput.type = type;
            confirmPasswordInput.type = type;
        });
    }

    if (submitBtn) { 
        submitBtn.addEventListener('click', async function(event) {
            event.preventDefault(); 

            // Clear previous validation messages
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

            const formData = new FormData(signupForm);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value.trim();
            });

            let isValid = true;

            // Basic client-side validation 
            if (!data.userName) {
                displayValidationError('userName', 'Username is required.');
                isValid = false;
            }
            if (!data.email || !validateEmail(data.email)) {
                displayValidationError('email', 'A valid email address is required.');
                isValid = false;
            }

            //Password validation
            const passwordError = validatePassword(data.password);
            if (passwordError) {
                displayValidationError('password', passwordError);
                isValid = false;
            }
            if (data.password !== data.confirmPassword) {
                displayValidationError('confirmPassword', 'Passwords do not match.');
                isValid = false;
            }
            if (data.phone && !/^\+?\d+$/.test(data.phone)) { 
                displayValidationError('phone', 'Phone number must contain only digits (and optional leading +).');
                isValid = false;
            }

            if (!isValid) {
                showSystemMessage('Please correct the errors in the form.', 'error', true);
                return;
            }

            try {
          
                const result = await fetchData('/api/users/create', 'POST', data); 

                if (result.success) {
                    showSystemMessage(result.message || 'Registration successful! Please log in.', 'info', false);
                    window.location.replace("login.php"); 
                } else {
                    showSystemMessage(result.message || 'Registration failed. Please try again.', 'error', false);
                    if (result.errors) {
                        for (const field in result.errors) {
                            displayValidationError(field, result.errors[field]);
                        }
                    }
                }
            } catch (error) {
                // Handled by fetchData
            }
        });
    } else {
        showSystemMessage('Submit_Btn element not found!', 'error', false)
    }

    // Utility functions for validation feedback
    function displayValidationError(fieldId, message) {
        const inputElement = document.getElementById(fieldId);
        if (inputElement) {
            inputElement.classList.add('is-invalid');
            let feedbackElement = inputElement.nextElementSibling;
            // Ensure the next sibling is indeed the invalid-feedback div
            if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                feedbackElement.textContent = message;
            } else {
                // If not, create one (might happen if structure is slightly off)
                feedbackElement = document.createElement('div');
                feedbackElement.classList.add('invalid-feedback');
                feedbackElement.textContent = message;
                inputElement.parentNode.insertBefore(feedbackElement, inputElement.nextSibling);
            }
        }
    }
    
    // Email validation regex
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});
