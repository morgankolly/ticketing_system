<?php



require_once __DIR__ . '/../config/connection.php'; // your $pdo
require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/notificationModel.php';
$TicketModel = new TicketModel($pdo);
$UserModel   = new UserModel($pdo);

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
             echo "<script>alert('Ticket submitted successfully.'); window.location.href='index.php';</script>";

        exit;
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
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

    if (empty($ticketRef) || $userId <= 0) {
        echo "<script>alert('Ticket reference or agent is missing.'); window.history.back();</script>";
        exit;
    }

    try {
        $ticketModel = new TicketModel($pdo);
        $userModel = new UserModel($pdo);
        $notifModel = new notificationModel($pdo);

        // 1️⃣ Assign ticket
        if (!$ticketModel->assignTicket($ticketRef, $userId)) {
            echo "<script>alert('Failed to assign ticket.'); window.history.back();</script>";
            exit;
        }

        // 2️⃣ Fetch agent info
        $agent = $userModel->getUserById($userId);
        if (!$agent) throw new Exception("Agent not found.");

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

        // 4️⃣ Create notification
        $ticket = $ticketModel->getTicketByReference($ticketRef);
        if (!$ticket) throw new Exception("Ticket not found.");

        $notifMsg = "Ticket {$ticketRef} has been assigned to {$agent['user_name']}.";
        $notifModel->createNotification($ticket['ticket_id'], $ticketRef, $notifMsg);

        // 5️⃣ Success
        echo "<script>alert('Ticket assigned successfully and agent notified!'); window.location.href='ticket_manager.php';</script>";
        exit;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
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

    $ticketRef = $_GET['ticket_ref'] ?? $_POST['ticket_ref'] ?? null;
    if (!$ticketRef) {
        die("Ticket reference missing.");
    }

    $comment  = trim($_POST['comment']);
    $agentId  = $_SESSION['user_id'];
    $parentId = $_POST['parent_comment_id'] ?? null;

    if ($comment === '') {
        header("Location: ticketComments.php?ticket_ref=$ticketRef");
        exit;
    }

    // --- FETCH TICKET INFO FIRST ---
    $stmt = $pdo->prepare("SELECT ticket_id, email FROM tickets WHERE reference = ? LIMIT 1");
    $stmt->execute([$ticketRef]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        die("Ticket not found for reference '$ticketRef'");
    }

    $ticketId = $ticket['ticket_id'];

    // --- INSERT COMMENT WITH REFERENCE ---
    $stmt = $pdo->prepare("
        INSERT INTO ticket_comments 
            (ticket_id, agent_id, comment, parent_comment_id, reference)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$ticketId, $agentId, $comment, $parentId, $ticketRef]);

    // --- DETERMINE RECIPIENT EMAIL ---
    $recipientEmail = $ticket['email'];
    if ($parentId) {
        $stmt = $pdo->prepare("
            SELECT COALESCE(commenter_email, ?) AS email 
            FROM ticket_comments 
            WHERE comment_id = ? LIMIT 1
        ");
        $stmt->execute([$ticket['email'], $parentId]);
        $parentComment = $stmt->fetch(PDO::FETCH_ASSOC);
        $recipientEmail = $parentComment['email'] ?? $ticket['email'];
    }

    // --- EMAIL CONTENT ---
    $emailSubject = $parentId
        ? "Reply to your comment on ticket: " . $ticketRef
        : "Update on your ticket: " . $ticketRef;

    $emailBody = "
        <p>Hello,</p>
        <p>" . ($parentId ? "An agent has replied to your comment" : "An agent has commented on your ticket") . ":</p>
        <blockquote style='border-left:3px solid #ccc;padding-left:10px;'>
            " . nl2br(htmlspecialchars($comment)) . "
        </blockquote>
        <p><strong>Ticket Ref:</strong> $ticketRef</p>
        <p>Support Team</p>
    ";

    // --- SEND EMAIL ---
    if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        sendemail($recipientEmail, 'Support Team', $emailSubject, $emailBody);
    }

    // --- REDIRECT BACK ---
    echo "<script>
        alert('Comment added successfully!');
        window.location.href='ticketComments.php?ticket_ref=$ticketRef';
    </script>";
    exit;
}
 $ticketRef = $_GET['ticket_ref'] ?? $_POST['ticket_ref'] ?? null;

// Initialize ticket and comments
$ticket = null;
$comments = []; // <- initialize to empty array

// Only fetch ticket if ticketRef exists
if (!empty($ticketRef) && is_string($ticketRef)) {
    $ticket = $TicketModel->getTicketByReference($ticketRef);

    if ($ticket) {
        $ticketId = $ticket['ticket_id'];
        $comments = $TicketModel->getTopLevelComments($ticketId);

        foreach ($comments as &$comment) {
            $comment['replies'] = $TicketModel->getReplies($comment['comment_id']);
        }
        unset($comment); // break reference
    }
}
