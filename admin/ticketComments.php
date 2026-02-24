<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/connection.php';
require_once 'models/TicketModel.php';
require_once 'compents/agentHeader.php';
require_once 'helpers/functions.php';
require_once 'controllers/TicketController.php';
require_once 'controllers/UserController.php';

$ticketModel = new TicketModel($pdo);
$ticketRef = $_GET['ticket_ref'] ?? null;



$ticket = $ticketModel->getTicketByReference($ticketRef);
$comments = $ticketModel->getTicketCommentsThread($ticketRef);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
    <meta charset="UTF-8">
    <title>Ticket #<?= htmlspecialchars($ticket['id'] ?? '') ?> Comments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">

        <h2>Ticket #<?= htmlspecialchars($ticket['reference'] ?? '') ?> -
            <?= htmlspecialchars($ticket['title'] ?? '') ?></h2>

        <p><strong>Description:</strong> <?= htmlspecialchars($ticket['description'] ?? '') ?></p>
        <p>
            <strong>Status:</strong> <?= htmlspecialchars($ticket['status'] ?? '') ?> |
            <strong>Priority:</strong> <?= ucfirst(htmlspecialchars($ticket['priority'] ?? '')) ?> |
            <strong>Assigned By:</strong> <?= htmlspecialchars($ticket['assigned_by'] ?? 'System') ?>
        </p>

        <?php if (!empty($comments)): ?>

            <?php foreach ($comments as $comment): ?>

                <div class="comment-box" style="margin-bottom:15px; padding:10px; border:1px solid #ddd;">

                    <?php if (!empty($comment['agent_id'])): ?>
                        <strong>Agent:</strong>
                    <?php else: ?>
                        <strong>User (<?= htmlspecialchars($comment['commenter_email'] ?? 'Unknown') ?>):</strong>
                    <?php endif; ?>

                    <p><?= nl2br(htmlspecialchars($comment['comment'] ?? '')) ?></p>

                    <small><?= htmlspecialchars($comment['created_at'] ?? '') ?></small>

                </div>

            <?php endforeach; ?>

        <?php else: ?>
            <p>No comments yet.</p>
        <?php endif; ?>


        <form method="POST" action="ticketComments.php?ticket_ref=<?= urlencode($ticketRef) ?>">
            <input type="hidden" name="ticket_ref" value="<?= htmlspecialchars($ticketRef ?? '') ?>">
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