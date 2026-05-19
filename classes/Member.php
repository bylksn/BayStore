<?php
require_once 'Person.php';

/**
 * Class Member - Child class dari Person
 * Menerapkan: Inheritance, Constructor override
 */
class Member extends Person {
    // Private properties tambahan
    private string $memberId;
    private string $membershipType; // bronze, silver, gold
    private float  $totalSpent;
    private string $joinDate;
    private string $role;

    public function __construct(
        string $name,
        string $email,
        string $phone,
        string $memberId,
        string $membershipType = 'bronze',
        float $totalSpent = 0.0,
        string $joinDate = '',
        string $role = 'member'
    ) {
        // Memanggil constructor parent (Person)
        parent::__construct($name, $email, $phone);
        $this->memberId        = $memberId;
        $this->membershipType  = $membershipType;
        $this->totalSpent      = $totalSpent;
        $this->joinDate        = $joinDate ?: date('Y-m-d');
        $this->role            = $role;
    }

    // Getters & Setters
    public function getMemberId(): string       { return $this->memberId; }
    public function getMembershipType(): string { return $this->membershipType; }
    public function getTotalSpent(): float      { return $this->totalSpent; }
    public function getJoinDate(): string       { return $this->joinDate; }
    public function getRole(): string           { return $this->role; }

    public function setMembershipType(string $type): void {
        $allowed = ['bronze', 'silver', 'gold'];
        if (in_array($type, $allowed)) {
            $this->membershipType = $type;
        }
    }

    public function addSpent(float $amount): void {
        $this->totalSpent += $amount;
        $this->upgradeMembership();
    }

    // Method khusus Member
    public function getDiscount(): float {
        return match($this->membershipType) {
            'silver' => 0.05,
            'gold'   => 0.10,
            default  => 0.00,
        };
    }

    private function upgradeMembership(): void {
        if ($this->totalSpent >= 1000000) {
            $this->membershipType = 'gold';
        } elseif ($this->totalSpent >= 500000) {
            $this->membershipType = 'silver';
        }
    }

    public function getInfo(): string {
        return parent::getInfo() . " | Member: {$this->memberId} ({$this->membershipType})";
    }

    public function toArray(): array {
        return [
            'id'         => $this->memberId,
            'name'       => $this->getName(),
            'email'      => $this->getEmail(),
            'phone'      => $this->getPhone(),
            'membership' => $this->membershipType,
            'spent'      => $this->totalSpent,
            'joined'     => $this->joinDate,
            'discount'   => $this->getDiscount() * 100 . '%',
        ];
    }
}
