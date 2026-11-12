<?php
// includes/header.php
// Kailangan ng bawat page ang $base (hal. '' o '../'), $page_title, at $active bago i-include ito.
$base       = $base ?? '';
$page_title = $page_title ?? 'Lakbay Traders';
$active     = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> | Lakbay Traders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= $base ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main">
<?php include __DIR__ . '/navbar.php'; ?>
<div class="content">
<?php flash_show(); ?>
