<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/TicketModel.php';

$reference = $_GET['ref'] ?? '';

if (!$reference) {
    exit("Invalid ticket reference.");
}

// Close ticket using reference
$stmt = $pdo->prepare("
    UPDATE tickets 
    SET status = 'closed' 
    WHERE reference = ?
");
$stmt->execute([$reference]);

if ($stmt->rowCount() == 0) {
    exit("Ticket not found or already closed.");
}



// Get ticket information
$ticket = $pdo->prepare("
    SELECT reference, title 
    FROM tickets 
    WHERE reference = ?
");
$ticket->execute([$reference]);
$data = $ticket->fetch(PDO::FETCH_ASSOC);

$title = $data['title'];

// Get latest agent comment as solution
$commentStmt = $pdo->prepare("
    SELECT comment 
    FROM ticket_comments
    WHERE reference = (
        SELECT reference FROM tickets WHERE reference = ?
    )
    ORDER BY created_at DESC
    LIMIT 1
");

$commentStmt->execute([$reference]);
$commentData = $commentStmt->fetch(PDO::FETCH_ASSOC);

$answer = $commentData['comment'] ?? 'Solution provided by support team.';

// Prevent duplicate FAQ
$check = $pdo->prepare("SELECT faq_id FROM faqs WHERE reference = ?");
$check->execute([$reference]);

if (!$check->fetch()) {

    $faqInsert = $pdo->prepare("
        INSERT INTO faqs (reference, title, answer)
        VALUES (?, ?, ?)
    ");

    $faqInsert->execute([$reference, $title, $answer]);
}

echo "<script>
alert('Ticket closed successfully. Thank you!');
window.location.href='../index.php';
</script>";