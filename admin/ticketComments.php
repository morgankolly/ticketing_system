<?php

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . '/compents/agentHeader.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/auto_close_tickets.php';
require_once __DIR__ . '/helpers/functions.php';

$ticketModel = new TicketModel($pdo);

// 1️⃣ Get reference
$ticketRef = trim($_GET['ticket_ref'] ?? $_GET['reference'] ?? '');

if ($ticketRef === '') {
    die("Ticket reference missing or invalid.");
}

// 2️⃣ Fetch ticket
$ticket = $ticketModel->getTicketByReference($ticketRef);

if (!$ticket) {
    die("Ticket not found for reference: " . htmlspecialchars($ticketRef));
}

// ✅ Extract ID
$ticketId = (int) $ticket['ticket_id'];

// 3️⃣ Fetch comments
$comments = $ticketModel->getTicketCommentsThread($ticketRef);

if (!is_array($comments)) {
    $comments = [];
}
$stmt = $pdo->prepare("
    SELECT file_name, original_name, file_size 
FROM ticket_attachments 
WHERE ticket_id = :ticket_id
");
$stmt->execute(['ticket_id' => $ticketId]);
$attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT * FROM ticket_attachments 
    WHERE ticket_id = ?
");
$stmt->execute([$ticketId]);
$allAttachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$attachmentsByComment = [];
foreach ($allAttachments as $att) {
    $attachmentsByComment[$att['comment_id']][] = $att;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= htmlspecialchars($ticket['reference'] ?? '') ?> Comments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container-fluid p-4">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="align-middle me-2" data-feather="ticket"></i>
                        <span class="ticket-reference me-3"><?= htmlspecialchars($ticket['reference'] ?? '') ?></span>
                        <?= htmlspecialchars($ticket['title'] ?? '') ?>
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Description:</strong></p>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($ticket['description'] ?? '')) ?></p>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <p class="mb-2"><strong>Attachments:</strong></p>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <p class="mb-2"><strong>Attachments:</strong></p>

                                    <?php foreach ($comments as $comment): ?>

                                        <div class="mb-3 p-2 border rounded">

                                            <!-- Comment text -->
                                            <p class="mb-0 mt-2">
                                                <?= nl2br(htmlspecialchars($comment['comment'] ?? '')) ?>
                                            </p>

                                            <?php
                                            $stmtAtt = $pdo->prepare("
                                                SELECT file_name, original_name 
                                                FROM ticket_attachments 
                                                WHERE comment_id = ?
                                            ");
                                            $stmtAtt->execute([$comment['comment_id']]);
                                            $commentFiles = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>

                                            <?php if (!empty($commentFiles)): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <?php foreach ($commentFiles as $cFile): ?>
                                                        <a href="/ticketing/ticketing_system/uploads/tickets/<?= htmlspecialchars($cFile['file_name']) ?>"
                                                            target="_blank"
                                                            class="d-inline-flex align-items-center gap-1 me-2 text-decoration-none small">
                                                            📎 <?= htmlspecialchars($cFile['original_name']) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                        </div>

                                    <?php endforeach; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <span
                                class="badge status-<?= strtolower(str_replace(' ', '-', $ticket['status'] ?? 'open')) ?> me-2">
                                <?= htmlspecialchars(ucfirst($ticket['status'] ?? 'Open')) ?>
                            </span>
                            <span class="badge priority-<?= strtolower($ticket['priority'] ?? 'medium') ?> me-2">
                                <?= htmlspecialchars(ucfirst($ticket['priority'] ?? 'Medium')) ?>
                            </span>
                        </p>
                        <p class="text-muted mb-0">
                            <small>Created: <?= date('M d, Y H:i', strtotime($ticket['created_at'] ?? 'now')) ?></small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Comments (<?= count($comments) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($comments)): ?>
                    <div class="comments-container">
                        <?php foreach ($comments as $comment): ?>
                            <div
                                class="ticket-comment <?= !empty($comment['agent_id']) ? 'agent-comment' : 'customer-comment' ?>">
                                <div class="ticket-comment-header">
                                    <div class="ticket-comment-author">
                                        <?php if (!empty($comment['agent_id'])): ?>
                                            <i class="align-middle me-1" data-feather="user-check"
                                                style="width: 14px; height: 14px;"></i>
                                            <span class="badge bg-primary me-2">Agent</span>
                                            <strong><?= htmlspecialchars($comment['commenter_name'] ?? 'Agent') ?></strong>
                                        <?php else: ?>
                                            <i class="align-middle me-1" data-feather="user" style="width: 14px; height: 14px;"></i>
                                            <span class="badge bg-success me-2">Customer</span>
                                            <strong><?= htmlspecialchars($comment['commenter_email'] ?? 'Unknown User') ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ticket-comment-time">
                                        <i class="align-middle me-1" data-feather="clock"
                                            style="width: 12px; height: 12px;"></i>
                                        <?= date('M d, Y H:i', strtotime($comment['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                                <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($comment['comment'] ?? '')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No comments yet. Be the first to comment!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle me-2" data-feather="message-circle"></i>
                    Add Comment
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="ticketComments.php?ticket_ref=<?= urlencode($ticketRef) ?>">
                    <input type="hidden" name="ticket_ref" value="<?= htmlspecialchars($ticketRef ?? '') ?>">
                    <div class="mb-3">
                        <label class="form-label">Your Comment</label>
                        <textarea name="comment" class="form-control" rows="5"
                            placeholder="Type your response here..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_comment" class="btn btn-primary">
                            <i class="align-middle" data-feather="send"></i> Submit Comment
                        </button>
                        <a href="agentTickets.php" class="btn btn-secondary">
                            <i class="align-middle" data-feather="arrow-left"></i> Back to Tickets
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>

</html>