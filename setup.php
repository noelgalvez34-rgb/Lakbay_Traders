<?php
// setup.php
// Isang beses lang patakbuhin: gumagawa ng unang admin account (at optional na sample data).
// BURAHIN ang file na ito pagkatapos gamitin.

require __DIR__ . '/includes/auth.php';

$user_count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$messages   = [];
$done       = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if ($user_count > 0) {
        $messages[] = ['danger', 'May admin account na. Hindi na puwedeng gumawa ulit gamit ang setup.'];
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullname = trim($_POST['full_name'] ?? '');

        if ($username === '' || strlen($username) > 50 || strlen($password) < 6) {
            $messages[] = ['danger', 'Username ay required, at ang password ay dapat hindi bababa sa 6 na character.'];
        } else {
            $pdo->prepare('INSERT INTO users (username, password_hash, full_name) VALUES (?, ?, ?)')
                ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $fullname !== '' ? $fullname : 'Administrator']);
            $messages[] = ['success', 'Nagawa na ang admin account na "' . $username . '".'];
            $done = true;

            // Optional na sample data (kung walang laman ang mga table)
            if (!empty($_POST['sample'])) {
                $empty = true;
                foreach (['customers', 'products', 'employees', 'orders'] as $t) {
                    if ((int)$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn() > 0) $empty = false;
                }
                if ($empty) {
                    $c = $pdo->prepare('INSERT INTO customers (first_name,last_name,email,phone,address) VALUES (?,?,?,?,?)');
                    foreach ([
                        ['Juan', 'Dela Cruz', 'juan@example.com', '09171234567', 'Pasig City'],
                        ['Maria', 'Santos', 'maria@example.com', '09181234567', 'Quezon City'],
                        ['Pedro', 'Reyes', 'pedro@example.com', '09191234567', 'Makati City'],
                    ] as $row) $c->execute($row);

                    $em = $pdo->prepare('INSERT INTO employees (first_name,last_name,role,hire_date) VALUES (?,?,?,?)');
                    foreach ([
                        ['Ana', 'Garcia', 'Sales Staff', '2024-01-15'],
                        ['Mark', 'Lopez', 'Cashier', '2024-06-01'],
                    ] as $row) $em->execute($row);

                    $pr = $pdo->prepare('INSERT INTO products (product_name,description,price,stock_quantity) VALUES (?,?,?,?)');
                    foreach ([
                        ['Bigas 25kg', 'Premium rice sack', 1350.00, 40],
                        ['Cooking Oil 1L', 'Coconut cooking oil', 95.50, 120],
                        ['Asukal 1kg', 'Refined sugar', 78.00, 8],
                        ['Kape 3-in-1 (box)', 'Instant coffee 30 sachets', 165.00, 60],
                        ['De-lata Sardinas', 'Sardines in tomato sauce', 24.75, 5],
                    ] as $row) $pr->execute($row);

                    $messages[] = ['info', 'Nadagdag ang sample na customers, employees, at products.'];
                } else {
                    $messages[] = ['warning', 'Hindi nagdagdag ng sample data dahil may laman na ang mga table.'];
                }
            }
        }
    }
    $user_count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Setup | Lakbay Traders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">
<div class="login-card">
    <div class="brand-big mb-3">LAKBAY TRADERS<br><small class="fs-6 fw-normal text-muted">First-time Setup</small></div>

    <?php foreach ($messages as $m): ?>
        <div class="alert alert-<?= e($m[0]) ?> py-2"><?= e($m[1]) ?></div>
    <?php endforeach; ?>

    <?php if ($user_count > 0): ?>
        <p class="mb-3">May admin account na. <strong>Burahin na ang file na <code>setup.php</code></strong> para ligtas.</p>
        <a href="login.php" class="btn btn-navy w-100">Pumunta sa Login</a>
    <?php else: ?>
        <form method="post" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input type="text" name="full_name" class="form-control" value="Administrator" maxlength="100">
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="admin" maxlength="50" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password (6+ characters)</label>
                <input type="password" name="password" class="form-control" minlength="6" required>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="sample" value="1" id="sample" checked>
                <label class="form-check-label" for="sample">Magdagdag ng sample data (para may mai-test agad)</label>
            </div>
            <button class="btn btn-navy w-100 py-2">Gumawa ng Admin</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
