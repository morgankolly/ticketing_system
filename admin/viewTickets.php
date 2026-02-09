<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/models/TicketModel.php';
// Session check: only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Ticket ID check
if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    die("Ticket ID missing or invalid");
}
$ticketId = (int) $_GET['ticket_id'];

// Includes
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/compents/AgentHeader.php';  // fixed typo
require_once __DIR__ . '/controllers/TicketController.php';

// Instantiate controller (even if empty)
$ticketController = new TicketController($conn);

// Standalone function to fetch ticket (defined in controller file or here)
function getTicketById($conn, $ticketId) {
    $stmt = $conn->prepare("SELECT t.*, u.username AS assigned_by_username
                            FROM tickets t
                            LEFT JOIN users u ON t.assigned_by = u.user_id
                            WHERE t.ticket_id = ?");
    $stmt->execute([$ticketId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch ticket using the standalone function
$ticket = getTicketById($ticketController->conn, $ticketId);

if (!$ticket) {
    die("Ticket not found");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Ticket Details</h2>
    <table class="table table-bordered mt-4">
        <tr>
            <th>ID</th>
            <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
        </tr>
        <tr>
            <th>Title</th>
            <td><?= htmlspecialchars($ticket['title']) ?></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><?= htmlspecialchars($ticket['description']) ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= htmlspecialchars($ticket['status']) ?></td>
        </tr>
        <tr>
            <th>Priority</th>
            <td><?= htmlspecialchars($ticket['priority']) ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?= htmlspecialchars($ticket['created_at']) ?></td>
        </tr>
        <tr>
            <th>Assigned By</th>
            <td><?= htmlspecialchars($ticket['assigned_by_username'] ?? 'System') ?></td>
        </tr>
    </table>

    <a href="javascript:history.back()" class="btn btn-secondary mt-3">Back</a>
</div>
</body>
</html>
