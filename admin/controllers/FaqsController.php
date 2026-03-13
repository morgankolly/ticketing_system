<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/FaqsModel.php';

// Initialize model
$faqModel = new FAQModel($pdo);
$popularTitles = $faqModel->getPopularClosedTitles(5);

foreach ($popularTitles as $ticket) {
    $latestRef = $ticket['latest_reference'];
    $ticketData = $faqModel->getTicketByReference($latestRef);
    if (!$ticketData) continue;

    $question = $ticketData['title'];
    $answer = $ticketData['description'];

    // Only insert if FAQ doesn't exist
    if (!$faqModel->exists($question)) {
        $faqModel->insertFAQ($question, $answer);
    }
}
$faqs = $faqModel->getAllFAQs();
?>