<?php


require_once 'require.php';

if (!isset($_SESSION['user_id'] )) {
    unset($_SESSION['username']);
    unset($_SESSION['user_id']);
    unset($_SESSION['password']);
    header("Location: ./index.php");
    exit;
}


ini_set('session.cookie_lifetime', 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);


