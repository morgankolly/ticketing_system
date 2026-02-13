<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

require __DIR__ . '/../../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/../../')->load();
require_once __DIR__ . '/../config/connection.php';


if (!function_exists('sendemail')) {
    function sendemail($email, $name, $subject, $body)
    {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'morgankolly5@gmail.com';
            $mail->Password = 'aypx pwwf rbnq swdq';
            $mail->SMTPSecure = "ssl";
            $mail->Port = 465;

            //Recipients
            $mail->setFrom('morgankolly5@gmail.com', 'Morgan Kolly Ticketing System');
            $mail->addAddress($email, $name);

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

if (!function_exists('sendnotification')) {
    function sendnotification($subject, $email, $body)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'morgankolly5@gmail.com';
            $mail->Password = 'aypx pwwf rbnq swdq';
            $mail->SMTPSecure = "ssl";
            $mail->Port = 465;

            $mail->setFrom('morgankolly5@gmail.com', 'Morgan Kolly Ticketing System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

function generateTicketRef($length = 6)
{
    $characters = '0123456789';
    $ref = '';
    for ($i = 0; $i < $length; $i++) {
        $ref .= $characters[rand(0, strlen($characters) - 1)];
    }


    return 'TN-' . $ref ;
}
