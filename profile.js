let isGuest = window.appConfig.isGuest;
let userName = window.appConfig.userName;

// Import shared utility functions
import { showSystemMessage, escapeHtml, fetchData, renderNavbar } from './utils.js';

let originalProfileData = {}; 

// --- PROFILE PAGE FUNCTIONS ---

//  Toggles the edit mode for the personal information form.

function toggleProfileEditMode(enable) {
    const formInputs = document.querySelectorAll('#profileInfoForm input');
    const editBtn = document.getElementById('editProfileInfoBtn');
    const saveBtn = document.getElementById('saveProfileInfoBtn');
    const cancelBtn = document.getElementById('cancelProfileInfoBtn');

    formInputs.forEach(input => {
        input.readOnly = !enable;
        // Optionally, add/remove a class for styling read-only vs editable
        if (enable) {
            input.classList.remove('form-control[readonly]'); // If you added custom readonly styles
        } else {
            input.classList.add('form-control[readonly]'); // If you added custom readonly styles
        }
    });

    if (enable) {
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
    } else {
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    }
}


//  Fetches user profile data from the backend and renders it.

async function renderProfilePage() {
    const profileUserNameEl = document.getElementById('profileUserName');
    const profileEmailEl = document.getElementById('profileEmail');
    const profilePictureDisplayEl = document.getElementById('profilePictureDisplay');
    const userNameInput = document.getElementById('userName');
    const emailInput = document.getElementById('email');
    const nameInput = document.getElementById('name');
    const lastNameInput = document.getElementById('lastName');
    const addressInput = document.getElementById('address');
    const phoneInput = document.getElementById('phone');
    const birthdayInput = document.getElementById('birthday');

    if (!profileUserNameEl) return; // Not on the profile page

    try {
        const data = await fetchData('/api/users/get'); // Assuming this endpoint returns user data
        if (data.success && data.user) {
            const user = data.user;
            
            // Store original data for cancel functionality
            originalProfileData = {
                userName: user.userName || '',
                email: user.email || '',
                name: user.name || '',
                lastName: user.lastName || '',
                address: user.address || '',
                phone: user.phone || '',
                birthday: user.birthday || ''
            };

            profileUserNameEl.textContent = escapeHtml(user.userName || 'N/A');
            profileEmailEl.textContent = escapeHtml(user.email || 'N/A');
            userNameInput.value = user.userName || '';
            emailInput.value = user.email || '';
            nameInput.value = user.name || '';
            lastNameInput.value = user.lastName || '';
            addressInput.value = user.address || '';
            phoneInput.value = user.phone || '';
            birthdayInput.value = user.birthday || '';

            if (user.profile_picture_url) {
                profilePictureDisplayEl.src = user.profile_picture_url;
            } else {
                profilePictureDisplayEl.src = "/media/profile_Img/account-icon-25499.png"; // Default placeholder
            }
            toggleProfileEditMode(false); // Ensure fields are read-only initially
        } else {
            showSystemMessage(data.message || 'Failed to load profile data.', 'error', false);
        }
    } catch (error) {
        // Error handled by fetchData
    }
}

//  Handles the submission of the personal information form.

async function handleProfileInfoFormSubmit(event) {
    event.preventDefault();
    const userName = document.getElementById('userName').value.trim();
    const email = document.getElementById('email').value.trim();
    const name = document.getElementById('name').value.trim();
    const lastName = document.getElementById('lastName').value.trim();
    const address = document.getElementById('address').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const birthday = document.getElementById('birthday').value.trim();

    if (!userName || !email) {
        showSystemMessage('Username and Email cannot be empty.', 'error', true);
        return;
    }

    try {
        const result = await fetchData('/api/users/update', 'POST', { 
            userName, email, name, lastName, address, phone, birthday
        });
        if (result.success) {
            showSystemMessage(result.message, 'info');
            // Update displayed username in navbar and on profile page
            if (document.getElementById('usernameDisplay')) {
                document.getElementById('usernameDisplay').textContent = userName;
            }
            document.getElementById('profileUserName').textContent = userName;
            document.getElementById('profileEmail').textContent = email;
            // Update global userName if needed
            window.appConfig.userName = userName;
            // userName = userName; // This line is redundant as userName is already updated via window.appConfig
            
            // Re-fetch data to update originalProfileData and disable edit mode
            await renderProfilePage(); 
        } else {
            showSystemMessage(result.message, 'error', false);
        }
    } catch (error) {
        // Error handled by fetchData
    }
}

//  Handles the submission of the profile picture upload form.
async function handleProfilePictureFormSubmit(event) {
    event.preventDefault();
    const fileInput = document.getElementById('profilePictureInput');
    const file = fileInput.files[0];

    if (!file) {
        showSystemMessage('Please select an image file to upload.', 'error', true);
        return;
    }

    if (file.size > 2 * 1024 * 1024) { 
        showSystemMessage('File size exceeds 2MB limit.', 'error', true);
        return;
    }

    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        showSystemMessage('Only JPG, PNG, and GIF formats are allowed.', 'error', true);
        return;
    }

    // Read file as Data URL (Base64)
    const reader = new FileReader();
    reader.onloadend = async () => {
        const base64Image = reader.result; 
        
        try {
            // Send base64 string to backend
            const result = await fetchData('/api/users/uploadPicture', 'POST', { imageData: base64Image });
            if (result.success) {
                showSystemMessage(result.message, 'info');
                if (result.profile_picture_url) {
                    document.getElementById('profilePictureDisplay').src = result.profile_picture_url;
                }
                fileInput.value = '';
            } else {
                showSystemMessage(result.message, 'error');
            }
        } catch (error) {
            console.log(error)
        }
    };
    reader.onerror = () => {
        showSystemMessage('Failed to read file.', 'error', true);
    };
    reader.readAsDataURL(file); 
}

//  Handles the submission of the password reset form.
async function handlePasswordResetFormSubmit(event) {
    event.preventDefault();
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmNewPassword = document.getElementById('confirmNewPassword').value;

    if (!currentPassword || !newPassword || !confirmNewPassword) {
        showSystemMessage('All password fields are required.', 'error', true);
        return;
    }

    if (newPassword.length < 6) {
        showSystemMessage('New password must be at least 6 characters long.', 'error', true);
        return;
    }

    if (newPassword !== confirmNewPassword) {
        showSystemMessage('New password and confirmation do not match.', 'error', true);
        return;
    }

    try {
        const result = await fetchData('/api/users/resetPassword', 'POST', {
            currentPassword,
            newPassword
        });
        if (result.success) {
            showSystemMessage(result.message, 'info');
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmNewPassword').value = '';
        } else {
            showSystemMessage(result.message, 'error', false);
        }
    } catch (error) {
        // Error handled by fetchData
    }
}


// --- Event Listeners ---

document.addEventListener('DOMContentLoaded', async () => {
    renderNavbar(isGuest, userName); 
    
    await renderProfilePage(); 
    // Attach profile-specific event listeners
    document.getElementById('profileInfoForm').addEventListener('submit', handleProfileInfoFormSubmit);
    document.getElementById('editProfileInfoBtn').addEventListener('click', () => toggleProfileEditMode(true));
    document.getElementById('cancelProfileInfoBtn').addEventListener('click', () => {
        // Restore original data and disable edit mode
        document.getElementById('userName').value = originalProfileData.userName;
        document.getElementById('email').value = originalProfileData.email;
        document.getElementById('name').value = originalProfileData.name;
        document.getElementById('lastName').value = originalProfileData.lastName;
        document.getElementById('address').value = originalProfileData.address;
        document.getElementById('phone').value = originalProfileData.phone;
        document.getElementById('birthday').value = originalProfileData.birthday;
        toggleProfileEditMode(false);
    });
    document.getElementById('profilePictureForm').addEventListener('submit', handleProfilePictureFormSubmit);
    document.getElementById('passwordResetForm').addEventListener('submit', handlePasswordResetFormSubmit);

    // Logout button
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
                // Error handled by fetchData
            }
        }
    });
});
