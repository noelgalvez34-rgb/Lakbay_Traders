<?php
// customers/edit.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Edit Customer';
$active = 'customers';
$form_title = 'Edit Customer';

$id = get_id();
$stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) {
    flash_set('danger', 'Hindi nahanap ang customer.');
    redirect('index.php');
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['first_name', 'last_name', 'email', 'phone', 'address'] as $k) {
        $c[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $errors = validate_customer($c);
    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE customer_id = ?');
        $stmt->execute([$c['first_name'], $c['last_name'], $c['email'], $c['phone'], $c['address'], $id]);
        flash_set('success', 'Na-update na ang customer.');
        redirect('index.php');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_form.php';
include __DIR__ . '/../includes/footer.php';
