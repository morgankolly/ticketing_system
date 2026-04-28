    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    include_once __DIR__ . '/compents/header.php';
    include_once __DIR__ . '/config/connection.php';
    require_once __DIR__ . '/controllers/UserController.php';
    require_once __DIR__ . '/helpers/functions.php';
    require_once __DIR__ . '/controllers/TicketController.php';
    require_once __DIR__ . '/models/TicketModel.php';

    // Fetch returned tickets
    $stmt = $pdo->query("
        SELECT tickets.*, users.user_name AS returned_by_name
        FROM tickets
        LEFT JOIN users ON tickets.returned_by = users.user_id
        WHERE tickets.status = 'unresolved'
        ORDER BY tickets.returned_at DESC
    ");

    $returned_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch agents for reassignment
    $agents = $pdo->query("SELECT user_id, user_name FROM users WHERE role_id = '2'")
                ->fetchAll(PDO::FETCH_ASSOC);

                
    ?>

    <div class="container mt-4">

        <h3>Returned Tickets</h3>

        <?php if (!empty($returned_tickets)): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Title</th>
                        <th>Returned By</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Reassign</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returned_tickets as $ticket): ?>
                        <tr>
                            <td><?= htmlspecialchars($ticket['reference']) ?></td>
                            <td><?= htmlspecialchars($ticket['title']) ?></td>
                            <td><?= htmlspecialchars($ticket['returned_by_name'] ?? 'Unknown') ?></td>
                            <td>
    <?php if (!empty($ticket['return_reason'])): ?>
        <div class="p-2 bg-warning-subtle rounded">
            <?= nl2br(htmlspecialchars($ticket['return_reason'])) ?>
        </div>
    <?php else: ?>
        <span class="text-muted">No reason</span>
    <?php endif; ?>
</td>
                            <td><?= date('M d, Y H:i', strtotime($ticket['returned_at'])) ?></td>
                            <td>
                                <form method="POST" action="" class="d-flex gap-2 align-items-center">
                                                <input type="hidden" name="ticket_ref" value="<?= htmlspecialchars($ticket['reference']) ?>">
                                                <select name="user_id" class="form-select form-select-sm" style="min-width: 150px;" required>
                                                    <option value="">Select Agent</option>
                                                    <?php foreach ($agents as $agent): ?>
                                                        <option value="<?= $agent['user_id'] ?>" <?= isset($ticket['user_id']) && $ticket['user_id'] == $agent['user_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($agent['user_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <select name="priority" class="form-select form-select-sm" style="min-width: 120px;" required>
                                                    <option value="">Priority</option>
                                                    <?php
                                                    $priorities = ['low', 'medium', 'high', 'urgent'];
                                                    foreach ($priorities as $p): ?>
                                                        <option value="<?= $p ?>" <?= (strtolower($ticket['priority'] ?? '') === $p) ? 'selected' : '' ?>>
                                                            <?= ucfirst($p) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="assign_ticket" class="btn btn-primary btn-sm">
                                                    ReAssign
                                                </button>
                                            </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No returned tickets available.</div>
        <?php endif; ?>

    </div>