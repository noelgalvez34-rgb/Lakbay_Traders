<?php
// orders/create.php
// Gumagawa ng bagong order gamit ang database transaction:
// 1) i-lock ang mga product row  2) i-check ang stock  3) i-save ang order at items
// 4) ibawas ang stock. Kapag may mali, lahat ay ibabalik (rollback).
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'New Order';
$active = 'orders';

$customers = $pdo->query('SELECT customer_id, first_name, last_name FROM customers ORDER BY first_name, last_name')->fetchAll();
$employees = $pdo->query('SELECT employee_id, first_name, last_name FROM employees ORDER BY first_name, last_name')->fetchAll();
$products  = $pdo->query('SELECT product_id, product_name, price, stock_quantity FROM products ORDER BY product_name')->fetchAll();

$errors = [];
$sel_customer = (int)($_POST['customer_id'] ?? 0);
$sel_employee = (int)($_POST['employee_id'] ?? 0);
$posted_rows  = [];

// Isang row ng produkto (ginagamit din sa template ng JavaScript)
function item_row($products, $pid = 0, $qty = 1)
{
    ob_start(); ?>
    <div class="item-row">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <span class="mini-label">Product</span>
                <select name="product_id[]" class="form-select product-select">
                    <option value="">-- Pumili ng product --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['product_id'] ?>"
                                data-price="<?= e($p['price']) ?>"
                                data-stock="<?= (int)$p['stock_quantity'] ?>"
                                <?= (int)$pid === (int)$p['product_id'] ? 'selected' : '' ?>
                                <?= (int)$p['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                            <?= e($p['product_name']) ?> (stock: <?= (int)$p['stock_quantity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-md-2">
                <span class="mini-label">Qty</span>
                <input type="number" name="quantity[]" class="form-control qty" min="1" step="1" value="<?= (int)$qty ?>">
            </div>
            <div class="col-4 col-md-2">
                <span class="mini-label">Price</span>
                <div class="price fw-semibold">₱0.00</div>
                <small class="text-muted">Stock: <span class="stock">-</span></small>
            </div>
            <div class="col-4 col-md-2">
                <span class="mini-label">Subtotal</span>
                <div class="subtotal fw-semibold">₱0.00</div>
            </div>
            <div class="col-12 col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Alisin"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Kolektahin ang mga item at pagsamahin kung pareho ang product
    $pids = $_POST['product_id'] ?? [];
    $qtys = $_POST['quantity'] ?? [];
    $items = [];
    if (is_array($pids) && is_array($qtys)) {
        foreach ($pids as $i => $pid) {
            $pid = (int)$pid;
            if ($pid <= 0) continue;
            $qty = filter_var($qtys[$i] ?? 0, FILTER_VALIDATE_INT);
            $posted_rows[] = [$pid, ($qty !== false && $qty > 0) ? $qty : 1];
            if ($qty === false || $qty <= 0) {
                $errors[] = 'Ang quantity ay dapat buong numero na hindi bababa sa 1.';
                continue;
            }
            $items[$pid] = ($items[$pid] ?? 0) + $qty;
        }
    }

    if ($sel_customer <= 0) $errors[] = 'Pumili ng customer.';
    if ($sel_employee <= 0) $errors[] = 'Pumili ng employee.';
    if (!$items) $errors[] = 'Magdagdag ng kahit isang product.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Siguraduhing may customer at employee
            $chk = $pdo->prepare('SELECT COUNT(*) FROM customers WHERE customer_id = ?');
            $chk->execute([$sel_customer]);
            if (!$chk->fetchColumn()) throw new RuntimeException('Hindi nahanap ang customer.');
            $chk = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE employee_id = ?');
            $chk->execute([$sel_employee]);
            if (!$chk->fetchColumn()) throw new RuntimeException('Hindi nahanap ang employee.');

            $pdo->prepare('INSERT INTO orders (customer_id, employee_id, order_date, status) VALUES (?, ?, NOW(), ?)')
                ->execute([$sel_customer, $sel_employee, 'Pending']);
            $order_id = (int)$pdo->lastInsertId();

            $lock   = $pdo->prepare('SELECT product_name, price, stock_quantity FROM products WHERE product_id = ? FOR UPDATE');
            $insert = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            $deduct = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?');

            ksort($items); // parehong pagkakasunod-sunod para iwas deadlock
            foreach ($items as $pid => $qty) {
                $lock->execute([$pid]);
                $p = $lock->fetch();
                if (!$p) throw new RuntimeException('May product na hindi na nahanap.');
                if ((int)$p['stock_quantity'] < $qty) {
                    throw new RuntimeException('Kulang ang stock ng "' . $p['product_name'] . '" (available: ' . (int)$p['stock_quantity'] . ').');
                }
                // Ang presyo ay kinukuha sa database (hindi sa form) at sine-save bilang unit_price
                $insert->execute([$order_id, $pid, $qty, $p['price']]);
                $deduct->execute([$qty, $pid, $qty]);
                if ($deduct->rowCount() !== 1) throw new RuntimeException('Hindi maibawas ang stock ng "' . $p['product_name'] . '".');
            }

            $pdo->commit();
            flash_set('success', 'Na-save ang Order #' . $order_id . '.');
            redirect('view.php?id=' . $order_id);
        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $ex->getMessage();
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'May problema sa database. Subukan ulit.';
        }
    }
}

if (!$posted_rows) $posted_rows = [[0, 1]];

include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="card-header">Create Order</div>
    <div class="card-body">
        <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach (array_unique($errors) as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <?php if (!$customers || !$employees || !$products): ?>
            <div class="alert alert-warning">
                Kailangan munang may kahit isang <strong>customer</strong>, <strong>employee</strong>, at <strong>product</strong> bago makagawa ng order.
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Customer *</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Pumili ng customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)$c['customer_id'] ?>" <?= $sel_customer === (int)$c['customer_id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . $c['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Assigned Employee *</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">-- Pumili ng employee --</option>
                        <?php foreach ($employees as $em): ?>
                            <option value="<?= (int)$em['employee_id'] ?>" <?= $sel_employee === (int)$em['employee_id'] ? 'selected' : '' ?>><?= e($em['first_name'] . ' ' . $em['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <label class="form-label">Mga Produkto *</label>
            <div id="items">
                <?php foreach ($posted_rows as $row) echo item_row($products, $row[0], $row[1]); ?>
            </div>
            <template id="item-template"><?= item_row($products) ?></template>

            <button type="button" id="add-item" class="btn btn-outline-navy btn-sm mb-3"><i class="bi bi-plus-lg"></i> Add another product</button>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <div class="fs-5">Total: <strong id="grand-total">₱0.00</strong></div>
                <small class="text-muted">Presyo ay kukunin sa database pagka-save.</small>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-navy"><i class="bi bi-check-lg"></i> Place Order</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
