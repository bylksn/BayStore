<?php
/**
 * Class Person - Base class (Parent)
 * Menerapkan: Class, Property, Method, Constructor, Encapsulation
 */
class Person {
    // Private properties (Encapsulation)
    private string $name;
    private string $email;
    private string $phone;

    // Constructor
    public function __construct(string $name, string $email, string $phone) {
        $this->name  = $name;
        $this->email = $email;
        $this->phone = $phone;
    }

    // Getter & Setter - name
    public function getName(): string {
        return $this->name;
    }
    public function setName(string $name): void {
        $this->name = trim($name);
    }

    // Getter & Setter - email
    public function getEmail(): string {
        return $this->email;
    }
    public function setEmail(string $email): void {
        $this->email = strtolower(trim($email));
    }

    // Getter & Setter - phone
    public function getPhone(): string {
        return $this->phone;
    }
    public function setPhone(string $phone): void {
        $this->phone = $phone;
    }

    // Method umum
    public function getInfo(): string {
        return "{$this->name} | {$this->email} | {$this->phone}";
    }
}
