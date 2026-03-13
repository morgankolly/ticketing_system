<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/compents/header.php';  // ← typo? should be components/header.php ?
include_once __DIR__ . '/helpers/functions.php';
include_once __DIR__ . '/config/connection.php';

// Get all notifications (newest first)
$stmt = $pdo->query("
    SELECT * 
    FROM notifications 
    ORDER BY created_at DESC
");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark all as read (you can make this more selective later)
$pdo->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");


$ticket_stmt = $pdo->query("
    SELECT * 
    FROM tickets 
    WHERE is_read = 0 
    ORDER BY created_at DESC
");
$new_tickets = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);
$new_ticket_count = count($new_tickets);

// Mark tickets as read (optional — many systems keep them unread until viewed)
$pdo->query("UPDATE tickets SET is_read = 1 WHERE is_read = 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications & Tickets - Ticketing System</title>
    <link href="assets/css/app.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">

    <h3>
        Notifications
        <?php if (count($notifications) > 0): ?>
            <span class="indicator badge bg-danger"><?= count($notifications) ?></span>
        <?php endif; ?>
    </h3>

    <?php if (!empty($notifications)): ?>
        <ul class="list-group list-group-flush notification-list">
            <?php foreach ($notifications as $notif): ?>
                <li class="list-group-item <?= $notif['is_read'] == 0 ? 'bg-light fw-bold' : '' ?>">
                    <?php
                    // Decide link — adjust column name to match your table
                    $link = '#'; // fallback
                    if (!empty($notif['related_ticket_ref'])) {
                        $link = "view_tickets.php?ref=" . urlencode($notif['related_ticket_ref']);
                    } elseif (!empty($notif['related_ticket_id'])) {
                        $link = "view-ticket.php?id=" . (int)$notif['related_ticket_id'];
                    }
                    ?>

                    <?php if ($link !== '#'): ?>
                        <a href="<?= htmlspecialchars($link) ?>" class="text-decoration-none stretched-link">
                    <?php endif; ?>

                        <?= htmlspecialchars($notif['message']) ?>

                        <small class="d-block text-muted mt-1">
                            <?= date('M d, Y H:i', strtotime($notif['created_at'])) ?>
                        </small>

                    <?php if ($link !== '#'): ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">No notifications at the moment.</div>
    <?php endif; ?>

    <hr>

    <h3>
        New Tickets
        <?php if ($new_ticket_count > 0): ?>
            <span class="indicator badge bg-warning"><?= $new_ticket_count ?></span>
        <?php endif; ?>
    </h3>

    <?php if (!empty($new_tickets)): ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($new_tickets as $ticket): ?>
                <li class="list-group-item bg-light">
                    <a href="view_tickets.php?ref=<?= urlencode($ticket['reference']) ?>" class="text-decoration-none stretched-link">
                        <strong><?= htmlspecialchars($ticket['title']) ?></strong><br>
                        <?= nl2br(htmlspecialchars(substr($ticket['description'], 0, 120))) ?>...
                        <small class="d-block text-muted mt-1">
                            Submitted by: <?= htmlspecialchars($ticket['email']) ?> • 
                            <?= date('M d, Y H:i', strtotime($ticket['created_at'])) ?>
                        </small>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-secondary">No new unread tickets.</div>
    <?php endif; ?>

</div>

</body>
</html>