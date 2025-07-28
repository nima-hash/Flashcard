
/**
 * Shows a system message toast.
 * @param {string} message The message to display.
 * @param {string} type The type of message ('info' or 'error').
 * @param {boolean} autohide If true, the toast hides automatically. If false, it persists.
 */
export function showSystemMessage(message, type = 'info', autohide = true) {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        console.error('Toast container not found!');
        return;
    }

    const toastEl = document.createElement('div');
    toastEl.className = 'toast text-white shadow-sm'; // Base classes

    if (type === 'error') {
        toastEl.classList.add('bg-danger');
    } else {
        toastEl.classList.add('bg-success');
    }

    toastEl.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white ms-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);

    // Add animation class
    toastEl.classList.add('showing');

    const toast = new bootstrap.Toast(toastEl, {
        autohide: autohide,
        delay: 5000
    });

    toast.show();

    // Remove showing class after animation completes
    setTimeout(() => {
        toastEl.classList.remove('showing');
    }, 500); // match animation duration

    // When toast is dismissed, animate out before removing
    toastEl.addEventListener('hide.bs.toast', () => {
        toastEl.classList.add('hiding');
    });

    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

/**
 * Helper to escape HTML entities to prevent XSS.
 * @param {string} str The string to escape.
 * @returns {string} The escaped string.
 */
export function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

/**
 * Shuffles an array in place (Fisher-Yates algorithm).
 * @param {Array} array The array to shuffle.
 * @returns {Array} The shuffled array.
 */
export function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]]; // Swap elements
    }
    return array;
}

/**
 * Fetches data from a given URL using the specified method and data.
 * Handles JSON parsing and error messages.
 * @param {string} url The API endpoint URL.
 * @param {string} method The HTTP method (GET, POST, PUT, DELETE).
 * @param {object|null|FormData} data The data to send in the request body (for POST/PUT).
 * @returns {Promise<object>} The JSON response data.
 * @throws {Error} If the API call fails or returns an error.
 */
export async function fetchData(url, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {},
            credentials: 'include' // For cookies if needed
        };

        if (data instanceof FormData) {
            // For FormData, fetch automatically sets 'Content-Type': 'multipart/form-data'
            // Do NOT set Content-Type header manually for FormData, it breaks boundary
            options.body = data;
        } else if (data) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        } else if (method === 'POST' || method === 'PUT') {
            options.headers['Content-Type'] = 'application/json'; // Default for empty POST/PUT
        }

        const response = await fetch(url, options);

        if (!response.ok) {
            // Try to parse JSON error message from backend
            let errorData = {};
            try {
                errorData = await response.json();
            } catch (e) {
                // If not JSON, get as text
                // errorData.message = await response.text();
            }
            throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
        }
        
        // Handle cases where backend might return empty response or non-JSON for success
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return await response.json();
        } else {
            // If it's not JSON, assume success and return a generic success object
            // This handles cases like '1' from ajax.php or simple text responses
            const textResponse = await response.text();
            if (textResponse.trim() === '1') {
                return { success: true, message: 'Operation successful.' };
            }
            return { success: true, message: textResponse || 'Operation successful.' };
        }

    } catch (error) {
        console.error('API call failed:', error);
        showSystemMessage(`Error: ${error.message}`, 'error', false);
        throw error; // Re-throw to allow calling function to handle
    }
}

/**
 * Renders the navigation bar based on guest/logged-in status.
 */
export function renderNavbar(isGuest, userName) {
    const userAuthDropdown = document.getElementById('userAuthDropdown');
    const usernameDisplay = document.getElementById('usernameDisplay');
    const loggedInItems = document.querySelectorAll('#categoryDropdownItem, #deckDropdownItem, #learnDropdownItem');
        console.log(isGuest)

    if (isGuest) {
        console.log('here')
        usernameDisplay.textContent = 'Guest';
        userAuthDropdown.querySelector('.dropdown-menu').innerHTML = `
            <li><a class="dropdown-item" href="login.php">Login</a></li>
        `;
        loggedInItems.forEach(item => item.classList.add('d-none'));
    } else {
        usernameDisplay.textContent = userName;
        userAuthDropdown.querySelector('.dropdown-menu').innerHTML = `
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><a class="dropdown-item logoutBtn" href="#">Sign Out</a></li>
        `;
        loggedInItems.forEach(item => item.classList.remove('d-none'));
    }
}

export function validatePassword(password) {
    if (password.length < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        return "Password must contain at least one special character.";
    }
    if (!/\d/.test(password)) {
        return "Password must contain at least one number.";
    }
    if (!/[A-Z]/.test(password)) {
        return "Password must contain at least one capital letter.";
    }
    if (!/[a-z]/.test(password)) {
        return "Password must contain at least one small letter.";
    }
    return null; // Password is valid
}

