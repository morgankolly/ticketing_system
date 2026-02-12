<?php

if (!isset($_GET['ticket_id']) && !isset($_POST['ticket_id'])) {

    return;
}
require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/functions.php';
$TicketModel = new TicketModel($pdo);
$assignedTickets = $TicketModel->getTicketsByUser($_SESSION['user_id']);
$UserModel = new UserModel($pdo);


if (isset($_POST['createTicket'])) {
    try {
         $ticketRef = generateTicketRef();
        // --- Prepare ticket data ---
        $data = [
            'title'         => $_POST['title'] ?? '',
            'description'   => $_POST['description'] ?? '',
            'email'         => $_POST['email'] ?? '',
            'status'        => 'open',
            'priority'      => $_POST['priority'] ?? 'medium',
            'category_id'   => $_POST['category_id'] ?? 1,
            'user_id'       => null,
            'contact'       => $_POST['contact'] ?? null,
            'support_email' => $_POST['support_email'] ?? null,
        ];

        // --- Validate required fields ---
        if (empty($data['title']) || empty($data['description']) || empty($data['email'])) {
            throw new Exception("Please fill in all required fields: Title, Description, Email.");
        }

        
        $ticket_id = $TicketModel->createTicket($data);

        // --- Handle file upload ---
        if (!empty($_FILES['file']['name'])) {
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = basename($_FILES['file']['name']);
            $fileTmp  = $_FILES['file']['tmp_name'];
            $fileType = mime_content_type($fileTmp);
            $filePath = $uploadDir . time() . "_" . $fileName;

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($fileTmp, $filePath)) {
                    $fileStmt = $pdo->prepare("
                        INSERT INTO ticket_files (ticket_id, file_name, file_path, file_type)
                        VALUES (:ticket_id, :file_name, :file_path, :file_type)
                    ");
                    $fileStmt->execute([
                        ':ticket_id' => $ticket_id,
                        ':file_name' => $fileName,
                        ':file_path' => 'uploads/' . time() . "_" . $fileName,
                        ':file_type' => $fileType
                    ]);
                }
            }
        }

        // --- Send confirmation email to ticket sender ---
        if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && function_exists('sendemail')) {
            $subject = "Ticket Received - #{$ticketRef}";
            $body = "
                <h2>Thank You for Contacting Support</h2>
                <p>Hello,</p>
                <p>Your support ticket has been successfully submitted.</p>

                <h3>Ticket Details</h3>
                 <p><strong>Reference Number:</strong> {$ticketRef}</p
                <p><strong>Title:</strong> " . htmlspecialchars($data['title']) . "</p>
                <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($data['description'])) . "</p>
                <p><strong>Priority:</strong> " . htmlspecialchars($data['priority']) . "</p>
                <p><strong>Status:</strong> Open</p>

                <p>Our team will get back to you shortly.</p>
                <br>
                <p>Best regards,<br>Support Team</p>
            ";

            sendemail($data['email'], "Customer", $subject, $body);
        }

        // --- Notify all admins ---
        $adminStmt = $pdo->prepare("SELECT email FROM users WHERE role_id = 1");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            $notif_stmt = $pdo->prepare("
                INSERT INTO notifications (email, ticket_id, message, is_read, created_at)
                VALUES (:email, :ticket_id, :message, 0, NOW())
            ");
            $notif_stmt->execute([
                ':email'     => $admin['email'],
                ':ticket_id' => $ticket_id,
                ':message'   => "New ticket (#$ticket_id) submitted by {$data['email']}."
            ]);
        }

        // --- Success message ---
        echo "<script>
                alert('Thank you! Your ticket has been submitted successfully.');
                window.location.href='index.php';
              </script>";

    } catch (PDOException $e) {
        echo "<script>alert('Database Error: {$e->getMessage()}');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error: {$e->getMessage()}');</script>";
    }
}


if (isset($_POST['createTicket'])) {
    try {
        // --- Prepare ticket data ---
        $data = [
            'title'         => $_POST['title'] ?? '',
            'description'   => $_POST['description'] ?? '',
            'email'         => $_POST['email'] ?? '',
            'status'        => 'open',
            'priority'      => $_POST['priority'] ?? 'medium',
            'category_id'   => $_POST['category_id'] ?? 1,
            'user_id'       => null,
            'contact'       => $_POST['contact'] ?? null,
            'support_email' => $_POST['support_email'] ?? null,
        ];

        // --- Validate required fields ---
        if (empty($data['title']) || empty($data['description']) || empty($data['email'])) {
            throw new Exception("Please fill in all required fields: Title, Description, Email.");
        }

        // --- Create ticket ---
        $ticket_id = $TicketModel->createTicket($data);

        // --- Handle file upload ---
        if (!empty($_FILES['file']['name'])) {
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = basename($_FILES['file']['name']);
            $fileTmp  = $_FILES['file']['tmp_name'];
            $fileType = mime_content_type($fileTmp);
            $filePath = $uploadDir . time() . "_" . $fileName;

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($fileTmp, $filePath)) {
                    $fileStmt = $pdo->prepare("
                        INSERT INTO ticket_files (ticket_id, file_name, file_path, file_type)
                        VALUES (:ticket_id, :file_name, :file_path, :file_type)
                    ");
                    $fileStmt->execute([
                        ':ticket_id' => $ticket_id,
                        ':file_name' => $fileName,
                        ':file_path' => 'uploads/' . time() . "_" . $fileName,
                        ':file_type' => $fileType
                    ]);
                }
            }
        }

        // --- Send confirmation email to ticket sender ---
        if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && function_exists('sendemail')) {
            $subject = "Ticket Received - #{$ticket_id}";
            $body = "
                <h2>Thank You for Contacting Support</h2>
                <p>Hello,</p>
                <p>Your support ticket has been successfully submitted.</p>

                <h3>Ticket Details</h3>
                <p><strong>Ticket ID:</strong> #{$ticket_id}</p>
                <p><strong>Title:</strong> " . htmlspecialchars($data['title']) . "</p>
                <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($data['description'])) . "</p>
                <p><strong>Priority:</strong> " . htmlspecialchars($data['priority']) . "</p>
                <p><strong>Status:</strong> Open</p>

                <p>Our team will get back to you shortly.</p>
                <br>
                <p>Best regards,<br>Support Team</p>
            ";

            sendemail($data['email'], "Customer", $subject, $body);
        }

        // --- Notify all admins ---
        $adminStmt = $pdo->prepare("SELECT email FROM users WHERE role_id = 1");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            $notif_stmt = $pdo->prepare("
                INSERT INTO notifications (email, ticket_id, message, is_read, created_at)
                VALUES (:email, :ticket_id, :message, 0, NOW())
            ");
            $notif_stmt->execute([
                ':email'     => $admin['email'],
                ':ticket_id' => $ticket_id,
                ':message'   => "New ticket (#$ticket_id) submitted by {$data['email']}."
            ]);
        }

        // --- Success message ---
        echo "<script>
                alert('Thank you! Your ticket has been submitted successfully.');
                window.location.href='index.php';
              </script>";

    } catch (PDOException $e) {
        echo "<script>alert('Database Error: {$e->getMessage()}');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error: {$e->getMessage()}');</script>";
    }
}

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
                window.location.href='../ticket_manager.php';
              </script>";
        exit;

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
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

    $ticketId = $_GET['ticket_id'] ?? $_POST['ticket_id'] ?? null;
    if (!$ticketId || !is_numeric($ticketId)) {
        die("Invalid ticket ID");
    }
    $ticketId = (int)$ticketId;
    $comment   = trim($_POST['comment']);
    $agentId   = $_SESSION['user_id'];
    $parentId  = $_POST['parent_comment_id'] ?? null;

    if ($ticketId <= 0 || $comment === '') {
        header("Location: ticketComments.php?ticket_id=$ticketId");
        exit;
    }

    // --- INSERT COMMENT OR REPLY ---
    $stmt = $pdo->prepare("
        INSERT INTO ticket_comments 
            (ticket_id, agent_id, comment, parent_comment_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$ticketId, $agentId, $comment, $parentId]);

    // --- FETCH TICKET INFO (title + owner email) ---
    $stmt = $pdo->prepare("SELECT title, email FROM tickets WHERE ticket_id = ? LIMIT 1");
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- DETERMINE WHO TO SEND EMAIL TO ---
    $recipientEmail = null;
    $emailSubject   = '';
    $emailBody      = '';

    if ($parentId) {
        // This is a reply to a comment
        $stmt = $pdo->prepare("
            SELECT COALESCE(commenter_email, ?) AS email 
            FROM ticket_comments 
            WHERE comment_id = ? LIMIT 1
        ");
        $stmt->execute([$ticket['email'], $parentId]);
        $parentComment = $stmt->fetch(PDO::FETCH_ASSOC);
        $recipientEmail = $parentComment['email'] ?? $ticket['email'];

        $emailSubject = "Reply to your comment on ticket: " . $ticket['title'];
        $emailBody    = "
            <p>Hello,</p>
            <p>An agent has replied to your comment:</p>
            <blockquote style='border-left:3px solid #ccc;padding-left:10px;'>
                " . nl2br(htmlspecialchars($comment)) . "
            </blockquote>
            <p><strong>Ticket ID:</strong> $ticketId</p>
            <p>Please reply if you need further assistance.</p>
            <p>Best regards,<br>Support Team</p>
        ";
    } else {
        // Top-level comment → send to ticket owner
        $recipientEmail = $ticket['email'];

        $emailSubject = "Update on your ticket: " . $ticket['title'];
        $emailBody    = "
            <p>Hello,</p>
            <p>An agent has replied to your ticket:</p>
            <blockquote style='border-left:3px solid #ccc;padding-left:10px;'>
                " . nl2br(htmlspecialchars($comment)) . "
            </blockquote>
            <p><strong>Ticket ID:</strong> $ticketId</p>
            <p>Support Team</p>
        ";
    }

    // --- SEND EMAIL IF VALID ---
    if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        sendemail($recipientEmail, 'Support Team', $emailSubject, $emailBody);
    }

    echo "<script>
            alert('Comment added successfully!');
            window.location.href='ticketComments.php?ticket_id=$ticketId';
          </script>";
    exit;
}



if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    die('Invalid ticket ID');
}

$ticketId = (int) $_GET['ticket_id'];

$TicketModel = new TicketModel($pdo);

$ticket = $TicketModel->getTicketById($ticketId);

if (!$ticket) {
    die('Ticket not found');
}

$comments = $TicketModel->getTicketComments($ticketId);

// Handle comment submission
if (isset($_POST['submit_comment'])) {
    if (!isset($_SESSION['user_id'])) {
        die('Unauthorized');
    }

    $comment = trim($_POST['comment']);
    if ($comment !== '') {
        $TicketModel->addComment($ticketId, $_SESSION['user_id'], $comment);
        header("Location: ticketComments.php?ticket_id=$ticketId");
        exit;
    }
}

if (isset($_POST['submit_comment'])) {
    $comment = trim($_POST['comment']);
    $agentId = $_SESSION['user_id'];

    if ($comment !== '') {
        $TicketModel->addAgentComment($ticketId, $agentId, $comment);
        header("Location: agentTicketComments.php?ticket_id=$ticketId");
        exit;
    }
}
