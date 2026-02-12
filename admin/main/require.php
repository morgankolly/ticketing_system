<?php

if (!defined('APPROOT')) {
    define('APPROOT', dirname(dirname(__DIR__))); // project root
}
require_once __DIR__ . '/Model.php';

require_once APPROOT .  '/admin/controllers/AuthController.php';
require_once APPROOT .  '/admin/controllers/UserController.php';
require_once APPROOT .  '/admin/controllers/CategoryController.php';
require_once APPROOT .  '/admin/controllers/TicketController.php';
require_once APPROOT .  '/admin/controllers/RolesController.php';
require_once APPROOT .  '/admin/controllers/ContactController.php';

