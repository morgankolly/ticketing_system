<?php
 
require_once __DIR__ . '/../config/connection.php';  // defines $pdo
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


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createUser'])) {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id  = (int) ($_POST['role_id'] ?? 0);

    try {
        // --- Validation ---
        if ($username === '' || $email === '' || $password === '' || $role_id <= 0) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // --- Hash the password ---
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // --- Handle profile image ---
        $profile = null;
        if (!empty($_FILES['profile']['name']) && $_FILES['profile']['error'] === 0) {

            $uploadDir = __DIR__ . '/../../users/'; // outside controllers folder

            // Create folder if missing
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    throw new Exception("Failed to create upload directory: $uploadDir");
                }
            }

            // Validate file type
            $ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid file type. Allowed: " . implode(', ', $allowed));
            }

            $fileName = uniqid('user_') . '.' . $ext;
            $destination = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
                throw new Exception("Failed to move uploaded file.");
            }

            // Store relative path in DB
            $profile = 'uploads/users/' . $fileName;
        }

        // --- Insert user into DB ---
        $userModel->createUser($username, $email, $passwordHash, $role_id, $profile);

        $_SESSION['success'] = "User created successfully!";
        header("Location: userManager.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: userManager.php");
        exit;
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




