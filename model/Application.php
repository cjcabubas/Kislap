<?php
class Application
{
    // ========================================
    // PROPERTIES
    // ========================================
    
    private ?int $application_id;
    private ?string $lastName;
    private ?string $firstName;
    private ?string $middleName;
    private ?string $email;
    private ?string $phoneNumber;
    private ?string $address;
    private ?string $password;
    private ?string $status;
    private ?DateTime $createdAt;

    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct(?string $lastName, ?string $firstName, ?string $middleName, ?string $email, ?string $phoneNumber, ?string $address, ?string $password) {
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->address = $address;
        $this->password = $password;
    }

    // ========================================
    // GETTERS
    // ========================================
    
    public function getApplicationId(): ?int {return $this->application_id; }
    public function getLastName(): ?string {return $this->lastName; }
    public function getFirstName(): ?string {return $this->firstName; }
    public function getMiddleName(): ?string {return $this->middleName; }
    public function getEmail(): ?string {return $this->email; }
    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function getAddress(): ?string { return $this->address; }
    public function getPassword(): ?string { return $this->password; }
    public function getStatus(): ?string {return $this->status; }

    // ========================================
    // UTILITY METHODS
    // ========================================
    
    public function toArray(): array {
        return [
            'lastName' => $this->lastName,
            'firstName' => $this->firstName,
            'middleName' => $this->middleName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'address' => $this->address,
            'password' => $this->password,
        ];
    }
}
