<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once __DIR__ . '/main/auth-middleware.php';
include_once __DIR__ . '/compents/header.php';
$usersStmt = $pdo->query("
    SELECT users.user_id, users.user_name, users.email, users.created_at, users.profile, roles.role_name, users.role_id
    FROM users
    LEFT JOIN roles ON users.role_id = roles.role_id
    ORDER BY users.created_at DESC
");
$agentRoleId = 2; // assuming role_id 2 = agent
$usersStmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE role_id = ?");
$usersStmt->execute([$agentRoleId]);
$agents = $usersStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Ticket Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
</head>

<body>

    <div class="container mt-5">
        <h1>Ticket Manager</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Filter Form -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($ticketPriorities as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= $priority === $p ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($p)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Tickets Table -->
        <?php if (empty($newTickets)): ?>
            <p>No tickets found.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Assign to Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newTickets as $ticket): ?>
                        <tr>
                            <td><?= $ticket['ticket_id'] ?></td>
                            <td><?= htmlspecialchars($ticket['title']) ?></td>
                            <td><?= htmlspecialchars($ticket['description']) ?></td>
                            <td><?= htmlspecialchars($ticket['priority']) ?></td>
                            <td><?= htmlspecialchars($ticket['status']) ?></td>
                            <td><?= htmlspecialchars($ticket['category_name']) ?></td>
                            <td><?= $ticket['created_at'] ?></td>
                            <td>
                              <form method="POST" class="d-flex gap-2">
    <input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">

    <select name="user_id" class="form-select" required>
    <option value="">Select Agent</option>
    <?php foreach ($agents as $agent): ?>
        <option value="<?= $agent['user_id'] ?>" 
            <?= isset($ticket['user_id']) && $ticket['user_id'] == $agent['user_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($agent['user_name']) ?>
        </option>
    <?php endforeach; ?>
</select>

    <button type="submit" name="assign_ticket" class="btn btn-primary">Assign</button>
</form>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>