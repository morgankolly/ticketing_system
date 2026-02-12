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
        SELECT ticket_id, title, description, status, priority, created_at
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

    public function assignTicket($ticketRef, $userId)
    {
        $stmt = $this->pdo->prepare("UPDATE tickets SET user_id = :user_id WHERE reference = :reference");
        if (
            !$stmt->execute([
                ':user_id' => $userId,
                ':reference' => $ticketRef
            ])
        ) {
            $errorInfo = $stmt->errorInfo();
            var_dump($errorInfo);  // shows the SQL error
            return false;
        }
        return true;
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

    public function markTicketAsInProgress(int $ticketId, int $agentId): void
    {
        $stmt = $this->pdo->prepare("
        UPDATE tickets
        SET status = 'in_progress',
            viewed_by = ?,
            viewed_at = NOW()
        WHERE ticket_id = ?
        AND status = 'open'
    ");
        $stmt->execute([$agentId, $ticketId]);
    }
}

