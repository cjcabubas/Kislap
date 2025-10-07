<?php
class ApplicationRepository
{
    private PDO $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
    }

    public function save(array $application): string {
        $stmt =$this->conn->prepare(
            "INSERT INTO application (lastName, firstName, middleName, email, phoneNumber, password, address) 
                    VALUES (:lastName, :firstName, :middleName, :email, :phoneNumber, :password, :address)"
        );

        $stmt->execute($application);
        return $this->conn->lastInsertId();
    }

    public function saveResume(int $application_id, string $filePath): void {
        $stmt =$this->conn->prepare(
            "INSERT INTO application_resume (application_id, resumeFilePath) 
                    VALUES (:id, :path)"
        );
        $stmt->execute(["id"=>$application_id, "path"=>$filePath]);
    }

    public function saveWorks(int $application_id, string $filePath): void {
        $stmt =$this->conn->prepare(
            "INSERT INTO application_resume (application_id, worksFilePath) 
                    VALUES (:id, :path)"
        );
        $stmt->execute(["id"=>$application_id, "path"=>$filePath]);
    }
}