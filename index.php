<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/admin/config/connection.php';

require_once __DIR__ . '/admin/helpers/functions.php';

require_once __DIR__ . '/admin/models/TicketModel.php';
require_once __DIR__ . '/admin/models/UserModel.php';       // Only if you need user info
require_once __DIR__ . '/admin/models/CategoryModel.php';   // Only if you need categories

require_once __DIR__ . '/admin/controllers/TicketController.php';
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
    <title>Admin - Ticket Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords"
        content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />

    <link rel="canonical" href="https://demo-basic.adminkit.io/" />

    <title>Ticketing System</title>

    <link href="assets/css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
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
