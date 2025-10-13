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
}