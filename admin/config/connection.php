<?php
require_once __DIR__ . '/../helpers/functions.php';
$DB_HOST = $_ENV['DB_HOST'] ?? null;
$DB_USERNAME = $_ENV['DB_USERNAME'] ?? null;
$DB_PASSWORD = $_ENV['DB_PASSWORD'] ?? null;
$DB_DATABASE = $_ENV['DB_DATABASE'] ?? null;

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset={$_ENV['DB_CHARSET']}",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
