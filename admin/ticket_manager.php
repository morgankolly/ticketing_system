<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


include_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . '/controllers/TicketController.php';
include_once __DIR__ . '/compents/header.php';
$ticketModel = new TicketModel($pdo);

// fetch tickets and agents
$tickets = $ticketModel->getAllTickets();

$agentRoleId = 2;
$agentsStmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE role_id = ?");
$agentsStmt->execute([$agentRoleId]);
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['assign_ticket'])) {

    $ticketRef = trim($_POST['ticket_ref'] ?? '');
    $userId    = (int) ($_POST['user_id'] ?? 0);

    // ✅ Validate input
    if ($ticketRef === '' || $userId <= 0) {
        $_SESSION['error'] = "Ticket reference or agent is missing.";
        header("Location: ../ticket_manager.php");
        exit;
    }

    try {

        $ticketModel = new TicketModel($pdo);

        // ✅ First check if ticket exists
        $stmt = $pdo->prepare("SELECT ticket_id FROM tickets WHERE reference = :reference LIMIT 1");
        $stmt->execute([':reference' => $ticketRef]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            $_SESSION['error'] = "Ticket not found.";
            header("Location: ../ticket_manager.php");
            exit;
        }

        $ticketId = (int)$ticket['ticket_id'];

        // ✅ Assign ticket
        $assigned = $ticketModel->assignTicket($ticketRef, $userId);

        if (!$assigned) {
            $_SESSION['error'] = "Failed to assign ticket.";
            header("Location: ../ticket_manager.php");
            exit;
        }

        // ✅ Fetch agent
        $stmt = $pdo->prepare("
            SELECT email, user_name 
            FROM users 
            WHERE user_id = :user_id 
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agent) {
            $_SESSION['error'] = "Agent not found.";
            header("Location: ../ticket_manager.php");
            exit;
        }

        // ✅ Send email safely
        if (function_exists('sendemail') && filter_var($agent['email'], FILTER_VALIDATE_EMAIL)) {

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
        }

        // ✅ Insert notification
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (ticket_id, reference, message, created_at)
            VALUES (:ticket_id, :reference, :message, NOW())
        ");

        $notif_stmt->execute([
            ':ticket_id' => $ticketId,
            ':reference' => $ticketRef,
            ':message'   => "Ticket {$ticketRef} has been assigned to {$agent['user_name']}."
        ]);

        $_SESSION['success'] = "Ticket assigned successfully.";

        echo "<script>
            alert('Ticket assigned successfully!');
            window.location.href='ticket_manager.php';
          </script>";

    } catch (PDOException $e) {

        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: ../ticket_manager.php");
        exit;
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