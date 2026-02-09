<?php

if (!isset($userModel)) {
    return; // stop execution if loaded too early
}

/* =========================
   USER DATA
========================= */

$users = $userModel->getAllUsers();
$totalUsers = count($users);
$users = $userModel->getAllUsers();

$agents = $pdo->query(
    "SELECT user_id, user_name 
     FROM users 
     WHERE role_id = 'agent'"
)->fetchAll(PDO::FETCH_ASSOC);

$totalUsers = count($users);

if (isset($_POST['createUser'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id  = $_POST['role_id'];

    if ($userModel->userExists($username, $email)) {
        die("Username or Email already taken!");
    }

    $uploadDir = dirname(__DIR__, 2) . '/uploads/profile/';
    $profile = null;

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['profile']['name']);
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['profile']['tmp_name'], $destination)) {
            die('❌ Failed to upload profile image');
        }

        $profile = "http://localhost/ticketing-system/uploads/profile/" . $filename;
    }

    $userModel->createUser($username, $email, $password, $role_id, $profile);

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

    $user_id = (int) $_POST['user_id'];

    if ($userModel->deleteUserById($user_id)) {
        echo "<script>
            alert('User deleted successfully');
            window.location.href='userManager.php';
        </script>";
        exit;
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
        header("Location: userManager.php?updated=1");
        exit;
    } else {
        echo "Failed to update user.";
    }
}


if (isset($_POST['createUser'])) {
    $username = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = (int) $_POST['role_id'];

    // Handle profile image upload
    $profileImage = 'uploads/users/default.png'; // default image
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array(strtolower($ext), $allowed)) {
            $newFileName = uniqid('user_') . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/users/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $destination = $uploadDir . $newFileName;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                $profileImage = 'uploads/users/' . $newFileName;
            }
        }
    }

    if ($userModel->userExists($username, $email)) {
        $error = "Username '$username' is already taken!";
    } elseif ($userModel->emailExists($email)) {
        $error = "Email '$email' is already registered!";
    } else {
        // Use registerUser to insert safely
        try {
            $userModel->registerUser($username, $email, $password, $role, $profileImage);
            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}


