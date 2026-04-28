<?php
class NotificationModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

 public function createNotification(
    int $agentId,
    int $ticketId,
    string $ticketRef,
    string $message
): bool {

    $stmt = $this->pdo->prepare("
        INSERT INTO notifications (agent_id, ticket_id, reference, message, is_read, created_at)
        VALUES (:agent_id, :ticket_id, :reference, :message, 0, NOW())
    ");

    return $stmt->execute([
        ':agent_id' => $agentId,
        ':ticket_id' => $ticketId,
        ':reference' => $ticketRef,
        ':message'   => $message
    ]);
}

   public function notifyAssignedAgent(string $ticketRef, string $senderEmail): void
{
    $stmt = $this->pdo->prepare("
        SELECT ticket_id, user_id 
        FROM tickets 
        WHERE reference = :reference 
        LIMIT 1
    ");
    $stmt->execute([':reference' => $ticketRef]);

    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket || empty($ticket['user_id'])) return;

    $message = "User ({$senderEmail}) replied to ticket {$ticketRef}.";

    $this->createNotification(
        (int)$ticket['user_id'],
        (int)$ticket['ticket_id'],
        $ticketRef,
        $message
    );
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
    public function getAllNotifications()
{
    $stmt = $this->pdo->query("
        SELECT n.*, t.reference
        FROM notifications n
        LEFT JOIN tickets t ON t.ticket_id = n.ticket_id
        ORDER BY n.created_at DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getUnreadCount()
{
    $stmt = $this->pdo->query("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE is_read = 0
    ");

    return $stmt->fetchColumn();
}
public function markAllAsRead()
{
    $stmt = $this->pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE is_read = 0
    ");

    $stmt->execute();
}



  public function getNotificationsByAgent(int $agentId): array {
    $stmt = $this->pdo->prepare("
        SELECT * FROM notifications 
        WHERE agent_id = :agent_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute(['agent_id' => $agentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getUnreadCountByAgent(int $agentId): int {
    $stmt = $this->pdo->prepare("
        SELECT COUNT(*) FROM notifications 
        WHERE agent_id = :id AND is_read = 0
    ");
    $stmt->execute(['id' => $agentId]);
    return (int)$stmt->fetchColumn();
}

public function markAllAsReadByAgent(int $agentId): void {
    $stmt = $this->pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE agent_id = :id AND is_read = 0
    ");
    $stmt->execute(['id' => $agentId]);
}
public function getNotificationsByReference(string $reference): array
{
    $stmt = $this->pdo->prepare("
        SELECT * 
        FROM notifications 
        WHERE reference = ?
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$reference]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
}
