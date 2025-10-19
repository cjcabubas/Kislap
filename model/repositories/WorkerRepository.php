<?php

class WorkerRepository

{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
    }

    public function findByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM worker WHERE email = ? LIMIT 1");

        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByphoneNumber($phoneNumber)
    {
        $stmt = $this->conn->prepare("SELECT * FROM worker WHERE phoneNumber = ? LIMIT 1");

        $stmt->execute([$phoneNumber]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}