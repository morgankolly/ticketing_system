<?php
class FAQModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get titles of closed tickets that appear 5+ times
    public function getPopularClosedTitles($threshold = 5) {
        $stmt = $this->pdo->prepare("
            SELECT title, COUNT(*) AS total, MAX(reference) AS latest_reference
            FROM tickets
            WHERE status = 'closed'
            GROUP BY title
            HAVING COUNT(*) >= :threshold
        ");
        $stmt->execute(['threshold' => $threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if FAQ exists
    public function exists($title) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM faqs WHERE question = :title");
        $stmt->execute(['title' => $title]);
        return $stmt->fetchColumn() > 0;
    }

    // Insert FAQ
    public function insertFAQ($question, $answer) {
        $stmt = $this->pdo->prepare("
            INSERT INTO faqs (question, answer, created_at)
            VALUES (:question, :answer, NOW())
        ");
        return $stmt->execute(['question' => $question, 'answer' => $answer]);
    }

    // Get ticket by reference
    public function getTicketByReference($reference) {
        $stmt = $this->pdo->prepare("
            SELECT title, description 
            FROM tickets 
            WHERE reference = :reference
        ");
        $stmt->execute(['reference' => $reference]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch all FAQs
    public function getAllFAQs() {
        $stmt = $this->pdo->query("SELECT * FROM faqs ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>