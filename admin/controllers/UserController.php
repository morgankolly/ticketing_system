<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getAllUsers'])) {
    $username = $_POST['user_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['Status'] ?? '';

}
$Users = $UserModel->getAllUsers();
$totalUsers= count($UserModel->getAllUsers());
if (isset($_POST['createUser'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id  = $_POST['role_id'];

    if ($UserModel->userExists($username, $email)) {
        echo "Username or Email already taken!";
        exit;
    }

   $uploadDir = dirname(__DIR__, 2) . '/uploads/profile/'; // admin/controllers → ticketing-system
$profile = null;

if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['profile']['name']);
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
        die('❌ Failed to move uploaded file. Check folder permissions.');
    }

    $profile = "http://localhost/ticketing-system/uploads/profile/" . $filename;
}
    $UserModel->createUser($username, $email, $password, $role_id, $profile);

    echo "User created successfully!";
}






if (isset($_POST['registerUser'])) {

    $username = $_POST['username'];
    $email = $_POST['email'];


    if ($UserModel->userExists($username, $email)) {
    }
    $uploadDir = __DIR__ . '/../uploads/profile/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$profile = null;
if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['profile']['name']);
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
        die('Failed to move uploaded file. Check folder permissions.');
    }

    $profile = "http://localhost/ticketing-system/uploads/profile/" . $filename;
}


    $UserModel->registerUser($username, $email, $password, $role);

    echo "User registered successfully!";
}



if (isset($_POST['deleteUserById'])) {

    $user_id = intval($_POST['user_id']);

    var_dump($_POST['deleteUserById']);

    if ($UserModel->deleteUserById($user_id)) {
        echo "<script>alert('User deleted successfully'); window.location.href='usersManager.php';</script>";
    } else {
        echo "<script>alert('Failed to delete user');</script>";
    }
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $user = $UserModel->getUserById($user_id);
}




if (isset($_POST['updateUser'])) {

    $user_id  = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $role_id  = $_POST['role_id'];

    $oldUser = $UserModel->getUserById($user_id);
    if (!$oldUser) {
        die('User not found');
    }

    $profile = $oldUser['profile'];


    $uploadDir = dirname(__DIR__, 3) . '/uploads/profile/';

    if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die('❌ Failed to create upload directory');
    }
}




    if (
        isset($_FILES['profile'])
    ) {

        $filename = time() . '_' . basename($_FILES['profile']['name']);
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
            die('Failed to move uploaded file. Check folder permissions.');
        }
        $profile = "http://localhost/ticketing-system/uploads/profile/" . $filename;
    }

    $updated = $UserModel->updateUser(
        $user_id,
        $username,
        $email,
        $role_id,
        $profile
    );

    if ($updated) {
        header("Location: usersManager.php?updated=1");
        exit;
    } else {
        echo "Failed to update user.";
    }
}





