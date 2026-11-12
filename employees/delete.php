<?php
// employees/delete.php - POST lang, may CSRF check
require __DIR__ . '/../includes/auth.php';
require_login('../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
try {
    $stmt = $pdo->prepare('DELETE FROM employees WHERE employee_id = ?');
    $stmt->execute([$id]);
    flash_set($stmt->rowCount() ? 'success' : 'danger', $stmt->rowCount() ? 'Nabura na ang employee.' : 'Hindi nahanap ang employee.');
} catch (PDOException $ex) {
    if ($ex->getCode() === '23000') {
        flash_set('danger', 'Hindi mabura: may mga order na naka-assign sa employee na ito.');
    } else {
        flash_set('danger', 'May error sa pagbura.');
    }
}
redirect('index.php');
