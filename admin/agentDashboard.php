<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

 include_once __DIR__ . '/components/header.php'; 



$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Access denied. Please log in.");
}

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    die('Access denied. Only agents can access this page.');
}

$agent_id = $user_id; // the logged-in agent ID
