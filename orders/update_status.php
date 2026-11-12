<?php
// orders/update_status.php
// Binabago ang status ng order. Kapag Cancelled, ibinabalik ang stock (sa loob ng transaction).
// Ang Cancelled na order ay hindi na puwedeng ibalik para hindi magulo ang inventory.
require __DIR__ . '/../includes/auth.php';
require_login('../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_check();

$order_id = (int)($_POST['order_id'] ?? 0);
$new      = $_POST['status'] ?? '';

if (!in_array($new, ORDER_STATUSES, true)) {
    flash_set('danger', 'Maling status.');
    redirect('view.php?id=' . $order_id);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT status FROM orders WHERE order_id = ? FOR UPDATE');
    $stmt->execute([$order_id]);
    $current = $stmt->fetchColumn();

    if ($current === false) {
        $pdo->rollBack();
        flash_set('danger', 'Hindi nahanap ang order.');
        redirect('index.php');
    }
    if ($current === 'Cancelled') {
        $pdo->rollBack();
        flash_set('danger', 'Cancelled na ang order. Hindi na ito puwedeng baguhin.');
        redirect('view.php?id=' . $order_id);
    }

    if ($new === 'Cancelled') {
        // Ibalik ang stock ng bawat item
        $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? ORDER BY product_id');
        $items->execute([$order_id]);
        $restore = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?');
        foreach ($items->fetchAll() as $it) {
            $restore->execute([(int)$it['quantity'], (int)$it['product_id']]);
        }
    }

    $pdo->prepare('UPDATE orders SET status = ? WHERE order_id = ?')->execute([$new, $order_id]);
    $pdo->commit();

    flash_set('success', $new === 'Cancelled' ? 'Na-cancel ang order at naibalik ang stock.' : 'Na-update ang status.');
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_set('danger', 'May problema sa database. Walang binago.');
}
redirect('view.php?id=' . $order_id);
