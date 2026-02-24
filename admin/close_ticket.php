<?php
// Show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/connection.php'; // adjust if your config is outside admin
require_once __DIR__ . '/models/TicketModel.php';

$ticketRef = $_GET['ref'] ?? '';
$token = $_GET['token'] ?? '';

if (!$ticketRef || !$token) {
    exit("Invalid request.");
}

$ticketModel = new TicketModel($pdo);

// Try to close the ticket
$result = $ticketModel->closeTicketByEmail($ticketRef, $token);

if ($result) {
    echo "<script>
                alert('Ticket closed successfully thank you!');
                window.location.href='../index.php';
              </script>";
} else {
    echo "Invalid link or ticket already closed.";
}