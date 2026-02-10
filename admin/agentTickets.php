<<<<<<< HEAD
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

=======
>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assigned Tickets</title>
<<<<<<< HEAD
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
=======
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #f4f4f4; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Tickets Assigned to You</h1>

    <?php if (empty($tickets)): ?>
        <p>No tickets assigned to you yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Submitted By</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)
                    <tr>
                        <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                        <td><?= htmlspecialchars($ticket['title']) ?></td>
                        <td><?= htmlspecialchars($ticket['description']) ?></td>
<<<<<<< HEAD
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
=======
                        <td><?= htmlspecialchars($ticket['submitted_by']) ?></td>
                        <td><?= htmlspecialchars($ticket['status']) ?></td>
                        <td><?= htmlspecialchars($ticket['priority']) ?></td>
                        <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)
