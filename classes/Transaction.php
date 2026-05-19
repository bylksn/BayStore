<?php
require_once 'Book.php';
require_once 'Member.php';

/**
 * Class Transaction
 * Menerapkan: Class, Object composition, Property, Method, Constructor, Encapsulation
 */
class Transaction {
    private string  $transactionId;
    private Member  $member;
    private array   $items;       // [['book' => Book, 'qty' => int]]
    private float   $subtotal;
    private float   $discount;
    private float   $total;
    private string  $status;      // pending, completed, cancelled
    private string  $createdAt;

    public function __construct(string $transactionId, Member $member) {
        $this->transactionId = $transactionId;
        $this->member        = $member;
        $this->items         = [];
        $this->subtotal      = 0.0;
        $this->discount      = 0.0;
        $this->total         = 0.0;
        $this->status        = 'pending';
        $this->createdAt     = date('Y-m-d H:i:s');
    }

    // Getters
    public function getTransactionId(): string { return $this->transactionId; }
    public function getMember(): Member        { return $this->member; }
    public function getItems(): array          { return $this->items; }
    public function getSubtotal(): float       { return $this->subtotal; }
    public function getDiscount(): float       { return $this->discount; }
    public function getTotal(): float          { return $this->total; }
    public function getStatus(): string        { return $this->status; }
    public function getCreatedAt(): string     { return $this->createdAt; }

    // Setters
    public function setStatus(string $status): void {
        $allowed = ['pending', 'completed', 'cancelled'];
        if (in_array($status, $allowed)) {
            $this->status = $status;
        }
    }

    public function setCreatedAt(string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    // Methods
    public function addItem(Book $book, int $qty): bool {
        if (!$book->isAvailable() || $book->getStock() < $qty) {
            return false;
        }
        $this->items[] = ['book' => $book, 'qty' => $qty];
        $this->recalculate();
        return true;
    }

    /**
     * Tambah item tanpa cek stok & tanpa kurangi stok (untuk load dari DB)
     */
    public function addItemRaw(Book $book, int $qty): void {
        $this->items[] = ['book' => $book, 'qty' => $qty];
        $this->recalculate();
    }

    private function recalculate(): void {
        $this->subtotal = 0.0;
        foreach ($this->items as $item) {
            $this->subtotal += $item['book']->getPrice() * $item['qty'];
        }
        $discountRate    = $this->member->getDiscount();
        $this->discount  = $this->subtotal * $discountRate;
        $this->total     = $this->subtotal - $this->discount;
    }

    public function complete(): bool {
        if ($this->status !== 'pending' || empty($this->items)) return false;
        foreach ($this->items as $item) {
            $item['book']->reduceStock($item['qty']);
        }
        $this->member->addSpent($this->total);
        $this->status = 'completed';
        return true;
    }

    public function getFormattedTotal(): string {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function toArray(): array {
        $itemsArr = [];
        foreach ($this->items as $item) {
            $itemsArr[] = [
                'book'  => $item['book']->getTitle(),
                'price' => $item['book']->getFormattedPrice(),
                'qty'   => $item['qty'],
                'total' => 'Rp ' . number_format($item['book']->getPrice() * $item['qty'], 0, ',', '.'),
            ];
        }
        return [
            'id'       => $this->transactionId,
            'member'   => $this->member->getName(),
            'items'    => $itemsArr,
            'subtotal' => 'Rp ' . number_format($this->subtotal, 0, ',', '.'),
            'discount' => 'Rp ' . number_format($this->discount, 0, ',', '.'),
            'total'    => $this->getFormattedTotal(),
            'status'   => $this->status,
            'date'     => $this->createdAt,
        ];
    }
}
