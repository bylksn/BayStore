-- ============================================
-- DATABASE: baystore_uas
-- APLIKASI: Bay Store - Toko Buku Online
-- ============================================

-- CREATE DATABASE
CREATE DATABASE IF NOT EXISTS baystore_uas;
USE baystore_uas;

-- ============================================
-- TABLE: BOOKS
-- ============================================
CREATE TABLE IF NOT EXISTS books (
    id VARCHAR(10) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    isbn VARCHAR(20),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE: MEMBERS
-- ============================================
CREATE TABLE IF NOT EXISTS members (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    membership_type ENUM('bronze', 'silver', 'gold') DEFAULT 'bronze',
    total_spent DECIMAL(15, 2) DEFAULT 0.00,
    join_date DATE NOT NULL,
    password VARCHAR(255) NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE: TRANSACTIONS
-- ============================================
CREATE TABLE IF NOT EXISTS transactions (
    id VARCHAR(15) PRIMARY KEY,
    member_id VARCHAR(10) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    discount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE: TRANSACTION_ITEMS
-- ============================================
CREATE TABLE IF NOT EXISTS transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(15) NOT NULL,
    book_id VARCHAR(10) NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- ============================================
-- INSERT DATA: BOOKS
-- ============================================
INSERT INTO books (id, title, author, category, price, stock, isbn, description) VALUES
('B001', 'Clean Code', 'Robert C. Martin', 'Pemrograman', 120000, 15, '978-0132350884', 'Panduan menulis kode yang bersih dan maintainable.'),
('B002', 'The Pragmatic Programmer', 'David Thomas', 'Pemrograman', 135000, 10, '978-0135957059', 'Dari journeyman menuju master.'),
('B003', 'Design Patterns', 'Gang of Four', 'Pemrograman', 145000, 8, '978-0201633610', 'Pola desain perangkat lunak klasik.'),
('B004', 'Belajar PHP OOP', 'Ahmad Fauzi', 'Pemrograman', 89000, 20, '978-6020000001', 'Panduan lengkap OOP dengan PHP.'),
('B005', 'Atomic Habits', 'James Clear', 'Pengembangan Diri', 99000, 25, '978-0735211292', 'Membangun kebiasaan kecil untuk hasil luar biasa.'),
('B006', 'Deep Work', 'Cal Newport', 'Pengembangan Diri', 95000, 18, '978-1455586691', 'Fokus mendalam di era gangguan digital.'),
('B007', 'Sapiens', 'Yuval Noah Harari', 'Sejarah', 115000, 12, '978-0062316097', 'Riwayat singkat umat manusia.'),
('B008', 'Homo Deus', 'Yuval Noah Harari', 'Sejarah', 119000, 9, '978-0062464316', 'Sejarah singkat hari esok.');

-- ============================================
-- INSERT DATA: MEMBERS
-- ============================================
INSERT INTO members (id, name, email, phone, membership_type, total_spent, join_date, password, role) VALUES
('A001', 'Administrator', 'admin@baystore.com', '000000000000', 'gold', 0.00, '2024-01-01', '$2y$10$tgiatHx2pspZ6.aLnnlYyeSiS71XxYL33914VeB3Qqg0eEi95c4fC', 'admin'),
('M001', 'Bayu Pratama', 'bayu@email.com', '081234567890', 'gold', 1500000.00, '2024-01-15', NULL, 'member'),
('M002', 'Siti Rahayu', 'siti@email.com', '082345678901', 'silver', 750000.00, '2024-02-20', NULL, 'member'),
('M003', 'Andi Wijaya', 'andi@email.com', '083456789012', 'bronze', 150000.00, '2024-03-10', NULL, 'member'),
('M004', 'Ratna Sari', 'ratna@email.com', '084567890123', 'bronze', 0.00, '2025-05-18', NULL, 'member'),
('M005', 'Dedy Gunawan', 'dedy@email.com', '085678901234', 'silver', 600000.00, '2024-04-05', NULL, 'member');

-- ============================================
-- INSERT DATA: TRANSACTIONS
-- ============================================
INSERT INTO transactions (id, member_id, subtotal, discount, total, status, created_at) VALUES
('TRX0001', 'M001', 255000, 25500, 229500, 'completed', '2024-05-01 10:30:00'),
('TRX0002', 'M002', 299000, 14950, 284050, 'completed', '2024-05-02 14:15:00'),
('TRX0003', 'M003', 120000, 0, 120000, 'completed', '2024-05-03 09:45:00'),
('TRX0004', 'M001', 189000, 18900, 170100, 'completed', '2024-05-05 16:20:00'),
('TRX0005', 'M004', 99000, 0, 99000, 'pending', '2025-05-18 11:00:00');

-- ============================================
-- INSERT DATA: TRANSACTION ITEMS
-- ============================================
INSERT INTO transaction_items (transaction_id, book_id, quantity, price_per_unit, subtotal) VALUES
('TRX0001', 'B001', 1, 120000, 120000),
('TRX0001', 'B005', 1, 99000, 99000),
('TRX0001', 'B002', 1, 135000, 135000),
('TRX0002', 'B003', 2, 145000, 290000),
('TRX0002', 'B004', 0, 89000, 9000),
('TRX0003', 'B001', 1, 120000, 120000),
('TRX0004', 'B006', 2, 95000, 190000),
('TRX0004', 'B007', 0, 115000, -1000),
('TRX0005', 'B005', 1, 99000, 99000);

-- ============================================
-- INDEXES (untuk performa query)
-- ============================================
CREATE INDEX idx_members_email ON members(email);
CREATE INDEX idx_members_membership ON members(membership_type);
CREATE INDEX idx_transactions_member ON transactions(member_id);
CREATE INDEX idx_transactions_status ON transactions(status);
CREATE INDEX idx_transactions_date ON transactions(created_at);
CREATE INDEX idx_transaction_items_book ON transaction_items(book_id);

-- ============================================
-- VIEWS (untuk laporan)
-- ============================================

-- View: Member dengan spending summary
CREATE OR REPLACE VIEW vw_member_summary AS
SELECT 
    m.id,
    m.name,
    m.email,
    m.membership_type,
    m.total_spent,
    COUNT(t.id) as total_transactions,
    SUM(CASE WHEN t.status = 'completed' THEN t.total ELSE 0 END) as completed_total
FROM members m
LEFT JOIN transactions t ON m.id = t.member_id
GROUP BY m.id, m.name, m.email, m.membership_type, m.total_spent;

-- View: Book stock dan sales summary
CREATE OR REPLACE VIEW vw_book_summary AS
SELECT 
    b.id,
    b.title,
    b.author,
    b.category,
    b.price,
    b.stock,
    COUNT(ti.id) as total_sold,
    SUM(ti.quantity) as total_quantity_sold,
    SUM(ti.subtotal) as total_revenue
FROM books b
LEFT JOIN transaction_items ti ON b.id = ti.book_id
GROUP BY b.id, b.title, b.author, b.category, b.price, b.stock;

-- View: Daily transaction report
CREATE OR REPLACE VIEW vw_daily_report AS
SELECT 
    DATE(t.created_at) as transaction_date,
    COUNT(t.id) as total_transactions,
    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN t.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN t.status = 'completed' THEN t.total ELSE 0 END) as total_revenue
FROM transactions t
GROUP BY DATE(t.created_at);

-- ============================================
-- SAMPLE QUERIES
-- ============================================

-- Get all members with their transaction count
-- SELECT * FROM vw_member_summary;

-- Get book sales summary
-- SELECT * FROM vw_book_summary;

-- Get daily revenue
-- SELECT * FROM vw_daily_report;

-- Get completed transactions with member details
-- SELECT t.id, m.name, m.email, t.total, t.created_at 
-- FROM transactions t 
-- JOIN members m ON t.member_id = m.id 
-- WHERE t.status = 'completed' 
-- ORDER BY t.created_at DESC;

-- Get member with most purchases
-- SELECT m.name, COUNT(t.id) as purchase_count, SUM(t.total) as total_spent
-- FROM members m
-- LEFT JOIN transactions t ON m.id = t.member_id
-- GROUP BY m.id
-- ORDER BY purchase_count DESC
-- LIMIT 5;
