<?php
require_once __DIR__ . '/../config.php';

/**
 * Class Database - Singleton untuk koneksi MySQLi
 */
class Database {
    private static ?Database $instance = null;
    private mysqli $conn;
    
    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection(): mysqli {
        return $this->conn;
    }
    
    // ─── BOOKS ───
    public function getBooks(): array {
        $result = $this->conn->query("SELECT * FROM books ORDER BY id");
        return $result->fetch_all(MYSQLI_ASSOC) ?? [];
    }
    
    public function getBook(string $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function addBook(Book $book): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO books (id, title, author, category, price, stock, isbn, description) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $id = $book->getBookId();
        $title = $book->getTitle();
        $author = $book->getAuthor();
        $category = $book->getCategory();
        $price = $book->getPrice();
        $stock = $book->getStock();
        $isbn = $book->getIsbn();
        $description = $book->getDescription();
        
        $stmt->bind_param("ssssdiss", $id, $title, $author, $category, $price, $stock, $isbn, $description);
        return $stmt->execute();
    }
    
    public function deleteBook(string $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
    
    public function reduceBookStock(string $id, int $qty): bool {
        $stmt = $this->conn->prepare("UPDATE books SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt->bind_param("isi", $qty, $id, $qty);
        return $stmt->execute();
    }
    
    public function generateBookId(): string {
        $result = $this->conn->query("SELECT COUNT(*) as cnt FROM books");
        $row = $result->fetch_assoc();
        $num = $row['cnt'] + 1;
        return 'B' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
    
    // ─── MEMBERS ───
    public function getMembers(): array {
        $result = $this->conn->query("SELECT * FROM members ORDER BY id");
        return $result->fetch_all(MYSQLI_ASSOC) ?? [];
    }
    
    public function getMember(string $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function addMember(Member $member): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO members (id, name, email, phone, membership_type, total_spent, join_date) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $id = $member->getMemberId();
        $name = $member->getName();
        $email = $member->getEmail();
        $phone = $member->getPhone();
        $membership = $member->getMembershipType();
        $spent = $member->getTotalSpent();
        $joinDate = $member->getJoinDate();
        
        $stmt->bind_param("sssssds", $id, $name, $email, $phone, $membership, $spent, $joinDate);
        return $stmt->execute();
    }
    
    public function updateMemberSpent(string $memberId, float $amount): bool {
        $stmt = $this->conn->prepare(
            "UPDATE members SET total_spent = total_spent + ? WHERE id = ?"
        );
        $stmt->bind_param("ds", $amount, $memberId);
        return $stmt->execute();
    }
    
    public function deleteMember(string $memberId): bool {
        $stmt = $this->conn->prepare("DELETE FROM members WHERE id = ?");
        $stmt->bind_param("s", $memberId);
        return $stmt->execute();
    }
    
    public function generateMemberId(): string {
        $result = $this->conn->query("SELECT COUNT(*) as cnt FROM members");
        $row = $result->fetch_assoc();
        $num = $row['cnt'] + 1;
        return 'M' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
    
    // ─── TRANSACTIONS ───
    public function getTransactions(): array {
        $result = $this->conn->query(
            "SELECT t.*, m.name as member_name 
             FROM transactions t 
             JOIN members m ON t.member_id = m.id 
             ORDER BY t.created_at DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC) ?? [];
    }
    
    public function getTransaction(string $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT t.*, m.name as member_name 
             FROM transactions t 
             JOIN members m ON t.member_id = m.id 
             WHERE t.id = ?"
        );
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function addTransaction(string $txId, string $memberId, float $subtotal, float $discount, float $total, string $status = 'pending'): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO transactions (id, member_id, subtotal, discount, total, status) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssddds", $txId, $memberId, $subtotal, $discount, $total, $status);
        return $stmt->execute();
    }
    
    public function addTransactionItem(string $txId, string $bookId, int $qty, float $pricePerUnit, float $subtotal): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO transaction_items (transaction_id, book_id, quantity, price_per_unit, subtotal) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssidd", $txId, $bookId, $qty, $pricePerUnit, $subtotal);
        return $stmt->execute();
    }
    
    public function updateTransactionStatus(string $txId, string $status): bool {
        $allowed = ['pending', 'completed', 'cancelled'];
        if (!in_array($status, $allowed)) return false;
        
        $stmt = $this->conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->bind_param("ss", $status, $txId);
        return $stmt->execute();
    }
    
    public function deleteTransaction(string $txId): bool {
        // Hapus items dulu (foreign key constraint)
        $stmt = $this->conn->prepare("DELETE FROM transaction_items WHERE transaction_id = ?");
        $stmt->bind_param("s", $txId);
        $stmt->execute();
        
        // Hapus transaksi
        $stmt2 = $this->conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt2->bind_param("s", $txId);
        return $stmt2->execute() && $stmt2->affected_rows > 0;
    }
    
    public function getTransactionItems(string $txId): array {
        $stmt = $this->conn->prepare(
            "SELECT ti.*, b.title as book_title 
             FROM transaction_items ti 
             JOIN books b ON ti.book_id = b.id 
             WHERE ti.transaction_id = ?"
        );
        $stmt->bind_param("s", $txId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?? [];
    }
    
    public function generateTransactionId(): string {
        $result = $this->conn->query("SELECT COUNT(*) as cnt FROM transactions");
        $row = $result->fetch_assoc();
        $num = $row['cnt'] + 1;
        return 'TRX' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
    
    // ─── STATS ───
    public function getStats(): array {
        $books = $this->conn->query("SELECT COUNT(*) as cnt FROM books")->fetch_assoc();
        $members = $this->conn->query("SELECT COUNT(*) as cnt FROM members")->fetch_assoc();
        $transactions = $this->conn->query("SELECT COUNT(*) as cnt FROM transactions")->fetch_assoc();
        $completed = $this->conn->query(
            "SELECT COUNT(*) as cnt, SUM(total) as revenue FROM transactions WHERE status = 'completed'"
        )->fetch_assoc();
        
        return [
            'total_books'        => $books['cnt'] ?? 0,
            'total_members'      => $members['cnt'] ?? 0,
            'total_transactions' => $transactions['cnt'] ?? 0,
            'completed_trx'      => $completed['cnt'] ?? 0,
            'total_revenue'      => $completed['revenue'] ?? 0,
        ];
    }
    
    public function close(): void {
        $this->conn->close();
    }
}
