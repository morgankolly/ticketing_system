<?php




require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/TicketModel.php';
$TicketModel = new TicketModel($pdo);
$assignedTickets = $TicketModel->getTicketsByUser($_SESSION['user_id']);

require_once __DIR__ . '/AuthController.php';




if (isset($_POST['submitTicket'])) {
    // --- Ticket fields ---
    $title       = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $email       = $_POST['email'] ?? '';
    $status      = $_POST['status'] ?? 'open'; // default to 'open'
    $priority    = $_POST['priority'] ?? 'medium';
    $category_id = $_POST['category_id'] ?? 1; // default category ID
    $contact       = $_POST['contact'] ?? null;
    $support_email = $_POST['support_email'] ?? null;

    // --- Insert ticket into DB ---
    $stmt = $pdo->prepare(
        "INSERT INTO tickets 
        (title, description, email, status, priority, category_id, contact, support_email)
        VALUES (:title, :description, :email, :status, :priority, :category_id, :contact, :support_email)"
    );

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':priority', $priority);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':support_email', $support_email);

    if ($stmt->execute()) {


        $ticket_id = $pdo->lastInsertId();

        // --- Handle file upload ---
        if (!empty($_FILES['file']['name'])) {
            $uploadDir = "uploads/"; // make sure this folder exists and is writable
            $fileName  = basename($_FILES['file']['name']);
            $fileTmp   = $_FILES['file']['tmp_name'];
            $fileType  = mime_content_type($fileTmp); // get MIME type
            $filePath  = $uploadDir . time() . "_" . $fileName; // unique file path

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($fileTmp, $filePath)) {
                    // Insert file info into ticket_files table
                    $fileStmt = $pdo->prepare(
                        "INSERT INTO ticket_files (ticket_id, file_name, file_path, file_type) 
                         VALUES (:ticket_id, :file_name, :file_path, :file_type)"
                    );
                    $fileStmt->bindParam(':ticket_id', $ticket_id);
                    $fileStmt->bindParam(':file_name', $fileName);
                    $fileStmt->bindParam(':file_path', $filePath);
                    $fileStmt->bindParam(':file_type', $fileType);
                    $fileStmt->execute();
                } else {
                    echo "<script>alert('Failed to upload the file.');</script>";
                }
            } else {
                echo "<script>alert('Only JPG, PNG, and GIF files are allowed.');</script>";
            }
        }

        // --- Send email notification ---
        if (!empty($email)) {
            $subject = "New Ticket Submitted: $title";
            $body = "
                <h3>Ticket Details</h3>
                <p><strong>Title:</strong> $title</p>
                <p><strong>Description:</strong> $description</p>
                <p><strong>Priority:</strong> $priority</p>
                <p><strong>Status:</strong> $status</p>
                <p>Thank you for contacting us. Our team will get back to you soon.</p>
            ";
            sendemail($email, $title, $subject, $body);
        }

        // --- System notification ---
        $notif_msg = "A new ticket (#$ticket_id) has been submitted by $email.";
        $notif_stmt = $pdo->prepare(
            "INSERT INTO notifications (ticket_id, message) VALUES (:ticket_id, :message)"
        );
        $notif_stmt->bindParam(':ticket_id', $ticket_id);
        $notif_stmt->bindParam(':message', $notif_msg);
        $notif_stmt->execute();

        echo "<script>
                alert('Thank you! Your ticket has been submitted successfully.');
                window.location.href='index.php';
              </script>";
    } else {
        echo "<script>alert('Failed to submit ticket. Please try again.');</script>";
    }
}
 
if (isset($_POST['assign_ticket'])) {

    $ticket_id = $_POST['ticket_id'];
    $user_id   = $_POST['user_id'];

    // 1. Assign ticket to user
    $stmt = $pdo->prepare(
        "UPDATE tickets SET user_id = :user_id WHERE ticket_id = :ticket_id"
    );
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':ticket_id', $ticket_id);
    $stmt->execute();

    // 2. Fetch user email + name
    $userStmt = $pdo->prepare(
        "SELECT email, user_name FROM users WHERE user_id = :user_id"
    );
    $userStmt->bindParam(':user_id', $user_id);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    // 3. Send email notification
    if ($user) {
        $subject = "New Ticket Assigned (#$ticket_id)";
        $body = "
            <h3>New Ticket Assigned</h3>
            <p>Hello <strong>{$user['user_name']}</strong>,</p>
            <p>A new ticket has been assigned to you.</p>
            <p><strong>Ticket ID:</strong> $ticket_id</p>
            <p>Please log in to the system to view and respond.</p>
            <br>
            <p>Regards,<br>Ticketing System</p>
        ";

        sendemail($user['email'], $user['user_name'], $subject, $body);
    }

    echo "<script>
                alert('Ticket assigned successfully!');
                window.location.href='ticket_manager.php';
              </script>";
    } 


$priority = $_GET['priority'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$ticketPriorities = ['low', 'medium', 'high'];

$query = "SELECT tickets.*, c.category_name 
          FROM tickets 
          JOIN category c ON tickets.category_id = c.category_id
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
$newTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

