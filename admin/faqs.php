<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/compents/header.php';

/**
 * ✅ SAVE FAQ (from closed ticket)
 */
if (isset($_GET['save'])) {
    $ticketId = (int) $_GET['save'];

    $stmt = $pdo->prepare("
        INSERT INTO faqs (reference, title, answer)
        SELECT 
            t.reference,
            t.title,
            tc.comment
        FROM tickets t
        LEFT JOIN ticket_comments tc 
            ON t.ticket_id = tc.ticket_id
        WHERE t.ticket_id = :ticketId
        AND t.status = 'closed'
        LIMIT 1
    ");
    $stmt->execute(['ticketId' => $ticketId]);
}

/**
 * ✅ DELETE FAQ
 */
if (isset($_GET['delete'])) {
    $faqId = (int) $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM faqs WHERE faq_id = :id");
    $stmt->execute(['id' => $faqId]);
}

/**
 * ✅ FETCH CLOSED TICKETS (for generating FAQs)
 */
$stmt = $pdo->prepare("
    SELECT 
        t.ticket_id,
        t.reference,
        t.title,
        tc.comment AS answer
    FROM tickets t
    LEFT JOIN ticket_comments tc 
        ON t.ticket_id = tc.ticket_id
    WHERE t.status = 'closed'
    AND tc.agent_id IS NOT NULL
    GROUP BY t.ticket_id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$closedTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * ✅ FETCH SAVED FAQs
 */
$stmt = $pdo->prepare("SELECT * FROM faqs ORDER BY created_at DESC");
$stmt->execute();
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid p-4">
    <h3>FAQ Manager</h3>

    <!-- ✅ CLOSED TICKETS (Generate FAQs) -->
    <h5 class="mt-4">Closed Tickets (Generate FAQ)</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Title</th>
                <th>Answer</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($closedTickets as $ticket): ?>
            <tr>
                <td><?= htmlspecialchars($ticket['reference']) ?></td>
                <td><?= htmlspecialchars($ticket['title']) ?></td>
                <td><?= htmlspecialchars(substr($ticket['answer'], 0, 100)) ?>...</td>
                <td>
                    <a href="?save=<?= $ticket['ticket_id'] ?>" 
                       class="btn btn-success btn-sm">
                        Save as FAQ
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ✅ SAVED FAQS -->
    <h5 class="mt-5">Saved FAQs</h5>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Title</th>
                <th>Answer</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($faqs)): ?>
            <?php foreach ($faqs as $faq): ?>
                <tr>
                    <td><?= htmlspecialchars($faq['reference']) ?></td>
                    <td><?= htmlspecialchars($faq['title']) ?></td>
                    <td><?= htmlspecialchars(substr($faq['answer'], 0, 120)) ?>...</td>
                    <td>
                        <a href="?delete=<?= $faq['faq_id'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this FAQ?')">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No FAQs saved yet.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>