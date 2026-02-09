<?php
class TicketModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createTicket($data) {
        $stmt = $this->pdo->prepare("INSERT INTO tickets (title, description, user_id) VALUES (:title, :description, :user_id)");
        return $stmt->execute($data);
    }

   

    public function updateTicket($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("UPDATE tickets SET title = :title, description = :description WHERE id = :id");
        return $stmt->execute($data);
    }

    public function deleteTicket($id) {
        $stmt = $this->pdo->prepare("DELETE FROM tickets WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    public function getTicketsByAdmin($adminId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE admin_id = :admin_id");
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
   public function getTicketsByUser($user_id) {
    $stmt = $this->pdo->prepare("
        SELECT ticket_id, title, description, status, priority, created_at
        FROM tickets
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}

