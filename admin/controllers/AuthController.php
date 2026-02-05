<?php
if (isset($_POST['register'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];


    $result = $user->validateUserInput($username, $email);

    if ($result !== true) {
        echo "<p style='color:red;'>$result</p>";
        exit;
    }


    $stmt = $pdo->prepare("
        INSERT INTO Users (username, email, password) 
        VALUES (:username, :email, :password)
    ");

    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    echo "<p style='color:green;'>Registration successful!</p>";
}

if (isset($_POST["login"]) && isset($_POST["action"]) && $_POST["action"] === "login_user") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $user = $UserModel->getUserByEmail($email);

    if (!$user) {
        echo "<script>alert('Invalid email or password.'); window.location.href='index.php';</script>";
        exit;
    }

    if (!password_verify($password, $user["password"])) {
        echo "<script>alert('Incorrect password.'); window.location.href='index.php';</script>";
        exit;
    }
    $_SESSION['username'] = $user["user_name"];
    $_SESSION['user_id'] = $user["user_id"];
    $_SESSION['role_id'] = $user["role_id"];

    header("Location: dashboard.php");
    exit;
}









