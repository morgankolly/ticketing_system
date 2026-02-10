<?php
<<<<<<< HEAD

=======
>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

<<<<<<< HEAD
include_once __DIR__ . '/compents/header.php'; 
include_once __DIR__ . '/helpers/functions.php';
include_once __DIR__ . '/config/connection.php';
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$notif_stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
$ticket_stmt = $pdo->query("SELECT * FROM tickets WHERE is_read = 0 ORDER BY created_at DESC");
$new_tickets = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);
$new_ticket_count = count($new_tickets);
$pdo->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
=======
include_once __DIR__ . '/components/header.php';

// --- Fetch notifications ---
$notif_stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch new tickets ---
$ticket_stmt = $pdo->query("SELECT * FROM tickets WHERE is_read = 0 ORDER BY created_at DESC");
$new_tickets = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);
$new_ticket_count = count($new_tickets);

// --- Optionally mark all notifications as read when page is loaded ---
$pdo->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
// Optionally mark tickets as read when viewed
>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)
$pdo->query("UPDATE tickets SET is_read = 1 WHERE is_read = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications & Tickets</title>
   
    <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
	<meta name="author" content="AdminKit">
	<meta name="keywords"
		content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="img/icons/icon-48x48.png" />

	<link rel="canonical" href="https://demo-basic.adminkit.io/" />

	<title>Ticketing System</title>

	<link href="assets/css/app.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
</head>
<body>

<h3>Notifications 
    <?php if(count($notifications) > 0): ?>
        <span class="indicator"><?= count($notifications) ?></span>
    <?php endif; ?>
</h3>

<?php if (!empty($notifications)): ?>
    <ul>
        <?php foreach ($notifications as $notif): ?>
            <li class="<?= $notif['is_read'] == 0 ? 'unread' : '' ?>">
                <?= htmlspecialchars($notif['message']) ?><br>
                <small><?= $notif['created_at'] ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No notifications</p>
<?php endif; ?>

<h3>New Tickets 
    <?php if($new_ticket_count > 0): ?>
        <span class="indicator"><?= $new_ticket_count ?></span>
    <?php endif; ?>
</h3>

<?php if (!empty($new_tickets)): ?>
    <ul>
        <?php foreach ($new_tickets as $ticket): ?>
            <li class="unread">
                <strong><?= htmlspecialchars($ticket['title']) ?></strong><br>
                <?= htmlspecialchars($ticket['description']) ?><br>
                <small>Submitted by: <?= htmlspecialchars($ticket['email']) ?> | <?= $ticket['created_at'] ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No new tickets</p>
<?php endif; ?>

</body>
</html>
