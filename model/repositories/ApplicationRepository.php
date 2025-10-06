<?php
class ApplicationRepository
{
    private PDO $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
    }

    public function save(array $application): void {
        $stmt =$this->conn->prepare(
            "INSERT INTO application (lastName, firstName, middleName, email, phoneNumber, password, address) 
                    VALUES (:lastName, :firstName, :middleName, :email, :phoneNumber, :password, :address)"
        );

        $stmt->execute($application);
    }
}