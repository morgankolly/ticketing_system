<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/connection.php';
require_once 'models/TicketModel.php';
require_once 'compents/agentHeader.php';
require_once 'helpers/functions.php';
require_once 'controllers/TicketController.php';

// -------------------------------
// GET TICKET REFERENCE FROM URL
// -------------------------------
$ticketRef = $_GET['ticket_ref'] ?? null;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= htmlspecialchars($ticket['id']) ?> Comments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2>Ticket #<?= htmlspecialchars($ticket['reference']) ?> - <?= htmlspecialchars($ticket['title']) ?></h2>

    <p><strong>Description:</strong> <?= htmlspecialchars($ticket['description']) ?></p>
    <p>
        <strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?> |
        <strong>Priority:</strong> <?= ucfirst(htmlspecialchars($ticket['priority'])) ?> |
        <strong>Assigned By:</strong> <?= htmlspecialchars($ticket['assigned_by'] ?? 'System') ?>
    </p>
<?php foreach ($comments as $comment): ?>
    <div class="comment border p-2 mb-2">
        <p><strong><?= htmlspecialchars($comment['commenter_name']) ?>:</strong></p>
        <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
        <p><small><?= $comment['created_at'] ?></small></p>

        <!-- Replies -->
        <?php if (!empty($comment['replies'])): ?>
            <div class="replies ms-4 mt-2">
                <?php foreach ($comment['replies'] as $reply): ?>
                    <p><strong><?= htmlspecialchars($reply['commenter_name']) ?>:</strong> <?= nl2br(htmlspecialchars($reply['comment'])) ?></p>
                    <p><small><?= $reply['created_at'] ?></small></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="ticket_ref" value="<?= htmlspecialchars($_GET['ticket_ref'] ?? '') ?>">

    <div class="mb-3">
        <label>Comment</label>
        <textarea name="comment" class="form-control" required></textarea>
    </div>

    <button type="submit" name="submit_comment" class="btn btn-primary">Submit</button>
</form>

    <a href="agentTickets.php" class="btn btn-secondary mt-3">Back to Assigned Tickets</a>

</div>
</body>
</html>
