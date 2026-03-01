<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once 'config/connection.php';
require_once 'compents/header.php';
require_once 'helpers/functions.php';
require_once 'controllers/UserController.php';
require_once 'models/UserModel.php';
require_once 'models/RoleModel.php';
$userModel = new UserModel($pdo);
$roleModel = new RoleModel($pdo);
$roles = $roleModel->getRoles();
$users = $userModel->getAllUsers();
$totalUsers = count($users);


if (isset($_POST['createUser'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = $_POST['role_id'];

    if ($UserModel->userExists($username, $email)) {
        echo "Username or Email already taken!";
        exit;
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ticketing/ticketing_system/uploads/profile/';

    // 1️⃣ Ensure folder exists and is writable
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            die('Failed to create upload directory. Check folder permissions!');
        }
    }

    // 2️⃣ Initialize profile variable
    $profile = 'default.png'; // default profile image if none uploaded

    // 3️⃣ Handle file upload
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $filename = time() . '_' . basename($_FILES['profile']['name']);
        $destination = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
            die('Failed to move uploaded file. Check folder permissions!');
        }

        // Set URL to the uploaded file
        $profile = '/ticketing/ticketing_system/uploads/profile/' . $filename;
    }

    // 4️⃣ Create user in database
    $UserModel->createUser($username, $email, $password, $role_id, $profile);

    echo "User created successfully!";
}

if (isset($_POST['deleteUserById'])) {

    $user_id = intval($_POST['user_id']);

    var_dump($_POST['deleteUserById']);

    if ($UserModel->deleteUserById($user_id)) {
        echo "<script>alert('User deleted successfully'); window.location.href='userManager.php';</script>";
    } else {
        echo "<script>alert('Failed to delete user');</script>";
    }
}




?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">

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
                        <img src="<?= !empty($user['profile']) ? htmlspecialchars($user['profile']) : 'uploads/profile/default.png'; ?>"
                            alt="<?= htmlspecialchars($user['user_name']) ?>" width="80" height="80"
                            style="object-fit:cover; border-radius:5px;">
                    </td>
                    <td><?= htmlspecialchars($user['user_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role_name']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>

                    <td>
                        <a href="updateUser.php?user_id=<?= $user['user_id'] ?>"
                            class="btn btn-default btn-sm btn-icon icon-left">
                            <i class="entypo-pencil"></i> Edit
                        </a>
                        <form method="POST" style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">

                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

                            <button type="submit" class="btn btn-danger btn-sm" name="deleteUserById">
                                Delete
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

<?php if (!empty($_SESSION['error'])): ?>
    <script>
        var addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        addUserModal.show();
    </script>
<?php endif; ?>
</body>

</html>