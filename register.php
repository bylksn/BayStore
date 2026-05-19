<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/DataStore.php';
require_once 'classes/Person.php';
require_once 'classes/Member.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $store = DataStore::getInstance();
    
    // Validasi email
    $db = Database::getInstance();
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $memberId = $store->generateMemberId();
        // Create new member object
        $member = new Member($name, $email, $phone, $memberId, 'bronze', 0, date('Y-m-d'), 'member');
        
        if ($store->registerMember($member, $password)) {
            $success = "Pendaftaran berhasil! Silakan login.";
        } else {
            $error = "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BayStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #f093fb;
            --bg: #0f172a;
            --surface: #1e293b;
            --text: #f1f5f9;
            --border: #334155;
            --error: #ef4444;
            --success: #10b981;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .login-card {
            background: var(--surface);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--border);
        }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo i { font-size: 2.5rem; color: var(--primary); }
        .logo h1 { margin: 10px 0 0; font-size: 1.8rem; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-size: 0.9rem; color: #94a3b8; }
        input {
            width: 100%; padding: 12px; border-radius: 8px;
            border: 1px solid var(--border); background: #0f172a;
            color: white; box-sizing: border-box; outline: none;
        }
        input:focus { border-color: var(--primary); }
        .btn {
            width: 100%; padding: 12px; border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; font-weight: bold; border: none;
            cursor: pointer; font-size: 1rem; margin-top: 10px;
        }
        .error { color: var(--error); font-size: 0.9rem; text-align: center; margin-bottom: 15px; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 8px;}
        .success { color: var(--success); font-size: 0.9rem; text-align: center; margin-bottom: 15px; background: rgba(16, 185, 129, 0.1); padding: 10px; border-radius: 8px;}
        .links { text-align: center; margin-top: 20px; font-size: 0.9rem; }
        .links a { color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="fa-solid fa-user-plus"></i>
            <h1>Daftar Member</h1>
        </div>
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Daftar Sekarang</button>
        </form>
        <div class="links">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>
</body>
</html>
