<?php
class NotificationModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createNotification(int $ticketId, string $ticketRef, string $message): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (ticket_id, reference, message)
            VALUES (:ticket_id, :reference, :message)
        ");
        return $stmt->execute([
            ':ticket_id' => $ticketId,
            ':reference' => $ticketRef,
            ':message'   => $message
        ]);
    }

    public function notifyAssignedAgent(string $ticketRef, string $senderEmail): void
    {
        // Get ticket and assigned agent's email
        $stmt = $this->pdo->prepare("
            SELECT t.ticket_id, u.email AS agent_email
            FROM tickets t
            INNER JOIN users u ON u.user_id = t.user_id
            WHERE t.reference = :reference
            LIMIT 1
        ");
        $stmt->execute([':reference' => $ticketRef]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return; // ticket not found or not assigned

        $message = "User ({$senderEmail}) replied to ticket {$ticketRef}.";

        $notifStmt = $this->pdo->prepare("
            INSERT INTO notifications (email, ticket_id, message, is_read, created_at)
            VALUES (:email, :ticket_id, :message, 0, NOW())
        ");
        $notifStmt->execute([
            ':email'     => $row['agent_email'],
            ':ticket_id' => $row['ticket_id'],
            ':message'   => $message,
        ]);
    }
    
    public function getAgent(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function getNotifications(string $email): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications
            WHERE email = :email
            ORDER BY created_at DESC
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread(array $notifications): int {
        return count(array_filter($notifications, fn($n) => $n['is_read'] == 0));
    }

    
    public function markAllRead(string $email): void {
        $stmt = $this->pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE email = :email AND is_read = 0
        ");
        $stmt->execute(['email' => $email]);
    }   

    public function markRelatedAsRead(int $ticketId, string $reference = ''): int
    {
        $conditions = [];
        $params = [];

        if ($ticketId > 0) {
            $conditions[] = "ticket_id = :ticket_id";
            $params['ticket_id'] = $ticketId;
        }

        if (!empty($reference)) {
            $conditions[] = "reference = :reference";
            $params['reference'] = $reference;
        }

        if (empty($conditions)) {
            return 0;
        }

        $whereClause = implode(' OR ', $conditions);

        $stmt = $this->pdo->prepare("
            UPDATE notifications
               SET is_read = 1
             WHERE ($whereClause)
               AND is_read = 0
        ");

        try {
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("markRelatedAsRead failed: " . $e->getMessage());
            return 0;
        }
    }
}
