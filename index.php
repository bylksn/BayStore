<?php
ob_start();
session_start();
require_once __DIR__ . '/classes/DataStore.php';

$store = DataStore::getInstance();

$jsRedirect = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_book') {
        $id   = $store->generateBookId();
        $book = new Book(
            $id,
            trim($_POST['title']        ?? ''),
            trim($_POST['author']       ?? ''),
            trim($_POST['category']     ?? 'Umum'),
            (float)($_POST['price']     ?? 0),
            (int)  ($_POST['stock']     ?? 0),
            trim($_POST['isbn']         ?? ''),
            trim($_POST['description']  ?? '')
        );
        if ($store->addBook($book)) {
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Buku \"{$book->getTitle()}\" berhasil ditambahkan!"];
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal menambahkan buku."];
        }
        $jsRedirect = '?page=books';
    }

    elseif ($action === 'add_member') {
        $id     = $store->generateMemberId();
        $member = new Member(
            trim($_POST['name']  ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
            $id, 'bronze'
        );
        if ($store->addMember($member)) {
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Member \"{$member->getName()}\" berhasil ditambahkan!"];
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal menambahkan member (mungkin email sudah terdaftar)."];
        }
        $jsRedirect = '?page=members';
    }

    elseif ($action === 'add_transaction') {
        $memberId = $_POST['member_id'] ?? '';
        $member   = $store->getMember($memberId);
        if ($member) {
            $txId    = $store->generateTransactionId();
            $tx      = new Transaction($txId, $member);
            $bookIds = $_POST['book_ids'] ?? [];
            $qtys    = $_POST['qtys']     ?? [];
            $added   = 0;
            foreach ($bookIds as $i => $bId) {
                if (empty($bId)) continue;
                $book = $store->getBook($bId);
                if ($book) {
                    $qty = max(1, (int)($qtys[$i] ?? 1));
                    if ($tx->addItem($book, $qty)) $added++;
                }
            }
            // Simpan langsung tanpa memanggil complete() — status tetap 'pending'
            if ($added > 0) {
                if ($store->addTransaction($tx)) {
                    $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Transaksi {$txId} berhasil dibuat! Total: {$tx->getFormattedTotal()}. Silakan konfirmasi untuk menyelesaikan."];
                } else {
                    $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal menyimpan transaksi."];
                }
            } else {
                $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal: pilih buku dan pastikan stok tersedia."];
            }
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Pilih member terlebih dahulu."];
        }
        $jsRedirect = '?page=transactions';
    }

    elseif ($action === 'delete_book') {
        if ($store->deleteBook($_POST['book_id'] ?? '')) {
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Buku berhasil dihapus."];
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal menghapus buku."];
        }
        $jsRedirect = '?page=books';
    }

    elseif ($action === 'delete_member') {
        if ($store->deleteMember($_POST['member_id'] ?? '')) {
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Member berhasil dihapus."];
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal menghapus member."];
        }
        $jsRedirect = '?page=members';
    }
    
    elseif ($action === 'confirm_transaction') {
        $txId = $_POST['transaction_id'] ?? '';
        if ($store->confirmTransaction($txId)) {
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Transaksi {$txId} berhasil dikonfirmasi!"];
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal mengkonfirmasi transaksi."];
        }
        $jsRedirect = '?page=transactions';
    }
    
    elseif ($action === 'cancel_transaction') {
        $txId = $_POST['transaction_id'] ?? '';
        if ($store->cancelTransaction($txId)) {
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Transaksi {$txId} berhasil dibatalkan."];
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal membatalkan transaksi."];
        }
        $jsRedirect = '?page=transactions';
    }

    elseif ($action === 'delete_transaction') {
        $txId = $_POST['transaction_id'] ?? '';
        if ($store->deleteTransaction($txId)) {
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>"✓ Transaksi {$txId} berhasil dihapus."];
        } else {
            $_SESSION['flash'] = ['type'=>'error', 'msg'=>"✗ Gagal menghapus transaksi."];
        }
        $jsRedirect = '?page=transactions';
    }
}


$page  = $_GET['page'] ?? 'dashboard';
$stats = $store->getStats();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BayStore - Toko Buku Modern</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php if ($jsRedirect): ?>
    <meta http-equiv="refresh" content="0;url=<?= htmlspecialchars($jsRedirect) ?>">
    <?php endif; ?>
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #f093fb;
            --accent: #fbbf24;
            --accent-dark: #f59e0b;
            --bg: #0f172a;
            --bg-secondary: #1e293b;
            --surface: #1a2744;
            --surface-light: #293548;
            --border: #334155;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            line-height: 1.6;
        }

        /* ─ SIDEBAR ─ */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            overflow-y: auto;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        .logo {
            padding: 28px 24px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(240, 147, 251, 0.1));
        }

        .logo h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo p {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        nav {
            padding: 20px 12px;
            flex: 1;
        }

        .nav-section {
            font-size: 0.65rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 12px 16px 6px;
            margin-top: 12px;
            font-weight: 700;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            margin-bottom: 4px;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--text);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(240, 147, 251, 0.1));
            color: var(--primary);
            border-left: 3px solid var(--primary);
            padding-left: 13px;
        }

        .nav-item span {
            font-size: 1.1rem;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
            background: rgba(102, 126, 234, 0.05);
        }

        .sidebar-footer em {
            color: var(--primary);
            font-style: normal;
            font-weight: 700;
        }

        /* ─ MAIN CONTENT ─ */
        .main {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
            background: linear-gradient(135deg, var(--bg) 0%, var(--bg-secondary) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .page-header {
            margin-bottom: 32px;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header h2 {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* ─ STATS GRID ─ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-card:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
            transform: translateY(-5px);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            top: -50px;
            right: -50px;
            background: radial-gradient(circle, var(--primary), transparent);
            opacity: 0.1;
            filter: blur(40px);
        }

        .stat-card .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 8px 0 4px;
            color: var(--text);
        }

        .stat-card .sub {
            font-size: 0.8rem;
            color: var(--text-dim);
        }

        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.15;
            margin-bottom: 8px;
        }

        /* ─ CARDS ─ */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.1);
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(102, 126, 234, 0.05);
        }

        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
        }

        .card-body {
            padding: 0;
            overflow-x: auto;
        }

        /* ─ TABLES ─ */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 14px 20px;
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
            background: rgba(102, 126, 234, 0.05);
            font-weight: 700;
        }

        td {
            padding: 14px 20px;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        /* ─ BADGES ─ */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-gold {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(251, 191, 36, 0.05));
            color: var(--accent);
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .badge-silver {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.15), rgba(148, 163, 184, 0.05));
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .badge-bronze {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.15), rgba(217, 119, 6, 0.05));
            color: #d97706;
            border: 1px solid rgba(217, 119, 6, 0.3);
        }

        .badge-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        /* ─ BUTTONS ─ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: var(--accent);
            color: #000;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .btn-secondary:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }

        .btn-ghost {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text-muted);
        }

        .btn-ghost:hover {
            border-color: var(--primary);
            color: var(--text);
            background: rgba(102, 126, 234, 0.05);
        }

        .btn-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.3);
            border-color: var(--error);
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 0.8rem;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ─ FORMS ─ */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            padding: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select, textarea {
            background: var(--surface-light);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 11px 14px;
            color: var(--text);
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: var(--transition);
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            background: var(--surface);
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.15);
        }

        input::placeholder {
            color: var(--text-dim);
        }

        select option {
            background: var(--surface-light);
            color: var(--text);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
            background: rgba(102, 126, 234, 0.05);
        }

        /* ─ ALERTS ─ */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease-out;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
        }

        /* ─ RECEIPT ─ */
        .receipt {
            background: linear-gradient(135deg, var(--surface-light), var(--surface));
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-top: 12px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.9rem;
            color: var(--text-muted);
            border-bottom: 1px dashed rgba(100, 116, 139, 0.3);
        }

        .receipt-row.total {
            border: none;
            border-top: 2px solid var(--primary);
            margin-top: 12px;
            padding-top: 12px;
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .empty {
            padding: 60px 40px;
            text-align: center;
            color: var(--text-muted);
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 0.5fr auto;
            gap: 12px;
            margin-bottom: 12px;
            align-items: end;
        }

        .btn-remove {
            flex: 0 0 auto;
            cursor: pointer;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
            border-radius: 7px;
            padding: 6px 10px;
            font-size: 0.8rem;
            font-family: inherit;
            transition: var(--transition);
        }

        .btn-remove:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        /* ─ ACTION BUTTONS GROUP ─ */
        .action-btns {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .action-btns form {
            margin: 0;
            padding: 0;
            display: inline-flex;
        }

        @media (max-width: 768px) {
            .sidebar { width: 0; transform: translateX(-100%); }
            .main { margin-left: 0; padding: 24px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-grid { grid-template-columns: 1fr; }
            .page-header h2 { font-size: 1.8rem; }
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-dim);
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        <h1><i class="fa-solid fa-book-open" style="color:var(--primary)"></i> BayStore</h1>
        <p>Toko Buku Online</p>
    </div>
    <nav>
        <div class="nav-section">Menu Utama</div>
        <a href="?page=dashboard"    class="nav-item <?= $page==='dashboard'   ?'active':'' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="?page=books"        class="nav-item <?= $page==='books'       ?'active':'' ?>"><i class="fa-solid fa-book"></i> Buku</a>
        <a href="?page=members"      class="nav-item <?= $page==='members'     ?'active':'' ?>"><i class="fa-solid fa-users"></i> Member</a>
        <a href="?page=transactions" class="nav-item <?= $page==='transactions'?'active':'' ?>"><i class="fa-solid fa-receipt"></i> Transaksi</a>
    </nav>
    <div class="sidebar-footer">
        <i class="fa-solid fa-store" style="color:var(--primary);font-size:1.1rem"></i><br>BayStore<br><span style="font-size: 0.65rem; margin-top: 2px; display: block;">Online Bookstore</span>
    </div>
</aside>

<main class="main">

<?php
// Flash message
if (isset($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo "<div class=\"alert alert-{$f['type']}\">{$f['msg']}</div>";
    unset($_SESSION['flash']);
}
?>

<?php if ($jsRedirect): ?>
<div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i> Berhasil diproses! <a href="<?= htmlspecialchars($jsRedirect) ?>" style="color:inherit;font-weight:700">Klik di sini jika halaman tidak otomatis berpindah &rarr;</a>
</div>
<?php endif; ?>

<?php switch ($page):

// ════════════════════ DASHBOARD ════════════════════
case 'dashboard': ?>
<div class="page-header">
    <h2>Dashboard</h2>
    <p>Selamat datang di BayStore — Sistem Manajemen Toko Buku</p>
</div>
<div class="stats-grid">
    <div class="stat-card gold">
        <div class="icon-bg"><i class="fa-solid fa-book"></i></div>
        <div class="label">Total Buku</div>
        <div class="value"><?= $stats['total_books'] ?></div>
        <div class="sub">Judul tersedia</div>
    </div>
    <div class="stat-card green">
        <div class="icon-bg"><i class="fa-solid fa-users"></i></div>
        <div class="label">Total Member</div>
        <div class="value"><?= $stats['total_members'] ?></div>
        <div class="sub">Member terdaftar</div>
    </div>
    <div class="stat-card blue">
        <div class="icon-bg"><i class="fa-solid fa-receipt"></i></div>
        <div class="label">Transaksi</div>
        <div class="value"><?= $stats['total_transactions'] ?></div>
        <div class="sub"><?= $stats['completed_trx'] ?> selesai</div>
    </div>
    <div class="stat-card purple">
        <div class="icon-bg"><i class="fa-solid fa-sack-dollar"></i></div>
        <div class="label">Pendapatan</div>
        <div class="value" style="font-size:1.6rem; margin:14px 0 10px;">Rp <?= number_format($stats['total_revenue'],0,',','.') ?></div>
        <div class="sub">Total revenue</div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-book-open"></i> Buku Tersedia</h3>
        <a href="?page=books" class="btn btn-ghost btn-sm">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <table>
        <thead><tr><th>Judul</th><th>Author</th><th>Kategori</th><th>Harga</th><th>Stok</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($store->getBooks(), 0, 6) as $b): ?>
        <tr>
            <td><strong><?= htmlspecialchars($b->getTitle()) ?></strong></td>
            <td><?= htmlspecialchars($b->getAuthor()) ?></td>
            <td><span class="badge badge-blue"><?= $b->getCategory() ?></span></td>
            <td style="color:var(--accent)"><?= $b->getFormattedPrice() ?></td>
            <td><span class="badge <?= $b->getStock()>5?'badge-green':($b->getStock()>0?'badge-gold':'badge-red') ?>"><?= $b->getStock() ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php break;

// ════════════════════ BOOKS ════════════════════
case 'books': ?>
<div class="page-header">
    <h2>Manajemen Buku</h2>
    <p>Kelola koleksi buku toko</p>
</div>
<div class="card" style="margin-bottom:22px">
    <div class="card-header"><h3><i class="fa-solid fa-plus"></i> Tambah Buku Baru</h3></div>
    <form method="POST" action="?page=books">
        <input type="hidden" name="action" value="add_book">
        <div class="form-grid">
            <div class="form-group">
                <label>Judul Buku *</label>
                <input type="text" name="title" required placeholder="Judul buku...">
            </div>
            <div class="form-group">
                <label>Penulis *</label>
                <input type="text" name="author" required placeholder="Nama penulis...">
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category">
                    <option>Pemrograman</option><option>Pengembangan Diri</option>
                    <option>Sejarah</option><option>Novel</option>
                    <option>Sains</option><option>Umum</option>
                </select>
            </div>
            <div class="form-group">
                <label>ISBN</label>
                <input type="text" name="isbn" placeholder="978-...">
            </div>
            <div class="form-group">
                <label>Harga (Rp) *</label>
                <input type="number" name="price" required min="0" placeholder="90000">
            </div>
            <div class="form-group">
                <label>Stok *</label>
                <input type="number" name="stock" required min="0" placeholder="10">
            </div>
            <div class="form-group full">
                <label>Deskripsi</label>
                <textarea name="description" placeholder="Deskripsi singkat..."></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Buku</button>
        </div>
    </form>
</div>
<div class="card">
    <div class="card-header"><h3><i class="fa-solid fa-books"></i> Daftar Buku (<?= count($store->getBooks()) ?>)</h3></div>
    <?php if (empty($store->getBooks())): ?>
    <div class="empty">Belum ada buku. Tambahkan buku pertama di atas!</div>
    <?php else: ?>
    <table>
        <thead><tr><th>ID</th><th>Judul</th><th>Author</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($store->getBooks() as $b): ?>
        <tr>
            <td><code style="color:var(--accent);font-size:.76rem"><?= $b->getBookId() ?></code></td>
            <td>
                <strong><?= htmlspecialchars($b->getTitle()) ?></strong>
                <?php if($b->getIsbn()): ?><br><small style="color:var(--text-muted)"><?= $b->getIsbn() ?></small><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($b->getAuthor()) ?></td>
            <td><span class="badge badge-blue"><?= $b->getCategory() ?></span></td>
            <td style="color:var(--accent)"><?= $b->getFormattedPrice() ?></td>
            <td><span class="badge <?= $b->getStock()>5?'badge-green':($b->getStock()>0?'badge-gold':'badge-red') ?>"><?= $b->getStock() ?></span></td>
            <td>
                <form method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus buku ini?')">
                    <input type="hidden" name="action" value="delete_book">
                    <input type="hidden" name="book_id" value="<?= $b->getBookId() ?>">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php break;

// ════════════════════ MEMBERS ════════════════════
case 'members': ?>
<div class="page-header">
    <h2>Manajemen Member</h2>
    <p>Kelola data member toko buku</p>
</div>
<div class="card" style="margin-bottom:22px">
    <div class="card-header"><h3><i class="fa-solid fa-user-plus"></i> Tambah Member Baru</h3></div>
    <form method="POST" action="?page=members">
        <input type="hidden" name="action" value="add_member">
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Nama member...">
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="email@contoh.com">
            </div>
            <div class="form-group full">
                <label>No. HP *</label>
                <input type="text" name="phone" required placeholder="08xxxxxxxxxx">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Daftarkan Member</button>
        </div>
    </form>
</div>
<div class="card">
    <div class="card-header"><h3><i class="fa-solid fa-users"></i> Daftar Member (<?= count($store->getMembers()) ?>)</h3></div>
    <table>
        <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Level</th><th>Diskon</th><th>Total Belanja</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($store->getMembers() as $m): ?>
        <tr>
            <td><code style="color:var(--accent);font-size:.76rem"><?= $m->getMemberId() ?></code></td>
            <td><strong><?= htmlspecialchars($m->getName()) ?></strong></td>
            <td><?= htmlspecialchars($m->getEmail()) ?></td>
            <td><?= $m->getPhone() ?></td>
            <td><span class="badge badge-<?= $m->getMembershipType() ?>"><?= strtoupper($m->getMembershipType()) ?></span></td>
            <td style="color:var(--green)"><?= ($m->getDiscount()*100) ?>%</td>
            <td>Rp <?= number_format($m->getTotalSpent(),0,',','.') ?></td>
            <td>
                <form method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus member ini? Semua transaksi dari member ini juga akan terhapus secara otomatis!')">
                    <input type="hidden" name="action" value="delete_member">
                    <input type="hidden" name="member_id" value="<?= $m->getMemberId() ?>">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php break;

// ════════════════════ TRANSACTIONS ════════════════════
case 'transactions': ?>
<div class="page-header">
    <h2>Transaksi</h2>
    <p>Buat dan pantau transaksi pembelian</p>
</div>
<div class="card" style="margin-bottom:22px">
    <div class="card-header"><h3><i class="fa-solid fa-plus"></i> Buat Transaksi Baru</h3></div>
    <form method="POST" action="?page=transactions">
        <input type="hidden" name="action" value="add_transaction">
        <div class="form-grid">
            <div class="form-group full">
                <label>Pilih Member *</label>
                <select name="member_id" required id="memberSel" onchange="updateInfo()">
                    <option value="">-- Pilih Member --</option>
                    <?php foreach ($store->getMembers() as $m): ?>
                    <option value="<?= $m->getMemberId() ?>"
                        data-disc="<?= $m->getDiscount()*100 ?>"
                        data-lvl="<?= strtoupper($m->getMembershipType()) ?>">
                        <?= htmlspecialchars($m->getName()) ?> — <?= strtoupper($m->getMembershipType()) ?> (diskon <?= $m->getDiscount()*100 ?>%)
                    </option>
                    <?php endforeach; ?>
                </select>
                <div id="mInfo" style="font-size:.8rem;color:var(--green);margin-top:5px;min-height:18px"></div>
            </div>

            <div class="form-group full">
                <label>Pilih Buku &amp; Jumlah *</label>
                <div id="itemsWrap">
                    <div class="item-row">
                        <select name="book_ids[]" onchange="calcPreview()">
                            <option value="">-- Pilih Buku --</option>
                            <?php foreach ($store->getBooks() as $b): if($b->isAvailable()): ?>
                            <option value="<?= $b->getBookId() ?>"
                                data-price="<?= $b->getPrice() ?>">
                                <?= htmlspecialchars($b->getTitle()) ?> — <?= $b->getFormattedPrice() ?> (stok: <?= $b->getStock() ?>)
                            </option>
                            <?php endif; endforeach; ?>
                        </select>
                        <input type="number" name="qtys[]" min="1" value="1" onchange="calcPreview()">
                    </div>
                </div>
                <button type="button" onclick="addRow()" class="btn btn-ghost btn-sm" style="margin-top:8px"><i class="fa-solid fa-plus"></i> Tambah Buku Lain</button>
            </div>

            <div class="form-group full" id="previewWrap" style="display:none">
                <label>Preview Pesanan</label>
                <div class="receipt" id="previewBody"></div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-credit-card"></i> Proses Transaksi</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Transaksi (<?= count($store->getTransactions()) ?>)</h3></div>
    <?php $txs = $store->getTransactions(); if(empty($txs)): ?>
    <div class="empty">Belum ada transaksi. Buat transaksi pertama di atas!</div>
    <?php else: ?>
    <table>
        <thead><tr><th>ID Transaksi</th><th>Member</th><th>Detail Item</th><th>Subtotal</th><th>Diskon</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach (array_reverse($txs, true) as $tx):
            $d = $tx->toArray(); ?>
        <tr>
            <td><code style="color:var(--accent)"><?= $d['id'] ?></code></td>
            <td><strong><?= htmlspecialchars($d['member']) ?></strong></td>
            <td class="tx-items">
                <?php if (!empty($d['items'])): ?>
                <div style="cursor:pointer; transition:var(--transition);" onclick="viewDetail('<?= $d['id'] ?>')" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color=''">
                    <i class="fa-solid fa-book" style="color:var(--primary);font-size:0.75rem"></i> 
                    <?= htmlspecialchars($d['items'][0]['book']) ?> 
                    <?= count($d['items']) > 1 ? '... <span style="font-size:0.75rem;color:var(--text-muted)">(+' . (count($d['items'])-1) . ' item)</span>' : '' ?>
                </div>
                <?php endif; ?>
            </td>
            <td><?= $d['subtotal'] ?></td>
            <td style="color:var(--green)"><?= $d['discount'] ?></td>
            <td style="color:var(--accent);font-weight:700;font-size:1rem"><?= $d['total'] ?></td>
            <td>
                <?php if ($d['status'] === 'pending'): ?>
                    <span class="badge badge-pending"><i class="fa-regular fa-clock"></i> Pending</span>
                <?php elseif ($d['status'] === 'completed'): ?>
                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> Completed</span>
                <?php else: ?>
                    <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: var(--error); border: 1px solid rgba(239, 68, 68, 0.3);"><i class="fa-solid fa-xmark"></i> Cancelled</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="action-btns">
                    <button type="button" class="btn btn-sm btn-ghost" onclick="viewDetail('<?= $d['id'] ?>')"><i class="fa-solid fa-eye"></i> Lihat</button>
                    <?php if ($d['status'] === 'pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="confirm_transaction">
                        <input type="hidden" name="transaction_id" value="<?= $d['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1)); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.3);"><i class="fa-solid fa-check"></i> Konfirmasi</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Yakin hapus transaksi <?= $d['id'] ?>? Tindakan ini tidak bisa dibatalkan!')">
                        <input type="hidden" name="action" value="delete_transaction">
                        <input type="hidden" name="transaction_id" value="<?= $d['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- MODAL DETAIL TRANSAKSI -->
<div id="detailModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:1000;padding:20px;overflow:auto">
    <div style="background:var(--surface);border-radius:16px;max-width:600px;margin:40px auto;padding:30px;border:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
            <h2 style="color:var(--text);font-size:1.4rem;margin:0"><i class="fa-solid fa-file-lines" style="color:var(--primary)"></i> Detail Transaksi</h2>
            <button type="button" onclick="closeDetail()" style="background:transparent;border:none;color:var(--text);font-size:1.3rem;cursor:pointer;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:background 0.2s" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div id="detailContent" style="color:var(--text)">
            <!-- Isi akan di-populate oleh JavaScript -->
        </div>
    </div>
</div>

<script>
// ── Data transaksi untuk modal ──
const transactionData = {
    <?php foreach ($store->getTransactions() as $tx):
        $d = $tx->toArray(); ?>
    '<?= $d['id'] ?>': {
        id: '<?= $d['id'] ?>',
        member: '<?= addslashes(htmlspecialchars($d['member'])) ?>',
        date: '<?= $d['date'] ?>',
        status: '<?= $d['status'] ?>',
        subtotal: '<?= $d['subtotal'] ?>',
        discount: '<?= $d['discount'] ?>',
        total: '<?= $d['total'] ?>',
        items: <?= json_encode($d['items']) ?>
    },
    <?php endforeach; ?>
};

// ── Tampilkan detail transaksi ──
function viewDetail(txId) {
    const tx = transactionData[txId];
    if (!tx) return;
    
    let html = `
        <div style="margin-bottom:20px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div>
                    <p style="font-size:0.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px">ID Transaksi</p>
                    <p style="font-size:1.2rem;font-weight:700;color:var(--accent)">${tx.id}</p>
                </div>
                <div>
                    <p style="font-size:0.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px">Tanggal</p>
                    <p style="font-size:1.1rem;font-weight:600">${tx.date}</p>
                </div>
                <div>
                    <p style="font-size:0.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px">Member</p>
                    <p style="font-size:1.1rem;font-weight:700">${tx.member}</p>
                </div>
                <div>
                    <p style="font-size:0.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px">Status</p>
                    <p style="font-weight:700">
                        ${tx.status === 'completed' ? '<i class="fa-solid fa-check" style="color:var(--success)"></i> <span style="color:var(--success)">Completed</span>' :
                          tx.status === 'pending' ? '<i class="fa-regular fa-clock" style="color:var(--warning)"></i> <span style="color:var(--warning)">Pending</span>' :
                          '<i class="fa-solid fa-xmark" style="color:var(--error)"></i> <span style="color:var(--error)">Cancelled</span>'}
                    </p>
                </div>
            </div>
        </div>
        
        <div style="border-top:1px solid var(--border);padding-top:20px;margin-bottom:20px">
            <h3 style="font-size:1rem;margin-bottom:12px;color:var(--text)"><i class="fa-solid fa-list" style="color:var(--primary)"></i> Daftar Item</h3>
            <div style="background:rgba(102,126,234,0.05);border-radius:8px;padding:12px">
    `;
    
    tx.items.forEach((item, i) => {
        html += `
            <div style="display:grid;grid-template-columns:2fr 0.5fr 1fr;gap:12px;padding:8px 0;${i > 0 ? 'border-top:1px solid var(--border);padding-top:12px;' : ''}">
                <div>
                    <p style="color:var(--text);font-weight:600">${item.book}</p>
                </div>
                <div style="text-align:center">
                    <p style="color:var(--text-muted)">×${item.qty}</p>
                </div>
                <div style="text-align:right">
                    <p style="color:var(--accent);font-weight:700">${item.total}</p>
                </div>
            </div>
        `;
    });
    
    html += `
            </div>
        </div>
        
        <div style="background:linear-gradient(135deg, var(--surface-light), var(--surface));border:1px solid var(--border);border-radius:12px;padding:16px">
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Subtotal:</span>
                <span style="text-align:right;color:var(--text)">${tx.subtotal}</span>
            </div>
            ${tx.discount !== 'Rp 0' ? `
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Diskon:</span>
                <span style="text-align:right;color:var(--success)">- ${tx.discount}</span>
            </div>
            ` : ''}
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px">
                <span style="color:var(--text);font-weight:700;font-size:1.1rem">Total Bayar:</span>
                <span style="text-align:right;color:var(--primary);font-weight:700;font-size:1.2rem">${tx.total}</span>
            </div>
        </div>
        
        ${tx.status === 'pending' ? `
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap">
            <form method="POST" style="flex:1;min-width:140px">
                <input type="hidden" name="action" value="confirm_transaction">
                <input type="hidden" name="transaction_id" value="${tx.id}">
                <button type="submit" class="btn" style="width:100%;background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(16, 185, 129, 0.1)); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.4);"><i class='fa-solid fa-check'></i> Konfirmasi Pembelian</button>
            </form>
            <button type="button" onclick="closeDetail()" class="btn btn-ghost" style="flex:1;min-width:100px"><i class='fa-solid fa-xmark'></i> Tutup</button>
        </div>
        ` : `
        <div style="margin-top:20px">
            <button type="button" onclick="closeDetail()" class="btn btn-ghost" style="width:100%"><i class='fa-solid fa-xmark'></i> Tutup</button>
        </div>
        `}
    `;
    
    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// ── Tutup modal ──
function closeDetail() {
    document.getElementById('detailModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ── Tutup modal dengan ESC ──
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.getElementById('detailModal').style.display !== 'none') {
        closeDetail();
    }
});

// ── Clone template baris buku ──
function addRow() {
    const wrap = document.getElementById('itemsWrap');
    const orig = wrap.querySelector('.item-row');
    const row  = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = orig.innerHTML
        + '<button type="button" class="btn-remove" onclick="this.parentElement.remove();calcPreview()"><i class="fa-solid fa-xmark"></i></button>';
    row.querySelector('select').value = '';
    row.querySelector('input').value  = 1;
    row.querySelector('select').onchange = calcPreview;
    row.querySelector('input').onchange  = calcPreview;
    wrap.appendChild(row);
}

// ── Info member ──
function updateInfo() {
    const sel = document.getElementById('memberSel');
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('mInfo').textContent =
        sel.value ? '✅ Level ' + opt.dataset.lvl + ' — Diskon ' + opt.dataset.disc + '%' : '';
    calcPreview();
}

// ── Kalkulasi preview ──
function calcPreview() {
    const memberSel = document.getElementById('memberSel');
    const discRate  = parseFloat(memberSel.options[memberSel.selectedIndex]?.dataset.disc || 0) / 100;
    let subtotal = 0, html = '';

    document.querySelectorAll('#itemsWrap .item-row').forEach(row => {
        const bSel = row.querySelector('select');
        const qty  = parseInt(row.querySelector('input')?.value) || 1;
        const opt  = bSel.options[bSel.selectedIndex];
        if (!bSel.value || !opt?.dataset?.price) return;
        const price = parseFloat(opt.dataset.price);
        const line  = price * qty;
        subtotal   += line;
        const name  = opt.text.split('—')[0].trim();
        html += `<div class="receipt-row"><span>${name} ×${qty}</span><span>Rp ${line.toLocaleString('id-ID')}</span></div>`;
    });

    if (!html) { document.getElementById('previewWrap').style.display = 'none'; return; }

    const disc  = subtotal * discRate;
    const total = subtotal - disc;
    html += `<div class="receipt-row" style="border-top:1px solid var(--border);margin-top:8px;padding-top:8px"><span>Subtotal</span><span>Rp ${subtotal.toLocaleString('id-ID')}</span></div>`;
    if (disc > 0) html += `<div class="receipt-row" style="color:var(--green)"><span>Diskon ${discRate*100}%</span><span>- Rp ${disc.toLocaleString('id-ID')}</span></div>`;
    html += `<div class="receipt-row total"><span>TOTAL BAYAR</span><span>Rp ${total.toLocaleString('id-ID')}</span></div>`;

    document.getElementById('previewBody').innerHTML = html;
    document.getElementById('previewWrap').style.display = 'block';
}
</script>
<?php break;

// ════════════════════ DEFAULT ════════════════════
default: ?>
<script>window.location.href='?page=dashboard';</script>
<?php endswitch; ?>

</main>
</body>
</html>
<?php ob_end_flush(); ?>
