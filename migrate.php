<?php
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tambahkan kolom
$conn->query("ALTER TABLE members ADD COLUMN password VARCHAR(255) NULL");
$conn->query("ALTER TABLE members ADD COLUMN role ENUM('admin', 'member') DEFAULT 'member'");

// Cek apakah admin sudah ada
$res = $conn->query("SELECT * FROM members WHERE email = 'admin@baystore.com'");
if ($res->num_rows == 0) {
    $hash = password_hash('admin', PASSWORD_DEFAULT);
    // Kita butuh ID untuk admin. Formatnya M... 
    // Ambil next ID. Atau hardcode 'ADMIN'
    $stmt = $conn->prepare("INSERT INTO members (id, name, email, phone, membership_type, total_spent, join_date, password, role) VALUES ('A001', 'Administrator', 'admin@baystore.com', '000', 'gold', 0, CURDATE(), ?, 'admin')");
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    echo "Admin created.\n";
} else {
    echo "Admin already exists.\n";
}

echo "Migration done.\n";
