<?php


require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/functions.php';
;
require_once __DIR__ . '/../models/notificationModel.php';
$TicketModel = new TicketModel($pdo);
$UserModel = new UserModel($pdo);
if (isset($_POST['createTicket'])) {

    try {

        $ticketRef = generateTicketRef();
        $closeToken = bin2hex(random_bytes(32)); // 🔐 Secure close token
        
        // ✅ Generate a unique Message-ID for this ticket
        $messageId = "<ticket-{$ticketRef}." . time() . "." . uniqid() . "@localhost>";

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'status' => 'open',
            'priority' => $_POST['priority'] ?? 'medium',
            'category_id' => $_POST['category_id'] ?? 1,
            'user_id' => null,
            'contact' => $_POST['contact'] ?? null,
            'support_email' => $_POST['support_email'] ?? null,
            'reference' => $ticketRef,
            'close_token' => $closeToken,
            'message_id' => $messageId // 👈 ADD THIS to store in database
        ];

        if (empty($data['title']) || empty($data['description']) || empty($data['email'])) {
            throw new Exception("Please fill in all required fields.");
        }

        // Insert ticket (make sure your model supports close_token and message_id)
        $result = $TicketModel->createFullTicket($data, $_FILES['file'] ?? null);

        $ticket_id = $result['ticket_id'];
        
        // ✅ Store the Message-ID in the tickets table for future replies
        $updateStmt = $pdo->prepare("UPDATE tickets SET message_id = ? WHERE reference = ?");
        $updateStmt->execute([$messageId, $ticketRef]);

        // ✅ Generate close link
        $closeLink = "http://localhost/ticketing-system/admin/closeTicket.php?ref="
            . urlencode($ticketRef)
            . "&token="
            . urlencode($closeToken);

        // Add link to email
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {

            $data['close_link'] = $closeLink; // pass link to email method
            
            // ✅ Pass the Message-ID to the email sending method
            $data['message_id'] = $messageId;

            $TicketModel->sendCustomerEmail($data, $ticketRef);
        }

        $TicketModel->notifyAdmins($ticket_id, $data['email']);

        echo "<script>
                alert('Ticket submitted successfully.');
                window.location.href='index.php';
              </script>";
        exit;

    } catch (Exception $e) {

        $_SESSION['error'] = $e->getMessage();
        header("Location: index.php");
        exit;
    }
}


if (isset($_POST['assign_ticket'])) {

    try {

        // 1️⃣ Sanitize inputs
        $ticketRef = trim($_POST['ticket_ref'] ?? '');
        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $priority = ucfirst(strtolower($_POST['priority'] ?? 'Medium'));

        // 2️⃣ Validate inputs
        if (empty($ticketRef) || $userId <= 0) {
            throw new Exception("Ticket reference or agent is missing.");
        }

        // Optional: validate allowed priorities
        $allowedPriorities = ['Low', 'Medium', 'High', 'Urgent'];
        if (!in_array($priority, $allowedPriorities)) {
            $priority = 'Medium';
        }

        // 3️⃣ Initialize models
        $ticketModel = new TicketModel($pdo);
        $userModel = new UserModel($pdo);
        $notifModel = new NotificationModel($pdo);

        // 4️⃣ Assign ticket (ONLY here)
        if (!$ticketModel->assignTicket($ticketRef, $userId, $priority)) {
            throw new Exception("Failed to assign ticket.");
        }

        // 5️⃣ Fetch agent
        $agent = $userModel->getUserById($userId);
        if (!$agent) {
            throw new Exception("Agent not found.");
        }

        // 6️⃣ Send email
        if (filter_var($agent['email'], FILTER_VALIDATE_EMAIL)) {

            if (!function_exists('sendemail')) {
                throw new Exception("Email function not found.");
            }

            $subject = "New Ticket Assigned (#{$ticketRef})";
            $body = "
                <h3>New Ticket Assigned</h3>
                <p>Hello <strong>" . htmlspecialchars($agent['user_name']) . "</strong>,</p>
                <p>A ticket has been assigned to you.</p>
                <p><strong>Reference:</strong> " . htmlspecialchars($ticketRef) . "</p>
                <p><strong>Priority:</strong> {$priority}</p>
                <br>
                <p>Please log in to view the ticket.</p>
                <p>Regards,<br>Morgan Kolly Ticketing System</p>
            ";

            $headers = [
                'X-Mailer' => 'PHP/' . phpversion(),
                'From' => 'morgankolly5@gmail.com'
            ];

            sendemail($agent['email'], $agent['user_name'], $subject, $body, $header, $data );
        }

        // 7️⃣ Create system notification
        $ticket = $ticketModel->getTicketByReference($ticketRef);
        if (!$ticket) {
            throw new Exception("Ticket not found.");
        }

        $notifMsg = "Ticket {$ticketRef} assigned to {$agent['user_name']} with {$priority} priority.";
        $notifModel->createNotification($ticket['ticket_id'], $ticketRef, $notifMsg);

        // 8️⃣ Success
        echo "<script>
                alert('Ticket assigned successfully and Agent got notified!');
                window.location.href='ticket_manager.php';
              </script>";
        exit;

    } catch (Exception $e) {

        error_log("Assign Ticket Error: " . $e->getMessage());

        echo "<script>
                alert('Error: " . htmlspecialchars($e->getMessage()) . "');
                window.history.back();
              </script>";
        exit;
    }
}




$priority = $_GET['priority'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$ticketPriorities = ['low', 'medium', 'high'];

$query = "SELECT tickets.*, category.category_name 
          FROM tickets 
          JOIN category ON tickets.category_id = category.category_id
          WHERE 1=1";

$params = [];

if (!empty($priority)) {
    $query .= " AND tickets.priority = :priority";
    $params[':priority'] = $priority;
}
if (!empty($startDate)) {
    $query .= " AND tickets.created_at >= :start_date";
    $params[':start_date'] = $startDate . " 00:00:00";
}
if (!empty($endDate)) {
    $query .= " AND tickets.created_at <= :end_date";
    $params[':end_date'] = $endDate . " 23:59:59";
}

$query .= " ORDER BY tickets.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);


$agentRoleId = 2;
$agentsStmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE role_id = ?");
$agentsStmt->execute([$agentRoleId]);
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
        exit('Unauthorized');
    }
    $ticketRef = trim($_POST['ticket_ref'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $parentId = $_POST['parent_comment_id'] ?? null; // can be null

    if ($ticketRef === '' || $comment === '') {
        header("Location: ticketComments.php?ticket_ref=" . urlencode($ticketRef));
        exit;
    }

    $agentId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT ticket_id, title, email, message_id FROM tickets WHERE reference = ? LIMIT 1");
    $stmt->execute([$ticketRef]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        die("Ticket not found.");
    }
    $ticketId = $ticket['ticket_id'];
    $stmt = $pdo->prepare("
        INSERT INTO ticket_comments 
        (ticket_id, agent_id, comment, parent_comment_id, reference, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$ticketId, $agentId, $comment, $parentId, $ticketRef]);
    $recipientEmail = $ticket['email'];
    if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $originalMessageId = $ticket['message_id'] ?? '';
        $uniqueId = time() . '.' . uniqid() . '.' . $ticketRef;
        $replyMessageId = "<{$uniqueId}@morgankolly5@gmail.com>";
        if (empty($originalMessageId)) {
            $inReplyTo = '';
            $references = '';
        } else {
            $inReplyTo = $originalMessageId;
            $references = $originalMessageId;
        }
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE tickets SET close_token = :token WHERE reference = :reference");
        $stmt->execute([
            ':token' => $token,
            ':reference' => $ticketRef
        ]);

        $closeLink = "https://localhost/ticketing-system/admin/close_ticket.php?ref="
            . urlencode($ticketRef)
            . "&token=" . urlencode($token);

        $emailSubject = "Re: Ticket Received - #{$ticketRef}";
        $emailBody = "
            <p>Hello,</p>
            <p>An agent has responded to your ticket:</p>
            <blockquote style='border-left:3px solid #ccc;padding-left:10px;'>
                " . nl2br(htmlspecialchars($comment)) . "
            </blockquote>
            <p><strong>Ticket Reference:</strong> {$ticketRef}</p>
            <br><br>
            If your issue is resolved, you can close your ticket here:<br>
            <a href='" . htmlspecialchars($closeLink) . "'>Close Ticket</a>
            <br><br>
            <p>Support Team</p>
        ";
        sendemail(
            $recipientEmail,
            'Customer',
            (string)$emailSubject,
            (string)$emailBody,
            (string)$replyMessageId,
            (string)$originalMessageId,
            (string)$inReplyTo,      // Add this parameter

        );
    }
}





$ticketRef = $_GET['ticket_ref'] ?? $_POST['ticket_ref'] ?? null;

if ($ticketRef) {
    $ticket = $TicketModel->getTicketByReference($ticketRef);
} else {
    $ticket = null;
}
$ticketRef = $_GET['ticket_ref'] ?? null;

$ticket = null;

if (!empty($ticketRef)) {
    $ticket = $TicketModel->getTicketByReference($ticketRef);
}


