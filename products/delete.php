<?php
// products/delete.php - POST lang, may CSRF check
require __DIR__ . '/../includes/auth.php';
require_login('../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE product_id = ?');
    $stmt->execute([$id]);
    flash_set($stmt->rowCount() ? 'success' : 'danger', $stmt->rowCount() ? 'Nabura na ang product.' : 'Hindi nahanap ang product.');
} catch (PDOException $ex) {
    if ($ex->getCode() === '23000') {
        flash_set('danger', 'Hindi mabura: ginamit na ang product na ito sa mga order.');
    } else {
        flash_set('danger', 'May error sa pagbura.');
    }
}
redirect('index.php');
