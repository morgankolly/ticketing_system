<?php


require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/functions.php';;
require_once __DIR__ . '/../models/notificationModel.php';
$TicketModel = new TicketModel($pdo);
$UserModel = new UserModel($pdo);

if (isset($_POST['createTicket'])) {

    try {

        $ticketRef = generateTicketRef();

        $data = [
            'title'         => trim($_POST['title'] ?? ''),
            'description'   => trim($_POST['description'] ?? ''),
            'email'         => trim($_POST['email'] ?? ''),
            'status'        => 'open',
            'priority'      => $_POST['priority'] ?? 'medium',
            'category_id'   => $_POST['category_id'] ?? 1,
            'user_id'       => null,
            'contact'       => $_POST['contact'] ?? null,
            'support_email' => $_POST['support_email'] ?? null,
            'reference'     => $ticketRef
        ];

        if (empty($data['title']) || empty($data['description']) || empty($data['email'])) {
            throw new Exception("Please fill in all required fields.");
        }

        $ticket_id = $TicketModel->createFullTicket($data, $_FILES['file'] ?? null);

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $TicketModel->sendCustomerEmail($data, $ticketRef);
        }

        $TicketModel->notifyAdmins($ticket_id, $data['email']);

        $_SESSION['success'] = "Ticket submitted successfully.";
         // 5️⃣ Success
        echo "<script>alert('Ticket submitted successfully.'); window.location.href='index.php';</script>";

        exit;

    } catch (Exception $e) {

        $_SESSION['error'] = $e->getMessage();
        header("Location: index.php");
        exit;
    }
}


if (isset($_POST['assign_ticket'])) {
    $ticketRef = trim($_POST['ticket_ref'] ?? '');
    $userId    = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

    if (empty($ticketRef) || $userId <= 0) {
        echo "<script>alert('Ticket reference or agent is missing.'); window.history.back();</script>";
        exit;
    }

    try {
        $ticketModel = new TicketModel($pdo);
        $userModel   = new UserModel($pdo);
        $notifModel  = new NotificationModel($pdo);

        // 1️⃣ Assign ticket to agent
        if (!$ticketModel->assignTicket($ticketRef, $userId)) {
            echo "<script>alert('Failed to assign ticket.'); window.history.back();</script>";
            exit;
        }

        // 2️⃣ Fetch agent info
        $agent = $userModel->getUserById($userId);
        if (!$agent) throw new Exception("Agent not found.");

        // 3️⃣ Send email to agent
        $subject = "New Ticket Assigned (#{$ticketRef})";
        $body = "
            <h3>New Ticket Assigned</h3>
            <p>Hello <strong>" . htmlspecialchars($agent['user_name']) . "</strong>,</p>
            <p>A new ticket has been assigned to you.</p>
            <p><strong>Ticket Reference:</strong> " . htmlspecialchars($ticketRef) . "</p>
            <p>Please log in to the system to view and respond.</p>
            <br>
            <p>Regards,<br>Morgan Kolly Ticketing System</p>
        ";

        // Validate email and send
        if (filter_var($agent['email'], FILTER_VALIDATE_EMAIL)) {
            if (!function_exists('sendemail')) {
                throw new Exception("Email function not found.");
            }

            // Optional headers
            $headers = [
                'X-Mailer' => 'PHP/' . phpversion(),
                'From'     => 'morgankolly5@gmail.com'
            ];

            sendemail($agent['email'], $agent['user_name'], $subject, $body, $headers);
        } else {
            throw new Exception("Invalid agent email: " . $agent['email']);
        }

        // 4️⃣ Create system notification
        $ticket = $ticketModel->getTicketByReference($ticketRef);
        if (!$ticket) throw new Exception("Ticket not found.");

        $notifMsg = "Ticket {$ticketRef} has been assigned to {$agent['user_name']}.";
        $notifModel->createNotification($ticket['ticket_id'], $ticketRef, $notifMsg);

        // 5️⃣ Success feedback
        echo "<script>alert('Ticket assigned successfully and agent notified!'); window.location.href='ticket_manager.php';</script>";
        exit;

    } catch (Exception $e) {
        // Log the error and show friendly message
        error_log("Assign Ticket Error: " . $e->getMessage());
        echo "<script>alert('An error occurred: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
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

    // --- AUTH CHECK: ONLY AGENTS ---
    if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
        exit('Unauthorized');
    }

    // --- DEFINE VARIABLES FIRST ---
    $ticketRef = trim($_POST['ticket_ref'] ?? '');
    $comment   = trim($_POST['comment'] ?? '');
    $parentId  = $_POST['parent_comment_id'] ?? null;

    if ($ticketRef === '' || $comment === '') {
        // Redirect back if missing
        header("Location: ticketComments.php?ticket_ref=" . urlencode($ticketRef));
        exit;
    }

    $agentId = $_SESSION['user_id'];

    // --- FETCH TICKET ---
    $stmt = $pdo->prepare("SELECT ticket_id, title, email FROM tickets WHERE reference = ? LIMIT 1");
    $stmt->execute([$ticketRef]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        die("Ticket not found.");
    }

    $ticketId = $ticket['ticket_id'];

    // --- CHECK FOR DUPLICATE COMMENT (Optional but safe) ---
    $stmt = $pdo->prepare("
        SELECT comment_id 
        FROM ticket_comments 
        WHERE ticket_id = ? AND agent_id = ? AND comment = ? AND parent_comment_id IS ?
        LIMIT 1
    ");
    $stmt->execute([$ticketId, $agentId, $comment, $parentId]);
    if ($stmt->fetch()) {
        
    }

    // --- INSERT COMMENT ---
    $stmt = $pdo->prepare("
        INSERT INTO ticket_comments 
        (ticket_id, agent_id, comment, parent_comment_id, reference, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$ticketId, $agentId, $comment, $parentId, $ticketRef]);

    // --- SEND EMAIL TO CUSTOMER ---
    $recipientEmail = $ticket['email'];

    if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $emailSubject = "Update on your ticket (#{$ticketRef})";
        $emailBody = "
            <p>Hello,</p>
            <p>An agent has responded to your ticket:</p>
            <blockquote style='border-left:3px solid #ccc;padding-left:10px;'>
                " . nl2br(htmlspecialchars($comment)) . "
            </blockquote>
            <p><strong>Ticket Reference:</strong> {$ticketRef}</p>
            <p>Support Team</p>
        ";

        try {
            sendemail($recipientEmail, 'Support Team', $emailSubject, $emailBody);
        } catch (Exception $e) {
            error_log("Email failed: " . $e->getMessage());
        }
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


