<?php
class ApplicationRepository
{
    private PDO $conn;

    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
    }

    // ========================================
    // APPLICATION MANAGEMENT
    // ========================================
    
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
            "INSERT INTO application_works (application_id, worksFilePath) 
                    VALUES (:id, :path)"
        );
        $stmt->execute(["id"=>$application_id, "path"=>$filePath]);
    }

    // ========================================
    // APPLICATION LOOKUP
    // ========================================
    
    public function findByEmailAndIdentifier($email, $identifier): ?array
    {
        $query = "SELECT status, firstName, lastName, email, phoneNumber, 
                     application_id, created_at
              FROM application
              WHERE email = ?
              AND (phoneNumber = ? OR application_id = ?) 
              LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email, $identifier, $identifier]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function findByEmailOrPhone($email, $phoneNumber)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM application
            WHERE email = :email OR phoneNumber = :phone
            LIMIT 1
        ");
        $stmt->execute([
            ':email' => $email,
            ':phone' => $phoneNumber
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


}