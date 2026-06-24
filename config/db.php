<?php
// MySQL Database Connection Configuration (Exclusive)
$mysql_host = getenv('MYSQL_HOST') ?: '127.0.0.1';
$mysql_db   = getenv('MYSQL_DATABASE') ?: 'madu';
$mysql_user = getenv('MYSQL_USER') ?: 'root';
$mysql_pass = getenv('MYSQL_PASSWORD') ?: 'root';
$charset    = 'utf8mb4';

$dsn = "mysql:host=$mysql_host;dbname=$mysql_db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $mysql_user, $mysql_pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed (MySQL): " . $e->getMessage());
}
?>
