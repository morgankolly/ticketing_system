    <?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once __DIR__ . '/config/connection.php';
    require_once __DIR__ . '/models/TicketModel.php';
    require_once __DIR__ . '/compents/agentHeader.php';
    require_once __DIR__ . '/controllers/TicketController.php';

    $status = $_GET['status'] ?? null;

    $reference = trim($_GET['reference'] ?? '');
    $TicketModel = new TicketModel($pdo);
    $UserModel = new UserModel($pdo);
    $agentId = $_SESSION['user_id'];
    $assignedTickets = $TicketModel->getTicketsByUser($agentId);

   $assignedTickets = $TicketModel->getTicketsByUser($agentId);

$reference = trim($_GET['reference'] ?? '');
$ticketModel = new TicketModel($pdo);
$tickets = $ticketModel->getAssignedTickets($_SESSION['user_id'], $reference);
if ($reference) {

    // search by reference
    $stmt = $pdo->prepare("
        SELECT *
        FROM tickets
        WHERE user_id = :agent
        AND reference LIKE :reference
        ORDER BY created_at DESC
    ");

    $stmt->execute([
        ':agent' => $agentId,
        ':reference' => "%$reference%"
    ]);

} elseif ($status) {

    // filter by status from dashboard
    $stmt = $pdo->prepare("
        SELECT *
        FROM tickets
        WHERE user_id = :agent
        AND status = :status
        ORDER BY created_at DESC
    ");

    $stmt->execute([
        ':agent' => $agentId,
        ':status' => $status
    ]);

} else {

    // show all assigned tickets
    $stmt = $pdo->prepare("
        SELECT *
        FROM tickets
        WHERE user_id = :agent
        ORDER BY created_at DESC
    ");

    $stmt->execute([
        ':agent' => $agentId
    ]);
}

$assignedTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Assigned Tickets</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/custom.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    </head>
    <body>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <strong>My</strong> Assigned Tickets
            </h1>
        </div>

        <div class="card mb-4">
    <div class="card-body">

        <form method="GET" class="mb-3 d-flex gap-2">

            <input type="text" 
                name="reference"
                class="form-control"
                placeholder="Enter Ticket Reference (e.g. T-205136)"
                value="<?= htmlspecialchars($_GET['reference'] ?? '') ?>">

            <button type="submit" class="btn btn-primary">
                Find Ticket
            </button>

            <a href="agentTickets.php" class="btn btn-secondary">
                Reset
            </a>

        </form>

    </div>
</div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle me-2" data-feather="ticket"></i>
                    All Assigned Tickets (<?= count($assignedTickets) ?> total)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($assignedTickets)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <p class="text-muted mb-0">No tickets assigned to you yet.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($assignedTickets as $ticket): ?>
                                    <tr class="ticket-list-item status-<?= strtolower(str_replace(' ', '-', $ticket['status'] ?? 'open')) ?>" style="border-left: 4px solid;">
                                        <td>
                                            <div class="ticket-reference">
                                                <i class="align-middle me-1" data-feather="hash" style="width: 14px; height: 14px;"></i>
                                                <?= htmlspecialchars($ticket['reference'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($ticket['title'] ?? 'N/A') ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(substr($ticket['description'] ?? '', 0, 50)) . (strlen($ticket['description'] ?? '') > 50 ? '...' : '') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge status-badge status-<?= strtolower(str_replace(' ', '-', $ticket['status'] ?? 'open')) ?>">
                                                <?= htmlspecialchars(ucfirst($ticket['status'] ?? 'Open')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge priority-badge priority-<?= strtolower($ticket['priority'] ?? 'medium') ?>">
                                                <i class="align-middle me-1" data-feather="flag" style="width: 12px; height: 12px;"></i>
                                                <?= htmlspecialchars(ucfirst($ticket['priority'] ?? 'Medium')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="align-middle me-1" data-feather="calendar" style="width: 12px; height: 12px;"></i>
                                                <?= date('M d, Y', strtotime($ticket['created_at'] ?? 'now')) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="viewTickets.php?ticket_ref=<?= urlencode($ticket['reference'] ?? '') ?>" 
                                            class="btn btn-primary btn-sm">
                                                <i class="align-middle" data-feather="message-square"></i> View & Comment
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    </body>
    </html>
