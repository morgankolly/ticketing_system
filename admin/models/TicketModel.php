<?php
class TicketModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }


    public function createTicket(array $data): int
    {
        // 1️⃣ Generate reference number if not already provided
        $ticketRef = $data['reference'] ?? $this->generateTicketRef(); // we will add generateTicketRef()

        // 2️⃣ Prepare SQL including reference
        $sql = "
        INSERT INTO tickets 
        (title, description, email, status, priority, category_id, user_id, contact, support_email, reference)
        VALUES 
        (:title, :description, :email, :status, :priority, :category_id, :user_id, :contact, :support_email, :reference)
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':description' => $data['description'] ?? '',
            ':email' => $data['email'] ?? '',
            ':status' => $data['status'] ?? 'open',
            ':priority' => $data['priority'] ?? 'medium',
            ':category_id' => $data['category_id'] ?? 1,
            ':user_id' => $data['user_id'] ?? null,
            ':contact' => $data['contact'] ?? null,
            ':support_email' => $data['support_email'] ?? null,
            ':reference' => $ticketRef
        ]);

        return (int) $this->pdo->lastInsertId();
    }




    public function updateTicket($id, $data)
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("UPDATE tickets SET title = :title, description = :description WHERE id = :id");
        return $stmt->execute($data);
    }

    public function deleteTicket($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tickets WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    public function getTicketsByAdmin($adminId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE admin_id = :admin_id");
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getTicketsByUser($user_id)
    {
        $stmt = $this->pdo->prepare("
        SELECT ticket_id, reference, title, description, status, priority, created_at
        FROM tickets
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTickets()
    {
        $stmt = $this->pdo->query("SELECT * FROM tickets ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAgentComment($ticketId, $agentId, $comment)
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO ticket_comments (ticket_id, agent_id, comment)
        VALUES (:ticket_id, :agent_id, :comment)
    ");
        $stmt->execute([

            'ticket_id' => $ticketId,
            'agent_id' => $agentId,
            'comment' => $comment
        ]);
    }
    public function getTicketComments(int $ticketId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.comment, c.created_at, u.user_name AS agent_name
            FROM ticket_comments c
            JOIN users u ON c.agent_id = u.user_id
            WHERE c.ticket_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketById(int $ticketId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT tickets.*, users.user_name AS assigned_by_user_name
            FROM tickets
            LEFT JOIN users ON tickets.user_id = users.user_id
            WHERE tickets.ticket_id = ?
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }




    function generateTicketRef($length = 8)
    {
        // Create a random alphanumeric string
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $ref = '';
        for ($i = 0; $i < $length; $i++) {
            $ref .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Optionally, add a timestamp for uniqueness
        $timestamp = date('YmdHis'); // e.g., 20260211123045

        return 'TICKET-' . $ref . '-' . $timestamp;
    }
    public function addComment(int $ticketId, int $agentId, string $comment): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO ticket_comments (ticket_id, agent_id, comment)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$ticketId, $agentId, $comment]);
    }

   
    public function createFullTicket($data, $file = null)
{
    try {

        $this->pdo->beginTransaction();

        // Insert ticket
        $stmt = $this->pdo->prepare("
            INSERT INTO tickets 
            (reference, title, description, email, status, priority, category_id, user_id, contact, support_email, created_at)
            VALUES 
            (:reference, :title, :description, :email, :status, :priority, :category_id, :user_id, :contact, :support_email, NOW())
        ");

        $stmt->execute([
            ':reference'     => $data['reference'],
            ':title'         => $data['title'],
            ':description'   => $data['description'],
            ':email'         => $data['email'],
            ':status'        => $data['status'],
            ':priority'      => $data['priority'],
            ':category_id'   => $data['category_id'],
            ':user_id'       => $data['user_id'],
            ':contact'       => $data['contact'],
            ':support_email' => $data['support_email']
        ]);

        $ticket_id = $this->pdo->lastInsertId();

        // Handle file upload
        if ($file && !empty($file['name'])) {

            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . "_" . basename($file['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {

                $fileStmt = $this->pdo->prepare("
                    INSERT INTO ticket_files 
                    (ticket_id, file_name, file_path, file_type)
                    VALUES (:ticket_id, :file_name, :file_path, :file_type)
                ");

                $fileStmt->execute([
                    ':ticket_id' => $ticket_id,
                    ':file_name' => $fileName,
                    ':file_path' => 'uploads/' . $fileName,
                    ':file_type' => mime_content_type($filePath)
                ]);
            }
        }

        $this->pdo->commit();

        return $ticket_id;

    } catch (Exception $e) {

        $this->pdo->rollBack();
        throw $e;
    }
}
public function notifyAdmins($ticketId, $customerEmail)
{
    $stmt = $this->pdo->prepare("SELECT email FROM users WHERE role_id = 1");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($admins as $admin) {

        $notif = $this->pdo->prepare("
            INSERT INTO notifications 
            (email, ticket_id, message, is_read, created_at)
            VALUES (:email, :ticket_id, :message, 0, NOW())
        ");

        $notif->execute([
            ':email'     => $admin['email'],
            ':ticket_id' => $ticketId,
            ':message'   => "New ticket (#{$ticketId}) submitted by {$customerEmail}."
        ]);
    }
}

public function sendCustomerEmail($data, $ticketRef)
{
    if (!function_exists('sendemail')) {
        return;
    }

    $subject = "Ticket Received - #{$ticketRef}";

    $body = "
        <h2>Thank You for Contacting Support</h2>
        <p>Your ticket has been submitted successfully.</p>
        <p><strong>Reference:</strong> {$ticketRef}</p>
        <p><strong>Title:</strong> " . htmlspecialchars($data['title']) . "</p>
        <p><strong>Status:</strong> Open</p>
        <br>
        <p>Support Team</p>
    ";

    sendemail($data['email'], "Customer", $subject, $body);
}
    public function getTicketByReference(string $ticketRef): ?array {
            $stmt = $this->pdo->prepare("
                SELECT * FROM tickets 
                WHERE reference = :reference 
                LIMIT 1
            ");
            $stmt->execute([':reference' => $ticketRef]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            return $ticket ?: null;
        }


     public function assignTicket(string $ticketRef, int $userId): bool {
        $stmt = $this->pdo->prepare("
            UPDATE tickets 
            SET user_id = :user_id, status = 'open' 
            WHERE reference = :reference
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':reference' => $ticketRef
        ]);
    }

    public function getTopLevelComments(int $ticketId): array
{
    $stmt = $this->pdo->prepare("
        SELECT ticket_comments.*, 
               COALESCE(users.user_name, ticket_comments.commenter_email, 'Agent') AS commenter_name
        FROM ticket_comments
        LEFT JOIN users ON ticket_comments.agent_id = users.user_id
        WHERE ticket_comments.ticket_id = :ticketId 
        AND ticket_comments.parent_comment_id IS NULL
        ORDER BY ticket_comments.created_at ASC
    ");
    $stmt->execute(['ticketId' => $ticketId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get replies for a comment
public function getReplies(int $parentCommentId): array
{
    $stmt = $this->pdo->prepare("
        SELECT ticket_comments.*, 
               COALESCE(users.user_name, ticket_comments.commenter_email, 'Agent') AS commenter_name
        FROM ticket_comments
        LEFT JOIN users ON ticket_comments.agent_id = users.user_id
        WHERE ticket_comments.parent_comment_id = :parentId
        ORDER BY ticket_comments.created_at ASC
    ");
    $stmt->execute(['parentId' => $parentCommentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}

