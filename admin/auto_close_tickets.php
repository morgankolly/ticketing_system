<?php
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/TicketModel.php';

$ticketModel = new TicketModel($pdo);
$ticketModel->autoCloseTicketsBySystem();