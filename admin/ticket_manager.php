<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/compents/header.php';
include_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/models/TicketModel.php';

$ticketModel = new TicketModel($pdo);

// fetch tickets and agents
$tickets = $ticketModel->getAllTickets();

$agentRoleId = 2;
$agentsStmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE role_id = ?");
$agentsStmt->execute([$agentRoleId]);
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['assign_ticket'])) {

    // ✅ Match exact form field names
    $ticketRef = trim($_POST['ticket_ref'] ?? '');
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

    // ✅ Strong validation
    if (empty($ticketRef) || $userId <= 0) {
        echo "<script>
                alert('Ticket reference or agent is missing.');
                window.history.back();
              </script>";
        exit;
    }

    try {

        // ✅ Initialize model
        $ticketModel = new TicketModel($pdo);

        // 1️⃣ Assign ticket
        $assigned = $ticketModel->assignTicket($ticketRef, $userId);

        if (!$assigned) {
            echo "<script>
                    alert('Failed to assign ticket.');
                    window.history.back();
                  </script>";
            exit;
        }

        // 2️⃣ Fetch agent info safely
        $stmt = $pdo->prepare("
            SELECT email, user_name 
            FROM users 
            WHERE user_id = :user_id 
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($agent) {

            // 3️⃣ Send email
            $subject = "New Ticket Assigned (#{$ticketRef})";

            $body = "
                <h3>New Ticket Assigned</h3>
                <p>Hello <strong>" . htmlspecialchars($agent['user_name']) . "</strong>,</p>
                <p>A new ticket has been assigned to you.</p>
                <p><strong>Ticket Reference:</strong> " . htmlspecialchars($ticketRef) . "</p>
                <p>Please log in to the system to view and respond.</p>
                <br>
                <p>Regards,<br>Ticketing System</p>
            ";

            sendemail($agent['email'], $agent['user_name'], $subject, $body);

            // 4️⃣ Insert system notification
            $notif_msg = "Ticket {$ticketRef} has been assigned to {$agent['user_name']}.";
            $stmt = $pdo->prepare("SELECT ticket_id FROM tickets WHERE reference = :reference LIMIT 1");
            $stmt->execute([':reference' => $ticketRef]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                die("Ticket not found.");
            }

            $ticketId = $ticket['ticket_id'];
            $notif_stmt = $pdo->prepare("
                  INSERT INTO notifications (ticket_id, reference, message) 
    VALUES (:ticket_id, :reference, :message)
");

            $notif_stmt->execute([
                ':ticket_id' => $ticketId,
                ':reference' => $ticketRef,
                ':message' => $notif_msg
            ]);
        }

        // 5️⃣ Success redirect
        echo "<script>
                alert('Ticket assigned successfully and agent notified!');
                window.location.href='ticket_manager.php';
              </script>";
        exit;

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
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
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Category ID</th>
                        <th>Created At</th>
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
                            <td><?= htmlspecialchars($ticket['priority']) ?></td>
                            <td><?= htmlspecialchars($ticket['status']) ?></td>
                            <td><?= htmlspecialchars($ticket['category_id']) ?></td>
                            <td><?= $ticket['created_at'] ?></td>
                            <td>
                                <!-- Form submits directly to controller -->
                                <form method="POST" action="" class="d-flex gap-2">
                                    <!-- Use ticket reference instead of ticket ID -->
                                    <input type="hidden" name="ticket_ref"
                                        value="<?= htmlspecialchars($ticket['reference']) ?>">
                                    <select name="user_id" class="form-select" required>
                                        <option value="">Select Agent</option>
                                        <?php foreach ($agents as $agent): ?>
                                            <option value="<?= $agent['user_id'] ?>" <?= isset($ticket['user_id']) && $ticket['user_id'] == $agent['user_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($agent['user_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" name="assign_ticket" class="btn btn-primary">Assign</button>
                                </form>

                            </td>
                        </tr>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>