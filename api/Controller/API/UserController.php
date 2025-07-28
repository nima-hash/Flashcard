<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/functions.php';
require_once __DIR__ . "/../../inc/bootstrap.php";

class UserController extends BaseController
{
    // Handles the /api/users/login request
    // Checks user credentials and logs him in with username or email

    public function loginAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405, array('HTTP/1.1 405 Method Not Allowed'));
            return;
        }

        try {
            
            $identifier = $requestBody['email'] ?? null;
            $password = $requestBody['password'] ?? null;

            if (empty($identifier) || empty($password)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Username/Email and password are required.']), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }


            $userModel = new UserModel();
            $user = null;

            // Determine if identifier is an email or username
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $users = $userModel->getUserByEmail($identifier);
            } else {
                $users = $userModel->getUserByUsername($identifier);
            }
            
            $user = $users[0];
            
            $hashedPassword = $user['password_hash'];
            
            if ($user && password_verify($password, $hashedPassword)) {
                
                // Prevent session fixation attacks
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['userName'] = $user['userName'];
                $_SESSION['userEmail'] = $user['email'];
                $_SESSION['LoggedIn'] = true;
            
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Login successful!', 'userName' => $user['userName'], 'userId' => $user['id']]), 200, array('HTTP/1.1 200 OK'));
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Invalid username/email or password.']), 401, array('HTTP/1.1 401 Unauthorized'));
            }

        } catch (Exception $e) {
            error_log("Error during login in AuthController: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error during login: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }


    public function getAction($queryParams)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'GET') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405);
            return;
        }

        try {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $userModel = new UserModel();
            $user = $userModel->getUserById($userId);

            if ($user) {
                unset($user['password_hash']);
                $this->sendOutput(json_encode(['success' => true, 'user' => $user]), 200);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not found.']), 404);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error fetching user data: ' . $e->getMessage()]), 500);
        }
    }

    public function createAction($queryParams, $requestBody)
    {
        // Ensure the request method is POST
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405, array('HTTP/1.1 405 Method Not Allowed'));
            return;
        }

        try {

            $userName = $requestBody['userName'] ?? null;
            $email = $requestBody['email'] ?? null;
            $password = $requestBody['password'] ?? null;
            $confirmPassword = $requestBody['confirmPassword'] ?? null;

            $name = !empty($requestBody['name']) ? $requestBody['name'] : null;
            $lastName = !empty($requestBody['lastName']) ? $requestBody['lastName'] : null;
            $phone = !empty($requestBody['phone']) ? $requestBody['phone'] : null;
            $address = !empty($requestBody['address']) ? $requestBody['address'] : null;
            $birthday = !empty($requestBody['birthday']) ? $requestBody['birthday'] : null;

            $errors = [];
            
            // Server-side validation
            if (empty($userName)) {
                $errors['userName'] = 'Username is required.';
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'A valid email address is required.';
            }
            $passwordStrengthError = validate_password_strength($password);
            if ($passwordStrengthError) {
                $errors['password'] = $passwordStrengthError;
            }
            if ($password !== $confirmPassword) {
                $errors['confirmPassword'] = 'Passwords do not match.';
            }

            $userModel = new UserModel();

            // Check for duplicate username and email
            if ($userModel->getUserByUsername($userName)) {
                $errors['userName'] = 'Username already taken.';
            }
            if ($userModel->getUserByEmail($email)) {
                $errors['email'] = 'Email already registered.';
            }

            // If any validation errors, send them back
            if (!empty($errors)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $newUserId = $userModel->createUser([$userName,$email, $hashedPassword, $name, $lastName, $phone, $address, $birthday]);

            if ($newUserId) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Registration successful! Please log in.', 'userId' => $newUserId]), 201, array('HTTP/1.1 201 Created'));
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to create user.']), 500, array('HTTP/1.1 500 Internal Server Error'));
            }

        } catch (Exception $e) {
            // Log the error 
            error_log("Error in UserController::createAction: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Server error during registration: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }

    //  Handles POST /api/users/update request
    //  Updates user's personal information.

    public function updateAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405);
            return;
        }

        try {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $userName = $requestBody['userName'] ?? null;
            $email = $requestBody['email'] ?? null;

            if (empty($userName) || empty($email)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Username and email are required.']), 400);
                return;
            }

            $address = !empty($requestBody['address']) ? $requestBody['address'] : null;
            $name = !empty($requestBody['name']) ? $requestBody['name'] : null;
            $birthday = !empty($requestBody['birthday']) ? $requestBody['birthday'] : null;
            $lastName = !empty($requestBody['lastName']) ? $requestBody['lastName'] : null;
            $phone = !empty($requestBody['phone']) ? $requestBody['phone'] : null;


            $userModel = new UserModel();
            $updated = $userModel->updateUser([$email, $address, $birthday, $name, $lastName, $phone, $userId]);

            if ($updated) {
                $_SESSION['userName'] = $userName;
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Profile updated successfully!']), 200);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to update profile or no changes made.']), 400);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]), 500);
        }
    }

    //  Handles POST /api/user/uploadPicture
    //  Uploads and saves user's profile picture (expects base64 image data).

    public function uploadPictureAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405);
            return;
        }

        try {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            // Expects a base64 data URL
            $imageData = $requestBody['imageData'] ?? null;
            if (empty($imageData)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'No image data provided.']), 400);
                return;
            }

            // Extract image type and base64 data
            if (!preg_match('/^data:image\/(jpeg|png|gif);base64,(.*)$/i', $imageData, $matches)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Invalid image data format. Only JPG, PNG, and GIF base64 data URLs are accepted.']), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            $imageType = $matches[1];
            $base64Data = $matches[2];
            
            $decodedImage = base64_decode($base64Data);
            if ($decodedImage === false) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to decode image data.']), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            // Define the upload directory and ensure it exists
            $uploadDir = __DIR__ . '/../../../media/profile_Img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate a unique filename
            $fileName = uniqid('profile_') . '.' . $imageType;
            $filePath = $uploadDir . $fileName;
            $fileUrl = '/media/profile_Img/' . $fileName;
            
            // Save the image file
            if (!file_put_contents($filePath, $decodedImage)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to save image file to disk. Check directory permissions.']), 500, array('HTTP/1.1 500 Internal Server Error'));
                return;
            }

            $userModel = new UserModel();            
            $updated = $userModel->updateProfilePicture([$fileUrl, $userId]); 

            if ($updated) {
    
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Profile picture uploaded!', 'profile_picture_url' => $fileUrl]), 200);
                       
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to update profile picture.']), 500);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error uploading picture: ' . $e->getMessage()]), 500);
        }
    }



    //  Handles /api/user/resetPassword request
    //  Resets authenticated user's password.
    
    public function resetPasswordAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405);
            return;
        }

        try {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $currentPassword = $requestBody['currentPassword'] ?? null;
            $newPassword = $requestBody['newPassword'] ?? null;

            if (empty($currentPassword) || empty($newPassword)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Current and new passwords are required.']), 400);
                return;
            }

            $passwordStrengthError = validate_password_strength($newPassword);
            if ($passwordStrengthError) {
                $this->sendOutput(json_encode(['success' => false, 'message' => $passwordStrengthError]), 401);
                return;
            }

            $userModel = new UserModel();

            // Verify current password
            if (!$userModel->verifyPassword($userId, $currentPassword)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Incorrect current password.']), 401);
                return;
            }

            // Hash new password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password in DB
            $updated = $userModel->updatePassword($userId, $hashedNewPassword);

            if ($updated) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Password reset successfully!']), 200);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to reset password.']), 500);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error resetting password: ' . $e->getMessage()]), 500);
        }
    }


    public function logoutAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405, array('HTTP/1.1 405 Method Not Allowed'));
            return;
        }

        try {
            // Destroy all session data
            $_SESSION = array(); 
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();

            $this->sendOutput(json_encode(['success' => true, 'message' => 'Logged out successfully!']), 200, array('HTTP/1.1 200 OK'));

        } catch (Exception $e) {
            error_log("Error during logout in AuthController: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error during logout: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }
    
    public function passwordResetEmailRequestAction($queryParams, $requestBody) {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405, array('HTTP/1.1 405 Method Not Allowed'));
            return;
        }

        try {
            $identifier = $requestBody['identifier'] ?? null;

            if (empty($identifier)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Username or email is required.']), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            $userModel = new UserModel();
            $user = null;

            // Find user by email or username
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $users = $userModel->getUserByEmail($identifier);
                $user = $users[0];
            } else {
                $users = $userModel->getUserByUsername($identifier);
                $user = $users[0];

            }

            // Return a generic response to prevent user enumeration
            if (!$user) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'If an account with that username or email exists, a password reset link has been sent.']), 200, array('HTTP/1.1 200 OK'));
                return;
            }

            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store the token in the database
            $updated = $userModel->savePasswordResetToken([$user['id'], $token, $expiresAt]);

            if (!$updated) {
                error_log("Failed to save password reset token for user ID: " . $user['id']);
                $this->sendOutput(json_encode(['success' => true, 'message' => 'If an account with that username or email exists, a password reset link has been sent.']), 200, array('HTTP/1.1 200 OK'));
                return;
            }

            // Construct the reset link
            $resetLink = 'http://localhost:3000/passReset.php?token=' . $token . '&email=' . urlencode($user['email']);

            // Send the email
            $subject = 'Flashcards - Password Reset Request';
            $message = "Dear " . htmlspecialchars($user['userName']) . ",\n\n"
                     . "You have requested to reset your password for your Flashcards account.\n"
                     . "Please click on the following link to reset your password:\n"
                     . $resetLink . "\n\n"
                     . "This link will expire in 1 hour.\n"
                     . "If you did not request a password reset, please ignore this email.\n\n"
                     . "Regards,\n"
                     . "Flashcards Support Team";

            $headers = 'From: n.mahsouli@bithorizon.com' . "\r\n" .
                       'Reply-To: n.mahsouli@bithorizon.com' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();

            $mailSent = mail($user['email'], $subject, $message, $headers);


            if ($mailSent) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'If an account with that username or email exists, a password reset link has been sent.']), 200, array('HTTP/1.1 200 OK'));
            } else {
                error_log("Failed to send password reset email to " . $user['email']);
                $this->sendOutput(json_encode(['success' => true, 'message' => 'If an account with that username or email exists, a password reset link has been sent.']), 200, array('HTTP/1.1 200 OK'));
            }

        } catch (Exception $e) {
            error_log("Error in PasswordController::requestResetAction: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Server error during password reset request: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }

    public function verifyTokenAction($queryParams, $requestBody) 
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'GET') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405, array('HTTP/1.1 405 Method Not Allowed'));
            return;
        }

        try {
            $token = $queryParams['token'] ?? null;
            $email = $queryParams['email'] ?? null;

            if (empty($token) || empty($email)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Token and email are required for verification.']), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            $userModel = new UserModel();
            $user = $userModel->getUserByPasswordResetToken($token);

            if ($user && $user['email'] === $email) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Token is valid.']), 200, array('HTTP/1.1 200 OK'));
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Invalid or expired password reset link.']), 400, array('HTTP/1.1 400 Bad Request'));
            }

        } catch (Exception $e) {
            error_log("Error in PasswordController::verifyTokenAction: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Server error during token verification: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }


    public function resetByTokenAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405, array('HTTP/1.1 405 Method Not Allowed'));
            return;
        }

        try {
            $token = $requestBody['token'] ?? null;
            $email = $requestBody['email'] ?? null;
            $newPassword = $requestBody['newPassword'] ?? null;

            $errors = [];
            if (empty($token) || empty($email) || empty($newPassword)) {
                $errors['general'] = 'Token, email, and new password are required.';
            }
            if (!empty($newPassword) && strlen($newPassword) < 6) {
                $errors['newPassword'] = 'New password must be at least 6 characters long.';
            }

            if (!empty($errors)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            $userModel = new UserModel();

            $user = $userModel->getUserByPasswordResetToken($token);

            if (!$user || $user['email'] !== $email) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Invalid or expired password reset link. Please request a new one.']), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Start transaction
            $userModel->startTransaction();
            $passwordUpdated = $userModel->updatePassword($user['id'], $hashedNewPassword);
            $tokenCleared = $userModel->clearPasswordResetToken($user['id']);

            if ($passwordUpdated && $tokenCleared) {

            // Commits Transactions
                $userModel->commitTransaction();
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Your password has been reset successfully!']), 200, array('HTTP/1.1 200 OK'));
            } else {
                $userModel->rollBackTransaction();
                error_log("Password reset failed for user ID: " . $user['id'] . " - Password Updated: " . ($passwordUpdated ? 'Yes' : 'No') . ", Token Cleared: " . ($tokenCleared ? 'Yes' : 'No'));
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to reset password. Please try again or request a new link.']), 500, array('HTTP/1.1 500 Internal Server Error'));
            }

        } catch (Exception $e) {
            // rollback if transaction was started before exception
            if (isset($userModel) && $userModel->isInTransaction()) {
                $userModel->rollBackTransaction();
            }
            error_log("Error in PasswordController::resetAction: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Server error during password reset: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }
}