<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/notificationModel.php';
require_once __DIR__ . '/compents/agentHeader.php';

$notificationModel = new NotificationModel($pdo);
$unreadCount = isset($unreadCount) ? $unreadCount : 0;
$notificationModel = new NotificationModel($pdo);

// Fetch all notifications from DB
$notifications = $notificationModel->getAllNotifications() ?? [];
$unreadCount = $notificationModel->getUnreadCount() ?? 0;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ticketing System - Notifications</title>
        <link href="assets/css/app.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <main class="content">
        <div class="container-fluid p-4">

          <?php foreach ($notifications as $notif): ?>
    <div class="list-group-item list-group-item-action <?= $notif['is_read'] == 0 ? 'list-group-item-warning' : '' ?>">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <i class="bi bi-envelope me-2"></i>
                <?= htmlspecialchars($notif['message']) ?>

                <?php if (!empty($notif['reference'])): ?>
                    —
                    <a href="viewTickets.php?reference=<?= urlencode($notif['reference']) ?>">
                        View Ticket
                    </a>
                <?php endif; ?>
            </div>

            <small class="text-muted ms-3 text-nowrap">
                <?= htmlspecialchars($notif['created_at']) ?>
            </small>
        </div>
    </div>
<?php endforeach; ?>

        </div>
    </main>
</body>
</html>