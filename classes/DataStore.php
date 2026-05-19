<?php
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/Member.php';
require_once __DIR__ . '/Transaction.php';
require_once __DIR__ . '/Database.php';

/**
 * Class DataStore - Hybrid storage: Database + Cache (In-memory)
 */
class DataStore {
    private static ?DataStore $instance = null;
    private Database $db;
    private array $books        = [];
    private array $members      = [];
    private array $transactions = [];

    private function __construct() {
        $this->db = Database::getInstance();
        $this->loadFromDatabase();
    }

    public static function getInstance(): DataStore {
        if (self::$instance === null) {
            self::$instance = new DataStore();
        }
        return self::$instance;
    }

    private function loadFromDatabase(): void {
        // Load Books
        $booksData = $this->db->getBooks();
        foreach ($booksData as $b) {
            $book = new Book($b['id'], $b['title'], $b['author'], $b['category'], 
                            $b['price'], $b['stock'], $b['isbn'], $b['description']);
            $this->books[$b['id']] = $book;
        }

        // Load Members
        $membersData = $this->db->getMembers();
        foreach ($membersData as $m) {
            $member = new Member(
                $m['name'], 
                $m['email'], 
                $m['phone'], 
                $m['id'], 
                $m['membership_type'], 
                (float)$m['total_spent'], 
                $m['join_date']
            );
            $this->members[$m['id']] = $member;
        }

        // Load Transactions
        $transactionsData = $this->db->getTransactions();
        foreach ($transactionsData as $t) {
            if ($this->members[$t['member_id']] ?? null) {
                $tx = new Transaction($t['id'], $this->members[$t['member_id']]);
                // Load transaction items dari DB
                $items = $this->db->getTransactionItems($t['id']);
                foreach ($items as $item) {
                    if (isset($this->books[$item['book_id']])) {
                        // Buat item manual tanpa mengurangi stok
                        $tx->addItemRaw($this->books[$item['book_id']], $item['quantity']);
                    }
                }
                // Set status dan tanggal dari DB
                $tx->setStatus($t['status']);
                $tx->setCreatedAt($t['created_at']);
                $this->transactions[$t['id']] = $tx;
            }
        }
    }

    // --- BOOKS ---
    public function getBooks(): array          { return $this->books; }
    public function getBook(string $id): ?Book { return $this->books[$id] ?? null; }

    public function addBook(Book $book): bool {
        if ($this->db->addBook($book)) {
            $this->books[$book->getBookId()] = $book;
            return true;
        }
        return false;
    }

    public function deleteBook(string $id): bool {
        if ($this->db->deleteBook($id)) {
            unset($this->books[$id]);
            return true;
        }
        return false;
    }

    public function generateBookId(): string {
        return $this->db->generateBookId();
    }

    // --- MEMBERS ---
    public function getMembers(): array              { return $this->members; }
    public function getMember(string $id): ?Member  { return $this->members[$id] ?? null; }

    public function addMember(Member $member): bool {
        if ($this->db->addMember($member)) {
            $this->members[$member->getMemberId()] = $member;
            return true;
        }
        return false;
    }

    public function deleteMember(string $id): bool {
        if ($this->db->deleteMember($id)) {
            unset($this->members[$id]);
            return true;
        }
        return false;
    }

    public function generateMemberId(): string {
        return $this->db->generateMemberId();
    }

    // --- TRANSACTIONS ---
    public function getTransactions(): array                     { return $this->transactions; }
    public function getTransaction(string $id): ?Transaction     { return $this->transactions[$id] ?? null; }

    public function addTransaction(Transaction $tx): bool {
        $txId = $tx->getTransactionId();
        $memberId = $tx->getMember()->getMemberId();
        $subtotal = $tx->getSubtotal();
        $discount = $tx->getDiscount();
        $total = $tx->getTotal();
        $status = $tx->getStatus(); // akan 'pending'

        if ($this->db->addTransaction($txId, $memberId, $subtotal, $discount, $total, $status)) {
            foreach ($tx->getItems() as $item) {
                $this->db->addTransactionItem($txId, $item['book']->getBookId(), $item['qty'], 
                                             $item['book']->getPrice(), 
                                             $item['book']->getPrice() * $item['qty']);
                // Kurangi stok buku di DB
                $this->db->reduceBookStock($item['book']->getBookId(), $item['qty']);
            }
            $this->transactions[$txId] = $tx;
            return true;
        }
        return false;
    }

    public function generateTransactionId(): string {
        return $this->db->generateTransactionId();
    }
    
    public function confirmTransaction(string $txId): bool {
        if ($this->db->updateTransactionStatus($txId, 'completed')) {
            // Update member total spent
            $tx = $this->transactions[$txId] ?? null;
            if ($tx) {
                $this->db->updateMemberSpent($tx->getMember()->getMemberId(), $tx->getTotal());
            }
            return true;
        }
        return false;
    }
    
    public function cancelTransaction(string $txId): bool {
        if ($this->db->updateTransactionStatus($txId, 'cancelled')) {
            return true;
        }
        return false;
    }
    
    public function deleteTransaction(string $txId): bool {
        if ($this->db->deleteTransaction($txId)) {
            unset($this->transactions[$txId]);
            return true;
        }
        return false;
    }

    // Stats
    public function getStats(): array {
        return $this->db->getStats();
    }
}
