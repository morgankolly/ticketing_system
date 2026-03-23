<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/compents/header.php';
require_once __DIR__ . '/controllers/FaqsController.php'; 

$ticketRef = ''; // replace with dynamic ticket reference if needed

$stmt = $pdo->prepare("
    SELECT comment 
    FROM ticket_comments
    WHERE reference = :ticketRef
    AND agent_id = 'agent'
    ORDER BY created_at ASC
");
$stmt->execute([':ticketRef' => $ticketRef]);
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid p-4">
    <h3>FAQ Manager</h3>

    

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Title</th>
                <th>Answer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faqs as $faq): ?>
<tr>
    <td><?= htmlspecialchars($faq['reference']) ?></td>
    <td><?= htmlspecialchars($faq['title']) ?></td>
    <td><?= htmlspecialchars(substr($faq['answer'], 0, 120)) ?>...</td>
</tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>