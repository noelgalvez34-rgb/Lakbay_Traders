<?php
// dashboard.php
require __DIR__ . '/includes/auth.php';
require_login();

$base = '';
$page_title = 'Dashboard';
$active = 'dashboard';

// Mga bilang mula sa totoong database
$total_customers = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$total_products  = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$total_orders    = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$total_employees = (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();

$low_stock = $pdo->prepare('SELECT COUNT(*) FROM products WHERE stock_quantity <= ?');
$low_stock->execute([LOW_STOCK_LIMIT]);
$low_stock = (int)$low_stock->fetchColumn();

// Sales = kabuuang halaga ng mga Completed na order
$sales = (float)$pdo->query(
    "SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0)
     FROM order_items oi JOIN orders o ON o.order_id = oi.order_id
     WHERE o.status = 'Completed'"
)->fetchColumn();

$by_status = $pdo->query('SELECT status, COUNT(*) AS n FROM orders GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

$recent = $pdo->query(
    'SELECT o.order_id, o.order_date, o.status, c.first_name, c.last_name,
            (SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0) FROM order_items oi WHERE oi.order_id = o.order_id) AS total
     FROM orders o JOIN customers c ON c.customer_id = o.customer_id
     ORDER BY o.order_id DESC LIMIT 5'
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-3">
    <?php
    $cards = [
        ['Customers', $total_customers, 'bi-people'],
        ['Products',  $total_products,  'bi-box-seam'],
        ['Orders',    $total_orders,    'bi-cart-check'],
        ['Employees', $total_employees, 'bi-person-badge'],
    ];
    foreach ($cards as $c): ?>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="stat-icon"><i class="bi <?= $c[2] ?>"></i></div>
                <div>
                    <div class="stat-label"><?= $c[0] ?></div>
                    <div class="stat-value"><?= $c[1] ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">Sales Overview</div>
            <div class="card-body">
                <div class="stat-label">Total Sales (Completed Orders)</div>
                <div class="stat-value mb-3"><?= money($sales) ?></div>
                <?php foreach (ORDER_STATUSES as $s):
                    $n = (int)($by_status[$s] ?? 0);
                    $pct = $total_orders > 0 ? round($n / $total_orders * 100) : 0; ?>
                    <div class="d-flex justify-content-between small mb-1"><span><?= e($s) ?></span><span><?= $n ?> order(s)</span></div>
                    <div class="progress mb-2" style="height:8px;">
                        <div class="progress-bar bg-primary" style="width:<?= $pct ?>%; background-color:#0a2a5e !important;"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">Inventory Alert</div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="stat-label">Produktong low-stock (<?= LOW_STOCK_LIMIT ?> pababa)</div>
                <div class="stat-value <?= $low_stock > 0 ? 'text-danger' : '' ?>"><?= $low_stock ?></div>
                <a href="products/index.php" class="btn btn-outline-navy btn-sm mt-3 align-self-start">Tingnan ang Products</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Orders</span>
        <a href="orders/index.php" class="btn btn-sm btn-outline-navy">Lahat</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$recent): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Wala pang orders.</td></tr>
                <?php endif; ?>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><a href="orders/view.php?id=<?= (int)$r['order_id'] ?>">#<?= (int)$r['order_id'] ?></a></td>
                        <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td><?= e(date('M d, Y h:i A', strtotime($r['order_date']))) ?></td>
                        <td><?= money($r['total']) ?></td>
                        <td><?= status_badge($r['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
