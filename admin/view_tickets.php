<?php
// admin/view-ticket.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // ← important if using $_SESSION

require_once __DIR__ . '/compents/header.php';       // fix typo if needed: components/
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . '/models/NotificationModel.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/controllers/UserController.php';



$ticketModel       = new TicketModel($pdo);
$notificationModel = new NotificationModel($pdo);

$agentRoleId = 2;
$agentsStmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE role_id = ?");
$agentsStmt->execute([$agentRoleId]);
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);

$ticket_ref = trim($_GET['ref'] ?? '');
$ticket_id  = (int) ($_GET['id']  ?? 0);

if (empty($ticket_ref) && $ticket_id === 0) {
    $_SESSION['error'] = "No ticket specified.";
    header("Location: dashboard.php"); // or tickets.php, notifications.php
    exit;
}


$ticket = $ticketModel->findTicket($ticket_ref, );

if (!$ticket) {
    $_SESSION['error'] = "Ticket not found.";
    header("Location: tickets.php");
    exit;
}


$ticketModel->markTicketAsRead($ticket['ticket_id']);
$notificationModel->markRelatedAsRead($ticket['ticket_id'], $ticket['reference'] ?? '');

$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status   = trim($_POST['status']   ?? $ticket['status']);
    $new_priority = trim($_POST['priority'] ?? $ticket['priority']);
    $note         = trim($_POST['note']     ?? '');

    $valid_statuses   = ['open', 'in_progress', 'on_hold', 'closed'];
    $valid_priorities = ['low', 'medium', 'high', 'urgent'];

    $updated = false;

    if (in_array($new_status, $valid_statuses) && $new_status !== $ticket['status']) {
        $ticketModel->updateStatus($ticket['id'], $new_status);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= htmlspecialchars($ticket['reference'] ?? $ticket['id'] ?? '—') ?> - Ticketing System</title>
    <link href="assets/css/app.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid py-4">

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $success_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                Ticket: <?= htmlspecialchars($ticket['reference'] ?? 'ID #' . ($ticket['id'] ?? '—')) ?>
            </h4>
            <span class="badge bg-light text-dark fs-6">
                <?= ucfirst($ticket['status'] ?? 'unknown') ?>
            </span>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-8">
                    <h5 class="mb-3"><?= htmlspecialchars($ticket['title'] ?? '—') ?></h5>
                    <div class="border rounded p-3 bg-light">
                        <?= nl2br(htmlspecialchars($ticket['description'] ?? 'No description')) ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3">Ticket Details</h6>
                            <p><strong>Submitted by:</strong><br><?= htmlspecialchars($ticket['email'] ?? '—') ?></p>
                            <p><strong>Priority:</strong><br>
                                <span class="badge bg-<?= 
                                    ($ticket['priority'] ?? '') === 'urgent' ? 'danger' : 
                                    ($ticket['priority'] === 'high'   ? 'warning' : 
                                    ($ticket['priority'] === 'medium' ? 'info'    : 'secondary'))
                                ?>">
                                    <?= ucfirst($ticket['priority'] ?? '—') ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong><br>
                                <?= $ticket['created_at'] ? date('d M Y • H:i', strtotime($ticket['created_at'])) : '—' ?>
                            </p>
                            <p><strong>Last updated:</strong><br>
                                <?= ($ticket['updated_at'] ?? $ticket['created_at']) 
                                    ? date('d M Y • H:i', strtotime($ticket['updated_at'] ?? $ticket['created_at'])) 
                                    : '—' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <form method="post" class="mt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Change Status</label>
                        <select name="status" class="form-select">
                            <option value="open"        <?= ($ticket['status'] ?? '') === 'open'        ? 'selected' : '' ?>>Open</option>
                            <option value="in_progress" <?= ($ticket['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="on_hold"     <?= ($ticket['status'] ?? '') === 'on_hold'     ? 'selected' : '' ?>>On Hold</option>
                            <option value="closed"      <?= ($ticket['status'] ?? '') === 'closed'      ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Change Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low"    <?= ($ticket['priority'] ?? '') === 'low'    ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= ($ticket['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high"   <?= ($ticket['priority'] ?? '') === 'high'   ? 'selected' : '' ?>>High</option>
                            <option value="urgent" <?= ($ticket['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Update Ticket
                        </button>
                    </div>
                </div>

                <!-- Optional internal note -->
                <div class="mt-3">
                    <label class="form-label">Internal Note (not visible to customer yet)</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Add internal comments or next steps..."></textarea>
                </div>
                 
            </form>
        </div>

        <div class="card-footer text-muted text-center">
            Reference: <?= htmlspecialchars($ticket['reference'] ?? '—') ?> 
            <?php if (!empty($ticket['id'])): ?>
                • ID: <?= $ticket['id'] ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// footer if you have one
// include_once __DIR__ . '/components/footer.php'; 
?>

</body>
</html>