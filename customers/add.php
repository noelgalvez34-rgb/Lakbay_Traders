<?php
// customers/add.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Add Customer';
$active = 'customers';
$form_title = 'New Customer';

$c = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'address' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($c as $k => $_) {
        $c[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $errors = validate_customer($c);
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO customers (first_name, last_name, email, phone, address) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$c['first_name'], $c['last_name'], $c['email'], $c['phone'], $c['address']]);
        flash_set('success', 'Naidagdag na ang customer.');
        redirect('index.php');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_form.php';
include __DIR__ . '/../includes/footer.php';
