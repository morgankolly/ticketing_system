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
    <title>Admin - Ticket Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Ticket Manager</h1>

        <?php if (empty($tickets)): ?>
            <p>No tickets found.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>reference</th>
                        <th>Title</th>
                        <th>Email</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Priority</th>
                        <th>Assign To</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?= $ticket['reference'] ?></td>
                            <td><?= htmlspecialchars($ticket['title']) ?></td>
                            <td><?= htmlspecialchars($ticket['email']) ?></td>
                            <td><?= htmlspecialchars($ticket['description']) ?></td>
                            <td><?= htmlspecialchars($ticket['status']) ?></td>
                            <td><?= htmlspecialchars($ticket['category_name']) ?></td>
                            <td><?= $ticket['created_at'] ?></td>  
                          <td>
    <?php
    // Define ENUM values (or fetch dynamically — shown below)
    $priorities = ['low', 'medium', 'high', 'urgent'];
    ?>

</td>
                            <td>
                               <form method="POST" action="" class="d-flex gap-2">
    <input type="hidden" name="ticket_ref" value="<?= htmlspecialchars($ticket['reference']) ?>">

    <select name="user_id" class="form-select" required>
        <option value="">Select Agent</option>
        <?php foreach ($agents as $agent): ?>
            <option value="<?= $agent['user_id'] ?>" <?= isset($ticket['user_id']) && $ticket['user_id'] == $agent['user_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($agent['user_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>


    <select name="priority" class="form-select" required>
        <option value="">Priority</option>

        <?php foreach ($priorities as $p): ?>
            <option value="<?= $p ?>"
                <?= (strtolower($ticket['priority'] ?? '') === $p) ? 'selected' : '' ?>>
                <?= ucfirst($p) ?>
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