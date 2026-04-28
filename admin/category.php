<?php

 include_once __DIR__ . '/compents/header.php'; 
 require_once __DIR__ . '/config/connection.php';
 require_once __DIR__ . '/controllers/UserController.php';
 require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . 'models/CategoryModel.php';
?>
<form action="<?php echo $_SERVER['REQUEST_URI']?>" method="POST">
    <label>Add new category:</label><br>
    <input type="text" name="category_name" required>
    <button type="submit" name="insertCategory">Add Category</button>
</form>

