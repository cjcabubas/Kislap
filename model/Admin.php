<?php
class Admin
{
    // ========================================
    // PROPERTIES
    // ========================================
    
    private ?int $admin_id = null;
    private string $username;
    private string $password;
    private string $firstName;
    private string $middleName;
    private string $lastName;
    private string $created_at;

    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct(
        string $username,
        string $password,
        string $firstName,
        string $middleName,
        string $lastName
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->created_at = date('Y-m-d H:i:s');
    }

    // ========================================
    // GETTERS
    // ========================================
    
    public function getId(): ?int { return $this->admin_id; }
    public function getUsername(): string { return $this->username; }
    public function getPassword(): string { return $this->password; }
    public function getFirstName(): string { return $this->firstName; }
    public function getMiddleName(): string { return $this->middleName; }
    public function getLastName(): string { return $this->lastName; }
    public function getCreatedAt(): string { return $this->created_at; }

    // ========================================
    // SETTERS
    // ========================================
    
    public function setId(int $id): void { $this->admin_id = $id; }
    public function setPassword(string $password): void { $this->password = $password; }

    // ========================================
    // UTILITY METHODS
    // ========================================
    
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
            'firstName' => $this->firstName,
            'middleName' => $this->middleName,
            'lastName' => $this->lastName
        ];
    }
}
