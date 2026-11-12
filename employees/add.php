<?php
// employees/add.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Add Employee';
$active = 'employees';
$form_title = 'New Employee';

$emp = ['first_name' => '', 'last_name' => '', 'role' => '', 'hire_date' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($emp as $k => $_) {
        $emp[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $errors = validate_employee($emp);
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO employees (first_name, last_name, role, hire_date) VALUES (?, ?, ?, ?)');
        $stmt->execute([$emp['first_name'], $emp['last_name'], $emp['role'], $emp['hire_date'] !== '' ? $emp['hire_date'] : null]);
        flash_set('success', 'Naidagdag na ang employee.');
        redirect('index.php');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_form.php';
include __DIR__ . '/../includes/footer.php';
