<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/compents/header.php';

include_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/models/TicketModel.php';

$ticketModel = new TicketModel($pdo);


$agentRoleId = 2;
$agentsStmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE role_id = ?");
$agentsStmt->execute([$agentRoleId]);
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Ticket Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <link href="assets/css/custom.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <strong>Ticket</strong> Manager
            </h1>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">No tickets found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="align-middle me-2" data-feather="ticket"></i>
                        All Tickets (<?= count($tickets) ?> total)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Title</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Category</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr class="ticket-list-item status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>" style="border-left: 4px solid;">
                                        <td>
                                            <div class="ticket-reference">
                                                <i class="align-middle me-1" data-feather="hash" style="width: 14px; height: 14px;"></i>
                                                <?= htmlspecialchars($ticket['reference']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($ticket['title']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="align-middle" data-feather="mail" style="width: 12px; height: 12px;"></i>
                                                <?= htmlspecialchars($ticket['email']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge status-badge status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>">
                                                <?= htmlspecialchars(ucfirst($ticket['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge priority-badge priority-<?= strtolower($ticket['priority'] ?? 'medium') ?>">
                                                <i class="align-middle me-1" data-feather="flag" style="width: 12px; height: 12px;"></i>
                                                <?= htmlspecialchars(ucfirst($ticket['priority'] ?? 'Medium')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <i class="align-middle me-1" data-feather="folder" style="width: 12px; height: 12px;"></i>
                                                <?= htmlspecialchars($ticket['category_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="align-middle me-1" data-feather="calendar" style="width: 12px; height: 12px;"></i>
                                                <?= date('M d, Y', strtotime($ticket['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-flex gap-2 align-items-center">
                                                <input type="hidden" name="ticket_ref" value="<?= htmlspecialchars($ticket['reference']) ?>">
                                                <select name="user_id" class="form-select form-select-sm" style="min-width: 150px;" required>
                                                    <option value="">Select Agent</option>
                                                    <?php foreach ($agents as $agent): ?>
                                                        <option value="<?= $agent['user_id'] ?>" <?= isset($ticket['user_id']) && $ticket['user_id'] == $agent['user_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($agent['user_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <select name="priority" class="form-select form-select-sm" style="min-width: 120px;" required>
                                                    <option value="">Priority</option>
                                                    <?php
                                                    $priorities = ['low', 'medium', 'high', 'urgent'];
                                                    foreach ($priorities as $p): ?>
                                                        <option value="<?= $p ?>" <?= (strtolower($ticket['priority'] ?? '') === $p) ? 'selected' : '' ?>>
                                                            <?= ucfirst($p) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="assign_ticket" class="btn btn-primary btn-sm">
                                                    <i class="align-middle" data-feather="user-check"></i> Assign
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>     