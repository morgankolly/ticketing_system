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
<<<<<<< HEAD
<<<<<<< HEAD

    if (!$user || !password_verify($password, $user["password"])) {
        echo "<script>alert('Invalid email or password.'); window.location.href='index.php';</script>";
        exit;
    }

    $_SESSION['user_id']   = $user["user_id"];
    $_SESSION['username'] = $user["username"];
    $_SESSION['role_id']  = $user["role_id"];

    if ($user["role_id"] == 1) {
        header("Location: dashboard.php");
    } elseif ($user["role_id"] == 2) {
        header("Location: agentDashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
=======
    if (!$user) {
=======

    if (!$user || !password_verify($password, $user["password"])) {
>>>>>>> c8ab191 (added agent dashboard and agent ticketpage)
        echo "<script>alert('Invalid email or password.'); window.location.href='index.php';</script>";
        exit;
    }

    $_SESSION['user_id']   = $user["user_id"];
    $_SESSION['username'] = $user["username"];
    $_SESSION['role_id']  = $user["role_id"];

    if ($user["role_id"] == 1) {
        header("Location: dashboard.php");
    } elseif ($user["role_id"] == 2) {
        header("Location: agentDashboard.php");
    } else {
        header("Location: index.php");
    }
<<<<<<< HEAD
>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)
=======
    exit;
>>>>>>> c8ab191 (added agent dashboard and agent ticketpage)
}









