<?php
// index.php - ididirekta sa dashboard (o login kung hindi pa naka-login)
require __DIR__ . '/includes/auth.php';
redirect(empty($_SESSION['user_id']) ? 'login.php' : 'dashboard.php');
