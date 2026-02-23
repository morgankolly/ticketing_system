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
    function sendemail($email, $name, $subject, $body, $headers = [])
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Morgan Kolly Ticketing System');
            $mail->addAddress($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Custom headers (Message-ID, In-Reply-To, References)
            foreach ($headers as $key => $value) {
                $mail->addCustomHeader($key, $value);
            }

            if ($mail->send()) {
                return true;   // ✅ IMPORTANT
            } else {
                return false;  // ✅ IMPORTANT
            }

        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false; // ✅ IMPORTANT
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
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = "ssl";
            $mail->Port = 465;

            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Morgan Kolly Ticketing System');
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


    return 'T-' . $ref;
}
function generateMessageID($ticketRef)
{
    // Use your actual domain if you have one
    $domain = $_ENV['MAIL_DOMAIN'] ?? 'morgankolly.com';

    // Generate a unique ID
    $unique = bin2hex(random_bytes(8));

    return "<ticket-{$ticketRef}-{$unique}@{$domain}>";
}
function displayComments(array $comments)
{
    foreach ($comments as $comment): ?>
        <div class="border p-3 mb-2">
            <p><strong><?= htmlspecialchars($comment['commenter_name'] ?? 'Agent') ?></strong></p>
            <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
            <small><?= $comment['created_at'] ?></small>

            <!-- Reply form -->
            <form method="POST" class="mt-2">
                <input type="hidden" name="ticket_ref" value="<?= htmlspecialchars($_GET['ticket_ref']) ?>">
                <input type="hidden" name="parent_comment_id" value="<?= $comment['comment_id'] ?>">
                <textarea name="comment" class="form-control mb-2" placeholder="Reply..." required></textarea>
                <button type="submit" name="submit_comment" class="btn btn-sm btn-primary">Reply</button>
            </form>

            <?php if (!empty($comment['replies'])): ?>
                <div class="ms-4 mt-2">
                    <?php displayComments($comment['replies']); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach;
}

function fetchUserReplies(PDO $pdo) {
    // --- Connect to Gmail via IMAP ---
    $mailbox = '{imap.gmail.com:993/imap/ssl}INBOX';
    $username = 'morgankolly5@gmail.com';
    $password = 'fibzakrruxcoenjj'; // use app password

    $inbox = imap_open($mailbox, $username, $password);
    if (!$inbox) {
        error_log("IMAP connection failed: " . imap_last_error());
        return;
    }

    // --- Search for unseen messages ---
    $emails = imap_search($inbox, 'UNSEEN');
    if (!$emails) {
        imap_close($inbox);
        return; // nothing to process
    }

    $ticketModel = new TicketModel($pdo);

    foreach ($emails as $emailNumber) {
        $overview = imap_fetch_overview($inbox, $emailNumber, 0)[0];
        $message  = imap_fetchbody($inbox, $emailNumber, 1.1);

        if (!$message) {
            $message = imap_fetchbody($inbox, $emailNumber, 1);
        }

        $message = trim(strip_tags($message));

        // --- Parse ticket reference from subject ---
        // Example subject: "Re: Ticket T-356515"
        if (preg_match('/T-\d+/', $overview->subject, $matches)) {
            $ticketRef = $matches[0];
        } else {
            continue; // cannot find ticket reference
        }

        // --- Add the reply to ticket comments ---
        if ($ticketRef && !empty($message)) {
            $ticketModel->addEmailReply($ticketRef, $message);
        }

        // --- Mark email as read ---
        imap_setflag_full($inbox, $emailNumber, "\\Seen");
    }

    imap_close($inbox);
}

function decodeHtmlEnt($str) {
    $ret = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
    $p2 = -1;
    for(;;) {
        $p = strpos($ret, '&#', $p2+1);
        if ($p === FALSE)
            break;
        $p2 = strpos($ret, ';', $p);
        if ($p2 === FALSE)
            break;
            
        if (substr($ret, $p+2, 1) == 'x')
            $char = hexdec(substr($ret, $p+3, $p2-$p-3));
        else
            $char = intval(substr($ret, $p+2, $p2-$p-2));
            
        //echo "$char\n";
        $newchar = iconv(
            'UCS-4', 'UTF-8',
            chr(($char>>24)&0xFF).chr(($char>>16)&0xFF).chr(($char>>8)&0xFF).chr($char&0xFF) 
        );
        //echo "$newchar<$p<$p2<<\n";
        $ret = substr_replace($ret, $newchar, $p, 1+$p2-$p);
        $p2 = $p + strlen($newchar);
    }
    return $ret;
}
function cleanEmailContent($html) {
    $text = decodeHtmlEnt($html);   // decode entities
    $text = strip_tags($text);      // remove all HTML
    $text = trim($text);
    return $text;
}
?>