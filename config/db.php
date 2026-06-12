<?php
// MySQL Database Configuration (Preferred)
$mysql_host = getenv('MYSQL_HOST') ?: 'localhost';
$mysql_db   = getenv('MYSQL_DATABASE') ?: 'madu_batu_meka';
$mysql_user = getenv('MYSQL_USER') ?: 'root';
$mysql_pass = getenv('MYSQL_PASSWORD') ?: '';

// SQLite Database Path (Fallback for Sandbox)
$sqlite_path = __DIR__ . '/../data/database.sqlite';

try {
    if (getenv('MYSQL_HOST')) {
        // Attempt MySQL connection if host is provided
        $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_db;charset=utf8mb4", $mysql_user, $mysql_pass);
    } else {
        // Fallback to SQLite
        $pdo = new PDO("sqlite:" . $sqlite_path);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If MySQL failed, try SQLite directly as last resort
    try {
        $pdo = new PDO("sqlite:" . $sqlite_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        die("Database connection failed: " . $e2->getMessage());
    }
}
?>
