<?php
// orders/index.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Orders';
$active = 'orders';

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
if (!in_array($status, ORDER_STATUSES, true)) $status = '';

$where = [];
$params = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = '(o.order_id = ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR CONCAT(c.first_name, " ", c.last_name) LIKE ?)';
    array_push($params, ctype_digit($q) ? (int)$q : 0, $like, $like, $like);
}
if ($status !== '') {
    $where[] = 'o.status = ?';
    $params[] = $status;
}

$sql = 'SELECT o.order_id, o.order_date, o.status,
               c.first_name AS cfn, c.last_name AS cln,
               e.first_name AS efn, e.last_name AS eln,
               (SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0) FROM order_items oi WHERE oi.order_id = o.order_id) AS total
        FROM orders o
        JOIN customers c ON c.customer_id = o.customer_id
        JOIN employees e ON e.employee_id = o.employee_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY o.order_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form method="get" class="flex-grow-1" style="max-width:560px;">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Order # o customer..." value="<?= e($q) ?>">
            <select name="status" class="form-select" style="max-width:150px;">
                <option value="">All status</option>
                <?php foreach (ORDER_STATUSES as $s): ?>
                    <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-navy"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <a href="create.php" class="btn btn-navy"><i class="bi bi-plus-lg"></i> New Order</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Order #</th><th>Customer</th><th>Employee</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Walang nahanap na order.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['order_id'] ?></td>
                        <td><?= e($r['cfn'] . ' ' . $r['cln']) ?></td>
                        <td><?= e($r['efn'] . ' ' . $r['eln']) ?></td>
                        <td><?= e(date('M d, Y h:i A', strtotime($r['order_date']))) ?></td>
                        <td><?= money($r['total']) ?></td>
                        <td><?= status_badge($r['status']) ?></td>
                        <td class="actions"><a href="view.php?id=<?= (int)$r['order_id'] ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-eye"></i> View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
