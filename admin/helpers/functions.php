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

    function sendemail(
        $email,
        $name,
        $subject,
        $body,
        $messageId = null,
        $inReplyTo = null,
        $references = null
    ) {
        $mail = new PHPMailer(true);

        try {

            // ===============================
            // SMTP CONFIG (OPTIMIZED)
            // ===============================
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // 🔥 PERFORMANCE SETTINGS
            $mail->Timeout = 10;
            $mail->SMTPKeepAlive = false;
            $mail->CharSet = 'UTF-8';

            // OPTIONAL: helps avoid SSL weird delays on XAMPP/mac
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // ===============================
            // SENDER / RECEIVER
            // ===============================
            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Morgan Kolly Ticketing System');
            $mail->addAddress($email, $name);

            // ===============================
            // SUBJECT SAFETY
            // ===============================
            if (is_array($subject)) {
                $subject = implode(' ', $subject);
            }
            $mail->Subject = trim($subject);

            // ===============================
            // CONTENT
            // ===============================
            $mail->isHTML(true);
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body); // fallback (important)

            // ===============================
            // HEADERS (FIXED)
            // ===============================

            if ($messageId) {
                $mail->addCustomHeader('Message-ID', $messageId);
            }

            if ($inReplyTo) {
                $mail->addCustomHeader('In-Reply-To', $inReplyTo);

                if ($references) {
                    $mail->addCustomHeader('References', $references);
                } else {
                    $mail->addCustomHeader('References', $inReplyTo);
                }
            }

            // ===============================
            // SEND
            // ===============================
            return $mail->send();

        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}


if (!function_exists('sendnotification')) {

    function sendnotification($subject, $email, $body)
    {
        $mail = new PHPMailer(true);

        try {

            // =========================
            // VALIDATE EMAIL
            // =========================
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email: " . $email);
            }

            // =========================
            // SMTP CONFIG
            // =========================
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];

            // Gmail recommended
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // =========================
            // DEBUG (TURN ON IF TESTING)
            // =========================
            // $mail->SMTPDebug = 2;
            // $mail->Debugoutput = 'error_log';

            // =========================
            // PERFORMANCE SETTINGS
            // =========================
            $mail->Timeout = 15;
            $mail->SMTPKeepAlive = false;
            $mail->CharSet = 'UTF-8';

            // safer SMTP options
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // =========================
            // SENDER
            // =========================
            $mail->setFrom(
                $_ENV['MAIL_USERNAME'],
                'Morgan Kolly Ticketing System'
            );

            $mail->addAddress($email);

            // =========================
            // CONTENT
            // =========================
            $mail->isHTML(true);

            $mail->Subject = is_array($subject)
                ? implode(' ', $subject)
                : $subject;

            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            // =========================
            // SEND
            // =========================
            $sent = $mail->send();

            if (!$sent) {
                error_log("Email failed: " . $mail->ErrorInfo);
            }

            return $sent;

        } catch (Exception $e) {

            error_log("sendnotification ERROR: " . $e->getMessage());

            return false;
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

function fetchUserReplies(PDO $pdo)
{
    $mailbox = '{imap.gmail.com:993/imap/ssl}INBOX';
    $username = 'morgankolly5@gmail.com';
    $password = 'fibzakrruxcoenjj';

    $inbox = imap_open($mailbox, $username, $password);
    if (!$inbox) {
        error_log("IMAP connection failed: " . imap_last_error());
        return;
    }

    $emails = imap_search($inbox, 'UNSEEN');
    if (!$emails) {
        imap_close($inbox);
        return;
    }

    $ticketModel = new TicketModel($pdo);

    foreach ($emails as $emailNumber) {
        $overview = imap_fetch_overview($inbox, $emailNumber, 0)[0];
        $structure = imap_fetchstructure($inbox, $emailNumber);

        // Use proper structure-aware plain text extraction
        $message = getCleanMessage($structure, $emailNumber, $inbox);

        // 🔥 FULL CLEAN PIPELINE
        $message = cleanEmailBody($message);
        $message = removeEmailQuotes($message);
        $message = trim($message);

        if (preg_match('/T-\d+/', $overview->subject, $matches)) {
            $ticketRef = $matches[0];
        } else {
            imap_setflag_full($inbox, $emailNumber, "\\Seen");
            continue;
        }

        if ($ticketRef && !empty($message)) {
            $commentId = $ticketModel->addEmailReply($ticketRef, $message);

            // Extract and save any attachments
            if ($commentId) {
                $ticket = $ticketModel->getTicketByReference($ticketRef);
                if ($ticket) {
                    extractAttachments($structure, $emailNumber, $inbox, $ticket['ticket_id'], $commentId, $pdo);
                }
            }
        }

        imap_setflag_full($inbox, $emailNumber, "\\Seen");
    }

    imap_close($inbox);
}


function decodeHtmlEnt($str)
{
    $ret = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
    $p2 = -1;
    for (; ; ) {
        $p = strpos($ret, '&#', $p2 + 1);
        if ($p === FALSE)
            break;
        $p2 = strpos($ret, ';', $p);
        if ($p2 === FALSE)
            break;

        if (substr($ret, $p + 2, 1) == 'x')
            $char = hexdec(substr($ret, $p + 3, $p2 - $p - 3));
        else
            $char = intval(substr($ret, $p + 2, $p2 - $p - 2));

        //echo "$char\n";
        $newchar = iconv(
            'UCS-4',
            'UTF-8',
            chr(($char >> 24) & 0xFF) . chr(($char >> 16) & 0xFF) . chr(($char >> 8) & 0xFF) . chr($char & 0xFF)
        );
        //echo "$newchar<$p<$p2<<\n";
        $ret = substr_replace($ret, $newchar, $p, 1 + $p2 - $p);
        $p2 = $p + strlen($newchar);
    }
    return $ret;
}
function cleanEmailContent($html)
{
    $text = decodeHtmlEnt($html);   // decode entities
    $text = strip_tags($text);      // remove all HTML
    $text = trim($text);
    return $text;
}
function processIncomingEmail($rawEmailContent)
{

    // Extract the plain text part or clean HTML
    $cleanBody = cleanEmailContent($rawEmailContent);

    // Remove email signatures and reply quotes (optional)
    $cleanBody = removeEmailQuotes($cleanBody);

    // Store in database
    return $cleanBody;
}



function removeEmailQuotes($content)
{
    $patterns = [
        '/On\s.+wrote:.*$/si',
        '/>.*$/m',
        '/---+\s*Forwarded message\s*---+.*$/si',
        '/Sent from my.*$/i',
        '/From:.*$/mi',
        '/Subject:.*$/mi'
    ];

    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '', $content);
    }

    return trim($content);
}

function sendTicketForReassignment($pdo, $reference)
{
    $stmt = $pdo->prepare("
        UPDATE tickets
        SET user_id = NULL,
            status = 'unresolved'
        WHERE reference = ?
    ");

    return $stmt->execute([$reference]);
}

function cleanEmailBody($body)
{
    // Decode quoted-printable
    $body = quoted_printable_decode($body);

    // Remove MIME boundaries
    $body = preg_replace('/--\S+/', '', $body);

    // Remove headers
    $body = preg_replace('/Content-Type:.*$/mi', '', $body);
    $body = preg_replace('/Content-Transfer-Encoding:.*$/mi', '', $body);

    // Remove "On ... wrote:"
    $body = preg_split('/On\s.+wrote:/i', $body)[0];

    // Remove forwarded messages
    $body = preg_split('/---+\s*Forwarded message\s*---+/i', $body)[0];

    // Remove quoted lines (>)
    $body = preg_replace('/^>.*$/m', '', $body);

    // Remove Gmail style reply blocks
    $body = preg_replace('/From:.*$/mi', '', $body);
    $body = preg_replace('/Sent:.*$/mi', '', $body);
    $body = preg_replace('/Subject:.*$/mi', '', $body);

    // Remove URLs like your localhost spam
    $body = preg_replace('/https?:\/\/\S+/', '', $body);

    // Clean multiple empty lines
    $body = preg_replace("/\n\s*\n+/", "\n\n", $body);

    return trim($body);
}

function getCleanMessage($structure, $messageNumber, $inbox)
{
    if (!$structure)
        return '';

    $body = '';

    // MULTIPART
    if (isset($structure->parts)) {

        foreach ($structure->parts as $index => $part) {

            // Prefer PLAIN TEXT
            if ($part->subtype == "PLAIN") {

                $body = imap_fetchbody($inbox, $messageNumber, $index + 1);

                if ($part->encoding == 3) {
                    $body = base64_decode($body);
                } elseif ($part->encoding == 4) {
                    $body = quoted_printable_decode($body);
                }

                return trim($body);
            }

            // Fallback to HTML if no plain text
            if ($part->subtype == "HTML" && empty($body)) {

                $body = imap_fetchbody($inbox, $messageNumber, $index + 1);

                if ($part->encoding == 3) {
                    $body = base64_decode($body);
                } elseif ($part->encoding == 4) {
                    $body = quoted_printable_decode($body);
                }

                // Convert HTML → clean text
                $body = strip_tags($body);

                return trim($body);
            }
        }
    }

    // SINGLE PART EMAIL
    $body = imap_body($inbox, $messageNumber);
    return trim($body);
}

function extractAttachments($structure, $messageNumber, $inbox, $ticketId, $commentId, $pdo, $partNumber = null)
{
    if (!isset($structure->parts)) return;

    foreach ($structure->parts as $index => $part) {

        $currentPartNumber = $partNumber
            ? $partNumber . '.' . ($index + 1)
            : ($index + 1);

        $isAttachment = false;
        $fileName = '';

        if (isset($part->disposition) && strtolower($part->disposition) == 'attachment') {
            $isAttachment = true;
        }

        if ($part->ifdparameters) {
            foreach ($part->dparameters as $param) {
                if (strtolower($param->attribute) == 'filename') {
                    $fileName = $param->value;
                    $isAttachment = true;
                }
            }
        }

        if ($part->ifparameters) {
            foreach ($part->parameters as $param) {
                if (strtolower($param->attribute) == 'name') {
                    $fileName = $param->value;
                    $isAttachment = true;
                }
            }
        }

        if ($isAttachment && $fileName) {

            $data = imap_fetchbody($inbox, $messageNumber, $currentPartNumber);

            if ($part->encoding == 3) {
                $data = base64_decode($data);
            } elseif ($part->encoding == 4) {
                $data = quoted_printable_decode($data);
            }

            $newFileName = uniqid() . '_' . $fileName;
            $filePath = __DIR__ . '/../uploads/tickets/' . $newFileName;

            file_put_contents($filePath, $data);

            $stmt = $pdo->prepare("
                INSERT INTO ticket_attachments 
                (ticket_id, comment_id, file_name, original_name, file_size)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $ticketId,
                $commentId,
                $newFileName,
                $fileName,
                strlen($data)
            ]);
        }

        // 🔁 RECURSION (CRITICAL)
        if (isset($part->parts)) {
            extractAttachments($part, $messageNumber, $inbox, $ticketId, $commentId, $pdo, $currentPartNumber);
        }
    }
}



?>