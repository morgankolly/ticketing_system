<?php
ob_start();          // start buffering output
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/RoleModel.php';
require_once __DIR__ . '/compents/header.php';
$userModel = new UserModel($pdo);
$roleModel = new RoleModel($pdo);

// Fetch roles for the dropdown
$roles = $roleModel->getRoles();

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createUser'])) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id  = (int)($_POST['role_id'] ?? 0);

    try {
        // Validation
        if ($username === '' || $email === '' || $password === '' || $role_id <= 0) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        if ($userModel->userExists($username, $email)) {
            throw new Exception("Username or email already exists.");
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Handle profile upload
        $profile = 'uploads/users/default.png'; // default
        if (!empty($_FILES['profile']['name']) && $_FILES['profile']['error'] === 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['profile']['type'], $allowedTypes)) {
                throw new Exception("Only JPG, PNG, GIF images allowed.");
            }

            $uploadDir = __DIR__ . '/../uploads/users/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('user_') . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
                throw new Exception("Failed to upload profile image.");
            }

            $profile = 'uploads/users/' . $filename;
        }

        // Create user
        $userModel->registerUser($username, $email, $passwordHash, $role_id, $profile);

        $_SESSION['success'] = "User '$username' created successfully!";
        header("Location: userManager.php"); // avoid resubmission
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: userManager.php");
        exit;
    }
}

// Fetch all users for display (optional)
$users = $userModel->getAllUsers();
$totalUsers = count($users);
?>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
    + Add User
</button>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <!-- Display errors or success -->
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">Select role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>">
                                    <?= htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="profile" class="form-control" accept="image/*">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="createUser" class="btn btn-primary">Create User</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<h2>Users List</h2>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Profile</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($Users)): ?>
            <?php foreach ($Users as $user): ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($user['profile']) ?>" alt="" width="80" height="80"
                            style="object-fit:cover; border-radius:5px;">
                    </td>
                    <td><?= htmlspecialchars($user['user_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role_name']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td>
                        <a href="UpdateUsers.php?user_id=<?= $user['user_id'] ?>"
                            class="btn btn-default btn-sm btn-icon icon-left">
                            <i class="entypo-pencil"></i> Edit
                        </a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm btn-icon icon-left" name="deleteUserById">
                                <i class="entypo-cancel"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No users found.</td>
            </tr>
        <?php endif; ?>



    </tbody>
</table>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>