<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'config/connection.php';
require_once 'compents/header.php';
require_once 'helpers/functions.php';
require_once 'models/UserModel.php';
$UserModel = new UserModel($pdo);


if (isset($_POST['updateUser'])) {

    $user_id  = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role_id  = isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0;

    if (!$user_id) {
        die('Invalid user ID');
    }

    $oldUser = $UserModel->getUserById($user_id);
    if (!$oldUser) {
        die('User not found');
    }

    // ✅ Keep old profile by default
    $profile = $oldUser['profile'];

    $uploadDir = __DIR__ . '/../uploads/profile/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // ✅ If new image uploaded
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === 0) {

        $ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array($ext, $allowed)) {
            die('Invalid file type.');
        }

        $newFileName = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $destination = $uploadDir . $newFileName;

        if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
            die('Move failed. Check folder path or permissions.');
        }

        // ✅ Delete old image if exists
        if (!empty($oldUser['profile'])) {

            $oldFilePath = $uploadDir . basename($oldUser['profile']);

            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // Save new path for DB
        $profile = '/ticketing/ticketing_system/uploads/profile/' . $newFileName;
    }

    // ✅ Update database
    $UserModel->updateUser($user_id, $username, $email, $role_id, $profile);

    header('Location: userManager.php');
    exit;
}

$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if (!$user_id) {
    die("Invalid user ID");
}

$user = $UserModel->getUserById($user_id);

if (!$user || !is_array($user)) {
    die("User not found in database");
}

// Load roles
$roles = $UserModel->getRoles();

if (!$roles) {
    die("No roles found");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
</head>

<body>
    <h2>Edit User</h2>
    <form action="UpdateUser.php" method="POST" enctype="multipart/form-data">
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

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role_id" class="form-select" required>
                <option value="">Select role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['role_id']; ?>" <?= $user['role_name'] == $role['role_name'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($user['profile'])): ?>
            <img src="<?= htmlspecialchars($user['profile']); ?>" width="80" style="margin-bottom:10px;">
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Profile Image</label>
            <input type="file" name="profile" class="form-control" accept="image/*">
        </div>

        <br>
        <button type="submit" name="updateUser" class="btn btn-primary">Update User</button>
        <a href="users_list.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>

</html>