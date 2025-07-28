<?php
require_once __DIR__ . "/inc/bootstrap.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logging Out...</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link href="style.css" rel="stylesheet" />
  <link href="auth.css" rel="stylesheet" />
</head>
<body>

<div class="auth-container d-flex flex-column align-items-center justify-content-center py-5">
  <div class="auth-card card bg-dark text-white shadow-lg p-4 p-md-5 rounded-3 text-center">
    <h4 class="text-info mb-3">Logging you out...</h4>
    <p class="text-white-50">Please wait while we securely log you out of your account.</p>
    <div class="spinner-border text-info mt-3" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script type="module" src="utils.js"></script>
<script type="module">
    import { showSystemMessage, fetchData } from './utils.js';

    document.addEventListener('DOMContentLoaded', async function() {
        try {
            // Call the backend API logout endpoint
            const result = await fetchData('/api/auth/logout', 'POST'); 
            
            if (result.success) {
                showSystemMessage(result.message || 'You have been logged out successfully!', 'info', false);
                setTimeout(() => {
                    window.location.replace("login.php"); 
                }, 1500);
            } else {
                showSystemMessage(result.message || 'Logout failed. Please try again.', 'error', false);
                // If logout fails, redirect to login anyway
                setTimeout(() => {
                    window.location.replace("login.php"); 
                }, 3000);
            }
        } catch (error) {
            console.error('Logout error:', error);
            showSystemMessage('An error occurred during logout. Please try again.', 'error', false);
            setTimeout(() => {
                window.location.replace("login.php"); 
            }, 3000);
        }
    });
</script>

</body>
</html>
