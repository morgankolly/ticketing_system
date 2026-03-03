<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/compents/AgentHeader.php';
require_once __DIR__ . '/controllers/TicketController.php';



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <strong>Ticket</strong> Details
        </h1>
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="align-middle" data-feather="arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Ticket #<?= htmlspecialchars($ticket['reference'] ?? 'N/A') ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Reference:</th>
                            <td><code><?= htmlspecialchars($ticket['reference'] ?? 'N/A') ?></code></td>
                        </tr>
                        <tr>
                            <th>Title:</th>
                            <td><strong><?= htmlspecialchars($ticket['title'] ?? 'N/A') ?></strong></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge status-<?= strtolower(str_replace(' ', '-', $ticket['status'] ?? 'open')) ?>">
                                    <?= htmlspecialchars(ucfirst($ticket['status'] ?? 'Open')) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Priority:</th>
                            <td>
                                <span class="badge priority-<?= strtolower($ticket['priority'] ?? 'medium') ?>">
                                    <?= htmlspecialchars(ucfirst($ticket['priority'] ?? 'Medium')) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Created At:</th>
                            <td><?= date('M d, Y H:i', strtotime($ticket['created_at'] ?? 'now')) ?></td>
                        </tr>
                        <tr>
                            <th>Assigned By:</th>
                            <td><?= htmlspecialchars($ticket['assigned_by_username'] ?? 'System') ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= htmlspecialchars($ticket['email'] ?? 'N/A') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2"><strong>Description:</strong></h6>
                    <div class="p-3 bg-light rounded">
                        <?= nl2br(htmlspecialchars($ticket['description'] ?? 'No description provided.')) ?>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <a href="ticketComments.php?ticket_ref=<?= urlencode($ticket['reference'] ?? '') ?>" 
                       class="btn btn-primary btn-lg">
                        <i class="align-middle" data-feather="message-square"></i> View & Reply Comments
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>

