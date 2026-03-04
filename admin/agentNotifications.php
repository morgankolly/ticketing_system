<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/notificationModel.php';
require_once __DIR__ . '/compents/agentHeader.php';

$notificationModel = new NotificationModel($pdo);
$unreadCount = isset($unreadCount) ? $unreadCount : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ticketing System - Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <main class="content">
        <div class="container-fluid p-4">

            <div class="d-flex align-items-center mb-4 gap-2">
                <h1 class="h3 mb-0"><strong>Notifications</strong></h1>
                <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-danger"><?= $unreadCount ?> new</span>
                <?php endif; ?>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="alert alert-secondary">No notifications yet.</div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notif): ?>
                        <div class="list-group-item list-group-item-action <?= $notif['is_read'] == 0 ? 'list-group-item-warning' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="bi bi-envelope me-2"></i>
                                    <?= htmlspecialchars($notif['message']) ?>
                                    <?php if (!empty($notif['ticket_id'])): ?>
                                        &mdash;
                                        <a href="viewTickets.php?ticket_ref=<?= urlencode($notif['reference'] ?? '') ?>">
                                            View Ticket
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted ms-3 text-nowrap"><?= htmlspecialchars($notif['created_at']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>