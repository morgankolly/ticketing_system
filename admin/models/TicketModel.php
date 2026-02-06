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

    public function getTicketsByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTickets() {
        $stmt = $this->pdo->query("SELECT * FROM tickets");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
}
