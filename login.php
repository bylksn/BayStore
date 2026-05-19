<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/DataStore.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $store = DataStore::getInstance();
    $user = $store->login($email, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BayStore</title>
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
        }
        .login-card {
            background: var(--surface);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i { font-size: 2.5rem; color: var(--primary); }
        .logo h1 { margin: 10px 0 0; font-size: 1.8rem; }
        .form-group { margin-bottom: 20px; }
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
        .error { color: var(--error); font-size: 0.9rem; text-align: center; margin-bottom: 15px; }
        .links { text-align: center; margin-top: 20px; font-size: 0.9rem; }
        .links a { color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="fa-solid fa-book-open"></i>
            <h1>BayStore</h1>
        </div>
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="links">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</body>
</html>
