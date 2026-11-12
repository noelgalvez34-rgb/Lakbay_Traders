<?php
// products/edit.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Edit Product';
$active = 'products';
$form_title = 'Edit Product';

$id = get_id();
$stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) {
    flash_set('danger', 'Hindi nahanap ang product.');
    redirect('index.php');
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['product_name', 'description', 'price', 'stock_quantity'] as $k) {
        $p[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $errors = validate_product($p);
    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE products SET product_name = ?, description = ?, price = ?, stock_quantity = ? WHERE product_id = ?');
        $stmt->execute([$p['product_name'], $p['description'], number_format((float)$p['price'], 2, '.', ''), (int)$p['stock_quantity'], $id]);
        flash_set('success', 'Na-update na ang product.');
        redirect('index.php');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_form.php';
include __DIR__ . '/../includes/footer.php';
