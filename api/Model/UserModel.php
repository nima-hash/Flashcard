<?php
require_once __DIR__ . "/Database.php";
class UserModel extends Database
{
    public function getUsers($limit)
    {
        return $this->select("SELECT * FROM Users ORDER BY user_id ASC LIMIT ?", ["i", $limit]);
    }

    
    public function registerUser($userData)
    {       
        
        return $this->post("INSERT INTO Users (userName, pass, email, phone, user_id, adress) VALUES (? ,? ,? ,? ,? ,?)", $userData);
    
    }


      public function getUserById(string $userId): ?array
    {
        try {
            $user = $this->select("SELECT * FROM users WHERE id = ?", [$userId]);
            return $user[0] ?? null;
        } catch (PDOException $e) {
            error_log("Error in UserModel::getUserById: " . $e->getMessage());
            throw new Exception("Database error fetching user: " . $e->getMessage());
        }
    }


    //  Updates user's personal information.

    public function updateUser($vars): bool
    {
        try {
   
            return $this->update("UPDATE users SET email=?, address = ?, birthday = ?, name = ?, lastName = ?, phone = ? WHERE id = ?", $vars);
        } catch (PDOException $e) {
            error_log("Error in UserModel::updateUser: " . $e->getMessage());
            throw new Exception("Database error updating user: " . $e->getMessage());
        }
    }


    //  Updates user's profile picture URL.

    public function updateProfilePicture($vars): bool
    {
        try {

            return $this->update($sql = "UPDATE users SET profile_picture_url = ? WHERE id = ?", $vars);
        
        } catch (PDOException $e) {
            error_log("Error in UserModel::updateProfilePicture: " . $e->getMessage());
            throw new Exception("Database error updating profile picture: " . $e->getMessage());
        }
    }

    // Verifies a user's password against the stored hash.

    public function verifyPassword(string $userId, string $password): bool
    {
        try {
            $sql = "SELECT password_hash FROM users WHERE id = ?";
            $user = $this->select($sql, [$userId]);
            
            if (empty($user) || !isset($user[0]['password_hash'])) {
                return false;
            }
            
            return password_verify($password, $user[0]['password_hash']);
        } catch (PDOException $e) {
            error_log("Error in UserModel::verifyPassword: " . $e->getMessage());
            throw new Exception("Database error during password verification: " . $e->getMessage());
        }
    }

    //  Updates a user's password.

    public function updatePassword(string $userId, string $hashedNewPassword): bool
    {
        try {
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $params = [$hashedNewPassword, $userId];
            return $this->update($sql, $params);
        } catch (PDOException $e) {
            error_log("Error in UserModel::updatePassword: " . $e->getMessage());
            throw new Exception("Database error updating password: " . $e->getMessage());
        }
    }

    public function createUser($params): string
    {
        try {
            $sql = "INSERT INTO Users (userName, email, password_hash, name, lastName, phone, address, birthday) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->post($sql, $params); 
            return $this->insertedId();
        } catch (PDOException $e) {
            error_log("Error in UserModel::createUser: " . $e->getMessage());
            throw new Exception("Database error creating user: " . $e->getMessage());
        }
    }

    public function getUserByUsername($userName)
    {
        return $this->select("SELECT * FROM Users WHERE userName = ?", [$userName]);
    }

    public function getUserByEmail($email)
    {
        return $this->select("SELECT * FROM Users WHERE email = ?", [$email]);
    }

    public function getProfileImageUrl($vars)
    {
        return $this->select("SELECT profile_picture_url FROM Users WHERE id = ?", $vars);
    }

    public function deleteOldProfileImage($fileUrl) {
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $fileUrl;
        if (file_exists($absolutePath) && is_file($absolutePath) && strpos($fileUrl, '/media/profile_Img/') === 0) {
            return unlink($absolutePath);
        }
        return true; 

    }

    public function savePasswordResetToken($params): bool
    {
        try {

            return $this->post("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)", $params) > 0;
        } catch (PDOException $e) {
            error_log("Error in UserModel::savePasswordResetToken: " . $e->getMessage());
            throw new Exception("Database error saving password reset token: " . $e->getMessage());
        }
    }

      public function getUserByPasswordResetToken(string $token): ?array {
        try {
            $sql = "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()";
            $result = $this->select($sql, [$token]);
            return $result[0] ?? null;
        } catch (PDOException $e) {
            error_log("Error in UserModel::getUserByPasswordResetToken: " . $e->getMessage());
            throw new Exception("Database error retrieving user by reset token: " . $e->getMessage());
        }
    }

    public function clearPasswordResetToken(string $userId): bool {
        try {
            $sql = "UPDATE password_resets SET token = NULL, expires_at = NULL WHERE user_id = ?";
            $params = [$userId];
            return $this->post($sql, $params) > 0;
        } catch (PDOException $e) {
            error_log("Error in UserModel::clearPasswordResetToken: " . $e->getMessage());
            throw new Exception("Database error clearing password reset token: " . $e->getMessage());
        }
    }

}