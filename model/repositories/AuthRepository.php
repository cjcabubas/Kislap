<?php

class AuthRepository
{
    private PDO $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
    }

    public function signUp(array $user): void {
        $stmt =$this->conn->prepare(
            "INSERT INTO User (lastName, firstName, middleName, email, phoneNumber, password, address) 
                    VALUES (:lastName, :firstName, :middleName, :email, :phoneNumber, :password, :address)"
        );

        $stmt->execute($user);
    }

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

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");

        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByphoneNumber($phoneNumber) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE phoneNumber = ? LIMIT 1");

        $stmt->execute([$phoneNumber]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}