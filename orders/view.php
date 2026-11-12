<?php
// orders/view.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Order Details';
$active = 'orders';

$id = get_id();
$stmt = $pdo->prepare(
    'SELECT o.*, c.first_name AS cfn, c.last_name AS cln, c.phone, c.address,
            e.first_name AS efn, e.last_name AS eln
     FROM orders o
     JOIN customers c ON c.customer_id = o.customer_id
     JOIN employees e ON e.employee_id = o.employee_id
     WHERE o.order_id = ?'
);
$stmt->execute([$id]);
$o = $stmt->fetch();
if (!$o) {
    flash_set('danger', 'Hindi nahanap ang order.');
    redirect('index.php');
}

$items = $pdo->prepare(
    'SELECT oi.order_item_id, oi.quantity, oi.unit_price, p.product_name, (oi.quantity * oi.unit_price) AS subtotal
     FROM order_items oi JOIN products p ON p.product_id = oi.product_id
     WHERE oi.order_id = ? ORDER BY oi.order_item_id'
);
$items->execute([$id]);
$items = $items->fetchAll();

$total = 0;
foreach ($items as $it) $total += (float)$it['subtotal'];

include __DIR__ . '/../includes/header.php';
?>
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Order #<?= (int)$o['order_id'] ?></span>
                <?= status_badge($o['status']) ?>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6"><div class="stat-label">Customer</div><a href="../customers/view.php?id=<?= (int)$o['customer_id'] ?>"><?= e($o['cfn'] . ' ' . $o['cln']) ?></a></div>
                    <div class="col-12 col-md-6"><div class="stat-label">Assigned Employee</div><a href="../employees/view.php?id=<?= (int)$o['employee_id'] ?>"><?= e($o['efn'] . ' ' . $o['eln']) ?></a></div>
                    <div class="col-12 col-md-6"><div class="stat-label">Order Date</div><?= e(date('M d, Y h:i A', strtotime($o['order_date']))) ?></div>
                    <div class="col-12 col-md-6"><div class="stat-label">Address</div><?= e($o['address'] ?: '-') ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">Update Status</div>
            <div class="card-body">
                <?php if ($o['status'] === 'Cancelled'): ?>
                    <p class="text-muted mb-0">Cancelled na ang order na ito at naibalik na ang stock. Hindi na ito puwedeng baguhin.</p>
                <?php else: ?>
                    <form method="post" action="update_status.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                        <select name="status" class="form-select mb-3">
                            <?php foreach (ORDER_STATUSES as $s): ?>
                                <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-navy w-100" onclick="if(this.form.status.value==='Cancelled'){return confirm('I-cancel ang order? Maibabalik ang stock at hindi na ito mababago.');}">Update</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Mga Produkto sa Order</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Order ID</th><th>Product</th><th class="text-end">Quantity</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td>#<?= (int)$o['order_id'] ?></td>
                        <td><?= e($it['product_name']) ?></td>
                        <td class="text-end"><?= (int)$it['quantity'] ?></td>
                        <td class="text-end"><?= money($it['unit_price']) ?></td>
                        <td class="text-end"><?= money($it['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="4" class="text-end">TOTAL</th><th class="text-end"><?= money($total) ?></th></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<a href="index.php" class="btn btn-outline-secondary mt-3">&larr; Back</a>
<?php include __DIR__ . '/../includes/footer.php'; ?>
