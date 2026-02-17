<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/connection.php'; // Make sure this works
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . '/compents/agentHeader.php';
$TicketModel = new TicketModel($pdo);
$UserModel = new UserModel($pdo);
$agentId = $_SESSION['user_id'];
$assignedTickets = $TicketModel->getTicketsByUser($agentId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assigned Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #f4f4f4; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Tickets Assigned to Me</h2>

    <table class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>Reference</th>
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
                        <td><?= htmlspecialchars($ticket['reference']) ?></td>
                        <td><?= htmlspecialchars($ticket['title']) ?></td>
                        <td><?= htmlspecialchars($ticket['description']) ?></td>
                        <td><?= htmlspecialchars($ticket['status']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($ticket['priority'])) ?></td>
                        <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                        <td><?= htmlspecialchars($ticket['assigned_by'] ?? 'System') ?></td>
                        <td>
                            <a href="viewTickets.php?ticket_ref=<?= urlencode($ticket['reference']) ?>" 
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

</html>
