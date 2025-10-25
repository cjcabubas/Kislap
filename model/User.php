<?php
class User
{
    // ========================================
    // PROPERTIES
    // ========================================
    
    private ?int $user_id = null;
    private string $lastName;
    private string $firstName;
    private string $middleName;
    private string $email;
    private string $phoneNumber;
    private string $password;
    private string $address;
    private ?string $profilePhotoUrl = null;
    private string $createdAt;

    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct(
        string $lastName,
        string $firstName,
        string $middleName,
        string $email,
        string $phoneNumber,
        string $password,
        string $address
    ) {
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->password = $password;
        $this->address = $address;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    // ========================================
    // GETTERS
    // ========================================
    
    public function getId(): ?int { return $this->user_id; }
    public function getLastName(): string { return $this->lastName; }
    public function getFirstName(): string { return $this->firstName; }
    public function getMiddleName(): string { return $this->middleName; }
    public function getEmail(): string { return $this->email; }
    public function getPhoneNumber(): string { return $this->phoneNumber; }
    public function getPassword(): string { return $this->password; }
    public function getAddress(): string { return $this->address; }
    public function getProfilePhotoUrl(): ?string { return $this->profilePhotoUrl; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getStatus(): string { return $this->status; }

    // ========================================
    // SETTERS
    // ========================================
    
    public function setId(int $id): void { $this->user_id = $id; }
    public function setProfilePhotoUrl(?string $url): void { $this->profilePhotoUrl = $url; }

    // ========================================
    // UTILITY METHODS
    // ========================================
    
    public function toArray(): array
    {
        return [
            'lastName' => $this->lastName,
            'firstName' => $this->firstName,
            'middleName' => $this->middleName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'password' => $this->password,
            'address' => $this->address
        ];
    }
}
