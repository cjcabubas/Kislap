<?php

require_once __DIR__ . '/BaseRepository.php';

class AuthRepository extends BaseRepository
{
    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct()
    {
        parent::__construct();
    }

    // ========================================
    // USER REGISTRATION
    // ========================================
    
    public function signUp(array $user): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO User (lastName, firstName, middleName, email, phoneNumber, password, address) 
                    VALUES (:lastName, :firstName, :middleName, :email, :phoneNumber, :password, :address)"
        );

        $stmt->execute($user);
    }

    // ========================================
    // USER LOOKUP
    // ========================================
    
    public function findByEmailOrPhone($email = null, $phoneNumber = null)
    {
        $stmt = $this->conn->prepare("
        SELECT * FROM user
        WHERE email = :email OR phoneNumber = :phone
        LIMIT 1
    ");
        $stmt->execute([
            ':email' => $email,
            ':phone' => $phoneNumber
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");

        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByphoneNumber($phoneNumber)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE phoneNumber = ? LIMIT 1");

        $stmt->execute([$phoneNumber]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ========================================
    // PASSWORD CHANGE
    // ========================================
    
    public function verifyUserDetails($firstName, $lastName, $middleName, $email, $phoneNumber, $userType)
    {
        if ($userType === 'worker') {
            $stmt = $this->conn->prepare("
                SELECT worker_id as id, firstName, lastName, middleName, email, phoneNumber 
                FROM workers 
                WHERE firstName = ? AND lastName = ? AND email = ? AND phoneNumber = ?
                AND (middleName = ? OR (middleName IS NULL AND ? = ''))
                LIMIT 1
            ");
        } else {
            $stmt = $this->conn->prepare("
                SELECT user_id as id, firstName, lastName, middleName, email, phoneNumber 
                FROM user 
                WHERE firstName = ? AND lastName = ? AND email = ? AND phoneNumber = ?
                AND (middleName = ? OR (middleName IS NULL AND ? = ''))
                LIMIT 1
            ");
        }

        $stmt->execute([$firstName, $lastName, $email, $phoneNumber, $middleName, $middleName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($userId, $hashedPassword, $userType)
    {
        if ($userType === 'worker') {
            $stmt = $this->conn->prepare("UPDATE workers SET password = ? WHERE worker_id = ?");
        } else {
            $stmt = $this->conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
        }

        return $stmt->execute([$hashedPassword, $userId]);
    }

    // ========================================
    // SIMPLE SECURE PASSWORD RESET
    // ========================================
    
    public function verifyEmailAndPhone(string $email, string $phoneNumber, string $userType): ?array
    {
        try {
            if ($userType === 'worker') {
                $stmt = $this->conn->prepare("
                    SELECT worker_id as id, email, phoneNumber 
                    FROM workers 
                    WHERE LOWER(email) = LOWER(?) AND phoneNumber = ?
                ");
            } else {
                $stmt = $this->conn->prepare("
                    SELECT user_id as id, email, phoneNumber 
                    FROM user 
                    WHERE LOWER(email) = LOWER(?) AND phoneNumber = ?
                ");
            }
            
            $stmt->execute([$email, $phoneNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error verifying email and phone: " . $e->getMessage());
            return null;
        }
    }

}