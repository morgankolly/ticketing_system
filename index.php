<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/admin/config/connection.php';
require_once __DIR__ . '/admin/models/TicketModel.php';
require_once __DIR__ . '/admin/helpers/functions.php'; 
include_once __DIR__ . '/admin/controllers/TicketController.php';

$TicketModel = new TicketModel($pdo);

// Fetch categories
$stmt = $pdo->prepare("SELECT category_id, category_name FROM category ORDER BY category_name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Submit a Support Ticket</h2>
    <form method="POST" action="" enctype="multipart/form-data">

        <input type="hidden" name="status" value="open">

        <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="5" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Your Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
                <option value="" disabled selected>Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['category_id'] ?>">
                        <?= htmlspecialchars($category['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Contact Number (optional)</label>
            <input type="text" name="contact" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Support Email (optional)</label>
            <input type="email" name="support_email" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Attach File (Images only)</label>
            <input type="file" name="file" class="form-control"
                   accept="image/png,image/jpeg,image/gif">
        </div>

        <button type="submit" name="createTicket" class="btn btn-primary">
            Submit Ticket
        </button>
    </form>
</div>
</body>
</html>
