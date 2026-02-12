<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- AUTH GUARD (ONLY HERE) ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config/connection.php';
require_once 'models/TicketModel.php';
require_once 'compents/agentHeader.php';
require_once 'helpers/functions.php';
require_once 'controllers/TicketController.php';
$ticketId = $_GET['ticket_id'] ?? null;
if (!$ticketId || !is_numeric($ticketId)) {
    die("Invalid ticket ID");
}
$stmt = $pdo->prepare("
    SELECT ticket_comments.*, 
           COALESCE(users.user_name, ticket_comments.commenter_email, 'Agent') AS commenter_name
    FROM ticket_comments
    LEFT JOIN users ON ticket_comments.agent_id = users.user_id
    WHERE ticket_comments.ticket_id = :ticketId AND ticket_comments.parent_comment_id IS NULL
    ORDER BY ticket_comments.created_at ASC
");
$stmt->execute(['ticketId' => $ticketId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($comments as &$comment) {
    $stmt = $pdo->prepare("
        SELECT ticket_comments.*, users.user_name AS commenter_name
        FROM ticket_comments
        LEFT JOIN users ON ticket_comments.agent_id = users.user_id
        WHERE ticket_comments.parent_comment_id = :parentId
        ORDER BY ticket_comments.created_at ASC
    ");
    $stmt->execute(['parentId' => $comment['comment_id']]);
    $comment['replies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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

    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Your Comment</label>
            <textarea name="comment" class="form-control" rows="4" required></textarea>
        </div>

        <button type="submit" name="submit_comment" class="btn btn-primary">
            Add Comment
        </button>
    </form>

    <a href="agentTickets.php" class="btn btn-secondary mt-3">Back to Assigned Tickets</a>

</div>
</body>
</html>
