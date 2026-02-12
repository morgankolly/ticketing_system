<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/compents/AgentHeader.php';
// Session check: only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if ($_SESSION['role_id'] === 'agent' && $ticket['status'] === 'open') {
    $TicketModel->markTicketAsInProgress($ticketId, $_SESSION['user_id']);
    $ticket['status'] = 'in_progress'; // reflect immediately in UI
}

$ticketId = (int) $_GET['ticket_id'];

// Includes
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/compents/AgentHeader.php';  // fixed typo
require_once __DIR__ . '/controllers/TicketController.php';

function getTicketById($conn, $ticketId) {
    $stmt = $conn->prepare("SELECT tickets.*, users.user_name AS assigned_by_user_name
                            FROM tickets
                            LEFT JOIN users ON tickets.user_id = users.user_id
                            WHERE tickets.ticket_id = ?");
    $stmt->execute([$ticketId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch ticket using the standalone function
$ticket = getTicketById($pdo, $ticketId);

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
    <tr>
        <th>Comments</th>
     
        <td>
            <a href="ticketComments.php?ticket_id=<?= urlencode($ticket['ticket_id']) ?>" class="btn btn-sm btn-primary">
                View / Reply Comments
            </a>
        </td>
    </tr>
</table>


    <a href="javascript:history.back()" class="btn btn-secondary mt-3">Back</a>
</div>
</body>
</html>
