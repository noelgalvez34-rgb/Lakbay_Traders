<?php
// employees/edit.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Edit Employee';
$active = 'employees';
$form_title = 'Edit Employee';

$id = get_id();
$stmt = $pdo->prepare('SELECT * FROM employees WHERE employee_id = ?');
$stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) {
    flash_set('danger', 'Hindi nahanap ang employee.');
    redirect('index.php');
}
$emp['role'] = (string)$emp['role'];
$emp['hire_date'] = (string)$emp['hire_date'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['first_name', 'last_name', 'role', 'hire_date'] as $k) {
        $emp[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $errors = validate_employee($emp);
    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE employees SET first_name = ?, last_name = ?, role = ?, hire_date = ? WHERE employee_id = ?');
        $stmt->execute([$emp['first_name'], $emp['last_name'], $emp['role'], $emp['hire_date'] !== '' ? $emp['hire_date'] : null, $id]);
        flash_set('success', 'Na-update na ang employee.');
        redirect('index.php');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_form.php';
include __DIR__ . '/../includes/footer.php';
