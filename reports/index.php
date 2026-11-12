<?php
// reports/index.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Reports';
$active = 'reports';

$total_orders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$by_status = $pdo->query('SELECT status, COUNT(*) AS n FROM orders GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

$sales_total = (float)$pdo->query(
    "SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0)
     FROM order_items oi JOIN orders o ON o.order_id = oi.order_id WHERE o.status = 'Completed'"
)->fetchColumn();
$completed = (int)($by_status['Completed'] ?? 0);
$avg_order = $completed > 0 ? $sales_total / $completed : 0;

$monthly = $pdo->query(
    "SELECT DATE_FORMAT(o.order_date, '%Y-%m') AS ym, COUNT(DISTINCT o.order_id) AS orders_count,
            SUM(oi.quantity * oi.unit_price) AS amount
     FROM orders o JOIN order_items oi ON oi.order_id = o.order_id
     WHERE o.status = 'Completed'
     GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
     ORDER BY ym DESC LIMIT 6"
)->fetchAll();

$top = $pdo->query(
    "SELECT p.product_name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.unit_price) AS amount
     FROM order_items oi
     JOIN products p ON p.product_id = oi.product_id
     JOIN orders o ON o.order_id = oi.order_id
     WHERE o.status <> 'Cancelled'
     GROUP BY p.product_id, p.product_name
     ORDER BY qty DESC LIMIT 5"
)->fetchAll();

$inventory = $pdo->query('SELECT product_name, price, stock_quantity FROM products ORDER BY stock_quantity ASC, product_name')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-end mb-3 no-print">
    <button class="btn btn-outline-navy" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="row g-3 mb-3">
    <?php
    $cards = [
        ['Total Orders', $total_orders],
        ['Completed', (int)($by_status['Completed'] ?? 0)],
        ['Pending', (int)($by_status['Pending'] ?? 0)],
        ['Processing', (int)($by_status['Processing'] ?? 0)],
        ['Cancelled', (int)($by_status['Cancelled'] ?? 0)],
    ];
    foreach ($cards as $c): ?>
        <div class="col-6 col-lg">
            <div class="card p-3"><div class="stat-label"><?= $c[0] ?></div><div class="stat-value"><?= $c[1] ?></div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">Sales Summary (Completed Orders)</div>
            <div class="card-body">
                <div class="d-flex justify-content-between"><span>Total Sales</span><strong><?= money($sales_total) ?></strong></div>
                <div class="d-flex justify-content-between mb-3"><span>Average per Order</span><strong><?= money($avg_order) ?></strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Month</th><th class="text-end">Orders</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                        <?php if (!$monthly): ?><tr><td colspan="3" class="text-center text-muted">Wala pang completed na order.</td></tr><?php endif; ?>
                        <?php foreach ($monthly as $m): ?>
                            <tr><td><?= e(date('F Y', strtotime($m['ym'] . '-01'))) ?></td><td class="text-end"><?= (int)$m['orders_count'] ?></td><td class="text-end"><?= money($m['amount']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">Top Products (hindi kasama ang Cancelled)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Product</th><th class="text-end">Qty Sold</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                        <?php if (!$top): ?><tr><td colspan="3" class="text-center text-muted">Wala pang benta.</td></tr><?php endif; ?>
                        <?php foreach ($top as $t): ?>
                            <tr><td><?= e($t['product_name']) ?></td><td class="text-end"><?= (int)$t['qty'] ?></td><td class="text-end"><?= money($t['amount']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Product Inventory</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Product</th><th class="text-end">Price</th><th class="text-end">Stock</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$inventory): ?><tr><td colspan="4" class="text-center text-muted">Walang product.</td></tr><?php endif; ?>
                <?php foreach ($inventory as $i): $s = (int)$i['stock_quantity']; ?>
                    <tr>
                        <td><?= e($i['product_name']) ?></td>
                        <td class="text-end"><?= money($i['price']) ?></td>
                        <td class="text-end"><?= $s ?></td>
                        <td>
                            <?php if ($s === 0): ?><span class="badge bg-danger">Out of stock</span>
                            <?php elseif ($s <= LOW_STOCK_LIMIT): ?><span class="badge bg-warning text-dark">Low stock</span>
                            <?php else: ?><span class="badge bg-success">OK</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
