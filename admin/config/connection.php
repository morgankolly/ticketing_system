<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Use getenv() or $_ENV safely
$host    = $_ENV['DB_HOST'] ?? '127.0.0.1';
$db      = $_ENV['DB_NAME'] ?? 'ticket_system';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
$user    = $_ENV['DB_USER'] ?? 'root';
$pass    = $_ENV['DB_PASS'] ?? '';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
