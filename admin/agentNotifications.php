<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/compents/agentHeader.php';
require_once __DIR__ . '/models/NotificationModel.php';

$agent_id = (int) $_SESSION['user_id'];
$notificationModel = new NotificationModel($pdo); /* MARK ALL READ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all'])) {
    $notificationModel->markAllAsReadByAgent($agent_id);
    header("Location: agentNotifications.php");
    exit();
} /* MARK SINGLE READ */
if (isset($_GET['read'])) {
    $notif_id = (int) $_GET['read'];
    $stmt = $pdo->prepare(" UPDATE notifications SET is_read = 1 WHERE id = ? AND agent_id = ? ");
    $stmt->execute([$notif_id, $agent_id]);
    header("Location: agentNotifications.php");
    exit();
} /* FETCH */
$notifications = $notificationModel->getNotificationsByAgent($agent_id);
$unreadCount = $notificationModel->getUnreadCountByAgent($agent_id); 
?>
<!DOCTYPE html>
<html>

<head>
    <title>Agent Notifications</title>
    <link href="../assets/css/app.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h3> 🔔 Your Notifications <?php if ($unreadCount > 0): ?> <span class="badge-count"><?= $unreadCount ?></span>
            <?php endif; ?> </h3> <?php if (!empty($notifications)): ?>
            <form method="POST" class="mb-3"> <button type="submit" name="mark_all" class="btn btn-primary btn-sm"> Mark all
                    as read </button> </form>
            <ul class="list-group"> <?php foreach ($notifications as $n): ?>
                    <li class="list-group-item <?= $n['is_read'] == 0 ? 'unread' : '' ?>"> <a
                            href="../view-ticket.php?ticket_ref=<?= urlencode($n['reference']) ?>&notif_id=<?= $n['id'] ?>">
                            <?= htmlspecialchars($n['message']) ?> <br> <small>
                                <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?> </small> </a>
                        <?php if ($n['is_read'] == 0): ?> <a href="?read=<?= $n['id'] ?>" class="btn btn-success btn-sm"> ✓ </a>
                        <?php endif; ?> </li> <?php endforeach; ?>
            </ul> <?php else: ?>
            <div class="alert alert-info"> No notifications found </div> <?php endif; ?>
    </div>
</body>

</html>