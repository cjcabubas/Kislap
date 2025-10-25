<?php

class UserRepository
{
    private PDO $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
    }

    public function updateUser(int $userId, array $user): bool
    {
        $sql = "UPDATE user 
                SET firstName = :firstName,
                    middleName = :middleName,
                    lastName = :lastName,
                    phoneNumber = :phoneNumber,
                    address = :address,
                    profilePhotoUrl = :profilePhotoUrl
                WHERE user_id = :userId";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':firstName' => $user['firstName'],
            ':middleName' => $user['middleName'],
            ':lastName' => $user['lastName'],
            ':phoneNumber' => $user['phoneNumber'],
            ':address' => $user['address'],
            ':profilePhotoUrl' => $user['profilePhotoUrl'] ?? null,
            ':userId' => $userId
        ]);
    }

    public function getUserById(int $userId): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error fetching user by ID: " . $e->getMessage());
            return null;
        }
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            return null;
        }
    }

    public function findByPhoneNumber(string $phoneNumber): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE phoneNumber = ?");
            $stmt->execute([$phoneNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error finding user by phone: " . $e->getMessage());
            return null;
        }
    }
}