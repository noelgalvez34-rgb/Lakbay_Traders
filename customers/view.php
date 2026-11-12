<?php
// customers/view.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Customer Details';
$active = 'customers';

$id = get_id();
$stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) {
    flash_set('danger', 'Hindi nahanap ang customer.');
    redirect('index.php');
}

$orders = $pdo->prepare(
    'SELECT o.order_id, o.order_date, o.status,
            (SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0) FROM order_items oi WHERE oi.order_id = o.order_id) AS total
     FROM orders o WHERE o.customer_id = ? ORDER BY o.order_id DESC'
);
$orders->execute([$id]);
$orders = $orders->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= e($c['first_name'] . ' ' . $c['last_name']) ?></span>
        <a href="edit.php?id=<?= (int)$c['customer_id'] ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-pencil"></i> Edit</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4"><div class="stat-label">Email</div><?= e($c['email'] ?: '-') ?></div>
            <div class="col-12 col-md-4"><div class="stat-label">Phone</div><?= e($c['phone'] ?: '-') ?></div>
            <div class="col-12 col-md-4"><div class="stat-label">Address</div><?= e($c['address'] ?: '-') ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Orders ng Customer</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$orders): ?><tr><td colspan="4" class="text-center text-muted py-4">Wala pang order.</td></tr><?php endif; ?>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><a href="../orders/view.php?id=<?= (int)$o['order_id'] ?>">#<?= (int)$o['order_id'] ?></a></td>
                        <td><?= e(date('M d, Y h:i A', strtotime($o['order_date']))) ?></td>
                        <td><?= money($o['total']) ?></td>
                        <td><?= status_badge($o['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<a href="index.php" class="btn btn-outline-secondary mt-3">&larr; Back</a>
<?php include __DIR__ . '/../includes/footer.php'; ?>
