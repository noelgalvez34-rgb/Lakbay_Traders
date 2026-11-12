<?php
// config/database.php
// Dito kumokonekta ang buong system sa database gamit ang PDO.

$db_host = 'localhost';
$db_name = 'lakbay_traders';
$db_user = 'root';   // PALITAN kung iba ang username mo sa phpMyAdmin
$db_pass = '';       // PALITAN kung may password ang database mo

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Pakisuri ang config/database.php (username/password) at siguraduhing naka-start ang database.');
}
