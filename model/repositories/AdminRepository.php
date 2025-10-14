<?php
class AdminRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Create a new admin
    public function signUp(array $admin): void
    {
        $admin['password'] = password_hash($admin['password'], PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            INSERT INTO admin (username, lastName, firstName, middleName, password)
            VALUES (:username, :lastName, :firstName, :middleName, :password)
        ");
        $stmt->execute($admin);
    }

    // Find admin by username
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $admin['password'] = trim($admin['password']); // remove hidden chars
        }
        return $admin ?: null;

    }
}
