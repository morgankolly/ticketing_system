<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

<<<<<<< HEAD
include_once __DIR__ . '/compents/header.php';
include_once __DIR__ . '/helpers/functions.php';
include_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/UserModel.php';
$userModel = new UserModel($pdo);
$usersStmt = $pdo->query("
    SELECT users.user_id, users.user_name, users.email, users.created_at, users.profile, roles.role_name, users.role_id
    FROM users
    LEFT JOIN roles ON users.role_id = roles.role_id
    ORDER BY users.created_at DESC
");
$Users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all roles for dropdowns (like Add User modal)
$rolesStmt = $pdo->query("SELECT * FROM roles ORDER BY role_name ASC");
$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);



if (isset($_POST['createUser'])) {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id  = (int)($_POST['role_id'] ?? 0);

    if ($username === '' || $email === '' || $password === '' || $role_id === 0) {
        die('All fields are required');
    }

    /* FILE UPLOAD */
    $profilePath = null;

    if (!empty($_FILES['profile']['name'])) {
        $uploadDir = __DIR__ . '/uploads/users/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['profile']['name']);
        $target   = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile']['tmp_name'], $target)) {
            $profilePath = 'uploads/users/' . $fileName;
        }
    }

    /* CREATE USER — THIS WILL NOW WORK */
    $userModel->createUser(
        $username,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role_id,
        $profilePath
    );

    header("Location: userManager.php?success=1");
    exit;
}
=======
include_once __DIR__ . '/components/header.php';

>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)

?>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

          <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <form method="POST" action="" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role_id" required>
        <option value="1">Admin</option>
        <option value="2">Agent</option>
    </select>
    <input type="file" name="profile_image" accept="image/*">
    <button type="submit" name="createUser">Create User</button>
</form>
<?php endif; ?>



        </div>
    </div>
</div>



<button type="button" class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="addUserModal">
    + Add User
</button>


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
                <img src="<?= htmlspecialchars($user['profile']) ?>" alt="" width="80" height="80" style="object-fit:cover; border-radius:5px;">
            </td>
            <td><?= htmlspecialchars($user['user_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role_name']) ?></td>
            <td><?= htmlspecialchars($user['created_at']) ?></td>
            <td>
                <a href="UpdateUsers.php?user_id=<?= $user['user_id'] ?>" class="btn btn-default btn-sm btn-icon icon-left">
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
