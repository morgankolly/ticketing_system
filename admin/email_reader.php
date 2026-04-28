<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/TicketModel.php';

$ticketModel = new TicketModel($pdo);

// =========================
// EMAIL CONFIG
// =========================
$hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
$username = 'morgankolly5@gmail.com';
$password = 'YOUR_APP_PASSWORD';

$inbox = imap_open($hostname, $username, $password);

if (!$inbox) {
    die("IMAP connection failed: " . imap_last_error());
}

// =========================
// GET EMAILS
// =========================
$emails = imap_search($inbox, 'UNSEEN');

if (!$emails) {
    echo "No new emails";
    exit;
}

rsort($emails);

// =========================
// PROCESS EMAILS
// =========================
foreach ($emails as $email_number) {

    $overview = imap_fetch_overview($inbox, $email_number, 0)[0];
    $structure = imap_fetchstructure($inbox, $email_number);

    $subject = $overview->subject ?? '';
    $message_id = $overview->message_id ?? uniqid();

    $from = $overview->from ?? '';
    preg_match('/<(.+?)>/', $from, $m);
    $senderEmail = $m[1] ?? $from;

    // =========================
    // BODY
    // =========================
    $body = getBody($inbox, $email_number);

    // =========================
    // FIND TICKET
    // =========================
    $ticketId = null;
    $ticketRef = null;

    if (preg_match('/T-\d+/', $subject . ' ' . $body, $m)) {
        $ticketRef = $m[0];
    }

    if ($ticketRef) {
        $stmt = $pdo->prepare("SELECT ticket_id FROM tickets WHERE reference = ?");
        $stmt->execute([$ticketRef]);
        $ticket = $stmt->fetch();
        $ticketId = $ticket['ticket_id'] ?? null;
    }

    if (!$ticketId) {
        continue;
    }

    // =========================
    // ATTACHMENTS (RECURSIVE)
    // =========================
    $attachments = extractAttachments($inbox, $email_number, $structure);

    foreach ($attachments as $att) {

        $uploadDir = __DIR__ . '/uploads/tickets/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newName = uniqid() . '_' . $att['filename'];
        $path = $uploadDir . $newName;

        file_put_contents($path, $att['data']);

        $stmt = $pdo->prepare("
            INSERT INTO ticket_attachments
            (ticket_id, file_name, original_name, file_size, mime_type)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $ticketId,
            $newName,
            $att['filename'],
            filesize($path),
            mime_content_type($path)
        ]);
    }

    imap_setflag_full($inbox, $email_number, "\\Seen");

    echo "Processed: $subject\n";
}

imap_close($inbox);

// =========================
// BODY FUNCTION
// =========================
function getBody($inbox, $email_number)
{
    $structure = imap_fetchstructure($inbox, $email_number);

    if (!isset($structure->parts)) {
        return imap_body($inbox, $email_number);
    }

    foreach ($structure->parts as $i => $part) {
        if ($part->subtype == "PLAIN") {
            $body = imap_fetchbody($inbox, $email_number, $i + 1);

            return match ($part->encoding) {
                3 => base64_decode($body),
                4 => quoted_printable_decode($body),
                default => $body
            };
        }
    }

    return '';
}

// =========================
// ATTACHMENT EXTRACTOR
// =========================
function extractAttachments($inbox, $email_number, $structure, $partNumber = '')
{
    $attachments = [];

    if (!isset($structure->parts)) return [];

    foreach ($structure->parts as $i => $part) {

        $current = $partNumber ? $partNumber . '.' . ($i + 1) : ($i + 1);

        $filename = null;

        if (!empty($part->dparameters)) {
            foreach ($part->dparameters as $obj) {
                if (strtolower($obj->attribute) == 'filename') {
                    $filename = $obj->value;
                }
            }
        }

        if (!empty($part->parameters)) {
            foreach ($part->parameters as $obj) {
                if (strtolower($obj->attribute) == 'name') {
                    $filename = $obj->value;
                }
            }
        }

        if ($filename) {

            $data = imap_fetchbody($inbox, $email_number, $current);

            switch ($part->encoding) {
                case 3:
                    $data = base64_decode($data);
                    break;
                case 4:
                    $data = quoted_printable_decode($data);
                    break;
            }

            $attachments[] = [
                'filename' => $filename,
                'data' => $data
            ];
        }

        if (isset($part->parts)) {
            $attachments = array_merge(
                $attachments,
                extractAttachments($inbox, $email_number, $part, $current)
            );
        }
    }

    return $attachments;
}
$body = getCleanMessage($structure, $emailNumber, $inbox);