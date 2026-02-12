
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../controllers/UserController.php';
$userModel = new UserModel($pdo);

if (!isset($role_id)) {
    $role_id = '1'; 
    $user_name = 'Admin';
} elseif (!isset($role_id)) {
    $role_id = '2';
    $user_name = 'Agent';
}



?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords"
        content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />

    <link rel="canonical" href="https://demo-basic.adminkit.io/" />

    <title>Ticketing System</title>

    <link href="assets/css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<div class="wrapper">
    <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand" href="dashboard.php">
                <span class="align-middle">Ticketing System</span>
            </a>

            <ul class="sidebar-nav">
                <li class="sidebar-header">
                    Pages
                </li>

                <li class="sidebar-item active">
                    <a class="sidebar-link" href="ticket_manager.php">
                        <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Ticket
                            Manager</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="userManager.php">
                        <i class="align-middle" data-feather="user"></i> <span class="align-middle">Users</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="pages-sign-in.html">
                        <i class="align-middle" data-feather="log-in"></i> <span class="align-middle">Sign In</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="notifications.php">
                        <i class="align-middle" data-feather="bell"></i> <span
                            class="align-middle">Notifications</span>
                    </a>
                </li>



            </ul>


        </div>
    </nav>

    <div class="main">
        <nav class="navbar navbar-expand navbar-light navbar-bg">
            <a class="sidebar-toggle js-sidebar-toggle">
                <i class="hamburger align-self-center"></i>
            </a>

            <div class="navbar-collapse collapse">
                <ul class="navbar-nav navbar-align">
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                            <div class="position-relative">
                                <i class="align-middle" data-feather="bell"></i>
                    <li> <a href="logout.php">
                            Log Out <i class="entypo-logout right"></i> </a> </li>
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle" href="#" id="messagesDropdown" data-bs-toggle="dropdown">
                            <div class="notifications">
                              
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0"
                            aria-labelledby="messagesDropdown">
                            <div class="dropdown-menu-header">
                                <div class="position-relative">

                                </div>
                            </div>

                            <div class="dropdown-menu-footer">
                                <a href="#" class="text-muted">Show all messages</a>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                            <i class="align-middle" data-feather="settings"></i>
                        </a>

                        <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                            <img src="img/avatars/avatar.jpg" class="avatar img-fluid rounded me-1"
                                alt="Charles Hall" /> <span class="text-dark">Charles Hall</span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="pages-profile.html"><i class="align-middle me-1"
                                    data-feather="user"></i> Profile</a>
                            <a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="pie-chart"></i>
                                Analytics</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="index.html"><i class="align-middle me-1"
                                    data-feather="settings"></i> Settings & Privacy</a>
                            <a class="dropdown-item" href="#"><i class="align-middle me-1"
                                    data-feather="help-circle"></i> Help Center</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Log out</a>
                        </div>
                    </li>
                </ul>
            </div>


        </nav>

        </nav>

        

