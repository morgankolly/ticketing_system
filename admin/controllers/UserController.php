<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once __DIR__ . '/../config/connection.php';
 require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/functions.php';


$UserModel = new UserModel($pdo);

$users = $UserModel->getAllUsers() ?? [];
$totalUsers = count($users);

$agents = $pdo->query(
    "SELECT user_id, user_name 
     FROM users 
     WHERE role_id = 'agent'"
)->fetchAll(PDO::FETCH_ASSOC);

$totalUsers = count($users);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getAllUsers'])) {
    $username = $_POST['user_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['Status'] ?? '';

}
$users = $pdo->query("SELECT user_id, user_name FROM users WHERE role_id = 'agent'")->fetchAll(PDO::FETCH_ASSOC);
$Users = $UserModel->getAllUsers();
$totalUsers= count($UserModel->getAllUsers());

if (!isset($userModel)) {
    return; // stop execution if loaded too early
}



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
     header("Location: usersManager.");
        exit;
    
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

    $profile = "http://localhost/ticketing/ticketing-system/uploads/profile/" . $filename;
}


    $UserModel->registerUser($username, $email, $password, $role);

    echo "User registered successfully!";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteUserById'])) {

    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId > 0) {

        try {

            // Optional: fetch profile image first (to delete file)
            $user = $userModel->getUserById($userId);

            if ($user && !empty($user['profile_image']) && file_exists($user['profile_image'])) {
                unlink($user['profile_image']); // delete image file
            }

            $userModel->deleteUserById($userId);

            $_SESSION['success'] = "User deleted successfully.";

        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to delete user.";
        }

    } else {
        $_SESSION['error'] = "Invalid user ID.";
    }

    header("Location: userManager.php");
    exit;
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

      $uploadDir = __DIR__ . '/../../uploads/profile/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // create if missing
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




