<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
 include_once __DIR__ . '/compents/agentHeader.php';
// Ensure only agents can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: index.php");
    exit;
}

require_once 'config/connection.php'; // adjust path if needed
require_once 'controllers/TicketController.php'; // adjust path if needed



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assigned Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Tickets Assigned to Me</h2>
    <table class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Created At</th>
                <th>Assigned By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($assignedTickets)): ?>
                <tr>
                    <td colspan="8" class="text-center">No tickets assigned to you yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($assignedTickets as $ticket): ?>
                    <tr>
                        <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                        <td><?= htmlspecialchars($ticket['title']) ?></td>
                        <td><?= htmlspecialchars($ticket['description']) ?></td>
                        <td><?= htmlspecialchars($ticket['status']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($ticket['priority'])) ?></td>
                        <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                        <td><?= htmlspecialchars($ticket['assigned_by'] ?? 'System') ?></td>
                        <td>
<a href="/Ticketing-system/admin/viewTickets.php?ticket_id=<?= urlencode($ticket['ticket_id']) ?>"
   class="btn btn-sm btn-primary">
   View
</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
