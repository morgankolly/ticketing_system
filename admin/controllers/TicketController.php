<?php


require_once __DIR__ . '/../../vendor/autoload.php';






if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitTicket'])) {

    // Get form data safely
    $title        = trim($_POST['title']);
    $description  = trim($_POST['description']);
    $email        = trim($_POST['email']);
    $category_id  = (int)$_POST['category_id'];
    $priority     = $_POST['priority'] ?? 'medium';
    $user_id      = $_SESSION['user_id'] ?? null; // optional if logged in
    $contact      = trim($_POST['contact'] ?? null);
    $support_email= trim($_POST['support_email'] ?? null);
    $status       = 'open';
    $is_read      = 0; // default

    $attachment_path = null; // optional file

    // Handle optional file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg','image/png','application/pdf','application/msword','text/plain'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['attachment']['tmp_name'];
            $fileName    = basename($_FILES['attachment']['name']);
            $fileSize    = $_FILES['attachment']['size'];
            $fileType    = mime_content_type($fileTmpPath);

            if (!in_array($fileType, $allowedTypes)) {
                echo "<script>alert('Invalid file type.');</script>";
            } elseif ($fileSize > $maxSize) {
                echo "<script>alert('File too large. Max 5MB.');</script>";
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $attachment_path = 'uploads/' . $newFileName; // store relative path
                } else {
                    echo "<script>alert('Failed to upload file.');</script>";
                }
            }
        } else {
            echo "<script>alert('File upload error.');</script>";
        }
    }

    // Insert ticket into database
    if (!empty($title) && !empty($description) && !empty($email) && !empty($category_id)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tickets 
                (title, description, email, category_id, priority, status, user_id, contact, support_email, is_read, created_at)
                VALUES 
                (:title, :description, :email, :category_id, :priority, :status, :user_id, :contact, :support_email, :is_read, NOW())
            ");

            $stmt->execute([
                ':title'         => $title,
                ':description'   => $description,
                ':email'         => $email,
                ':category_id'   => $category_id,
                ':priority'      => $priority,
                ':status'        => $status,
                ':user_id'       => $user_id,
                ':contact'       => $contact,
                ':support_email' => $support_email,
                ':is_read'       => $is_read
            ]);

            echo "<script>alert('Ticket submitted successfully!'); window.location.href='index.php';</script>";
            exit;

        } catch (PDOException $e) {
            echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
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
?>