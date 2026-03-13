<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/TicketModel.php';

$TicketModel = new TicketModel($pdo);
$UserModel = new UserModel($pdo);
$notificationModel = new notificationModel($pdo);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status   = trim($_POST['status']   ?? $ticket['status']);
    $new_priority = trim($_POST['priority'] ?? $ticket['priority']);
    $note         = trim($_POST['note']     ?? '');

    $valid_statuses   = ['open', 'in_progress', 'on_hold', 'closed'];
    $valid_priorities = ['low', 'medium', 'high', 'urgent'];

    $updated = false;

    if (in_array($new_status, $valid_statuses) && $new_status !== $ticket['status']) {
        $ticketModel->updateStatus($ticket['ticket_id'], $new_status);
        $success_msg = "Status updated to <strong>" . htmlspecialchars($new_status) . "</strong>";
        $updated = true;
    }

    if (
    isset($ticket['priority']) &&
    in_array($new_priority, $valid_priorities) &&
    $new_priority !== $ticket['priority']
) {
    $ticketModel->updatePriority($ticket['reference'], $new_priority);
    $success_msg = ($success_msg ?? '') . ($success_msg ? ' & ' : '') . 'Priority updated.';
    $updated = true;
}

    if (!empty($note)) {
        $success_msg .= "<br>Internal note recorded (not yet saved to database).";
    }

    if ($updated) {
    $ticket = $ticketModel->findTicket($ticket['reference']);
}

}
