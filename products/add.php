<?php
// products/add.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Add Product';
$active = 'products';
$form_title = 'New Product';

$p = ['product_name' => '', 'description' => '', 'price' => '', 'stock_quantity' => '0'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($p as $k => $_) {
        $p[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $errors = validate_product($p);
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO products (product_name, description, price, stock_quantity) VALUES (?, ?, ?, ?)');
        $stmt->execute([$p['product_name'], $p['description'], number_format((float)$p['price'], 2, '.', ''), (int)$p['stock_quantity']]);
        flash_set('success', 'Naidagdag na ang product.');
        redirect('index.php');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_form.php';
include __DIR__ . '/../includes/footer.php';
