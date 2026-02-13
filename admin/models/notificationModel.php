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
}
