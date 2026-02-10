<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/compents/header.php';
$user_id = $_GET["user_id"];
$Users = $UserModel->getUserById((int) $user_id);
?>


<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="path_to_your_css.css"> <!-- Optional -->
</head>

<body>
    <h2>Edit User</h2>
    <form action="UpdateUsers.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?= $user['user_id']; ?>">
        

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['user_name']); ?>"
                required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>"
                required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role_id" class="form-control" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['role_id']; ?>" <?= $user['role_id'] == $role['role_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Profile Image</label>

            <input type="file" name="profile" class="form-control" accept="image/*"
                value="<?= $user['profile'] ?? '' ?>">

        </div>



        <br>
        <button type="submit" name="updateUser" class="btn btn-primary">Update User</button>
        <a href="users_list.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>

</html>


