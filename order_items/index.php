<?php
// order_items/index.php - lahat ng produkto sa mga order, may subtotal
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Order Items';
$active = 'order_items';

$order_id = (int)($_GET['order_id'] ?? 0);
$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($order_id > 0) {
    $where[] = 'oi.order_id = ?';
    $params[] = $order_id;
}
if ($q !== '') {
    $where[] = 'p.product_name LIKE ?';
    $params[] = '%' . $q . '%';
}

$sql = 'SELECT oi.order_item_id, oi.order_id, p.product_name, oi.quantity, oi.unit_price,
               (oi.quantity * oi.unit_price) AS subtotal, o.status
        FROM order_items oi
        JOIN products p ON p.product_id = oi.product_id
        JOIN orders o ON o.order_id = oi.order_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY oi.order_id DESC, oi.order_item_id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$total = 0;
foreach ($rows as $r) $total += (float)$r['subtotal'];

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form method="get" class="flex-grow-1" style="max-width:520px;">
        <div class="input-group">
            <input type="number" name="order_id" class="form-control" placeholder="Order #" min="1" value="<?= $order_id > 0 ? $order_id : '' ?>" style="max-width:130px;">
            <input type="text" name="q" class="form-control" placeholder="Product name..." value="<?= e($q) ?>">
            <button class="btn btn-navy"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Order ID</th><th>Product</th><th class="text-end">Quantity</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Walang nahanap na order item.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><a href="../orders/view.php?id=<?= (int)$r['order_id'] ?>">#<?= (int)$r['order_id'] ?></a></td>
                        <td><?= e($r['product_name']) ?></td>
                        <td class="text-end"><?= (int)$r['quantity'] ?></td>
                        <td class="text-end"><?= money($r['unit_price']) ?></td>
                        <td class="text-end"><?= money($r['subtotal']) ?></td>
                        <td><?= status_badge($r['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <?php if ($rows): ?>
                <tfoot><tr><th colspan="4" class="text-end">Total ng ipinapakita</th><th class="text-end"><?= money($total) ?></th><th></th></tr></tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
