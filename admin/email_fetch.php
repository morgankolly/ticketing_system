<?php

require_once 'config/connection.php';
require_once 'models/TicketModel.php';
require_once 'helpers/functions.php';

$hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
$username = $_ENV['MAIL_USERNAME'];
$password = $_ENV['MAIL_PASSWORD'];

$inbox = @imap_open($hostname, $username, $password);

if (!$inbox) {
    echo "IMAP Error: " . imap_last_error();
    exit;
}


$emails = imap_search($inbox, 'UNSEEN');


if ($emails) {
    foreach ($emails as $email_number) {

        $overview = imap_fetch_overview($inbox, $email_number, 0);
        $structure = imap_fetchstructure($inbox, $email_number);
        $from     = $overview[0]->from ?? '';
        $subject  = $overview[0]->subject ?? '';
        $message  = imap_fetchbody($inbox, $email_number, 1);

        // Decode body
        if (!empty($structure->parts[0])) {
            $part = $structure->parts[0];
            if ($part->encoding == 3) $message = base64_decode($message);
            if ($part->encoding == 4) $message = quoted_printable_decode($message);
        }

        // Clean reply (strip quoted text)
        $message = preg_split('/On .* wrote:/', $message)[0];
        $message = trim($message);

        // Extract ticket reference
        if (preg_match('/T-\d+/', $subject, $matches)) {
            $ticketRef = $matches[0];

            // --- Find last agent comment sent to this user for threading ---
            $stmt = $pdo->prepare("
                SELECT comment_id 
                FROM ticket_comments 
                WHERE reference = ? AND agent_id IS NOT NULL 
                  AND (commenter_email = ? OR 1=1)
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$ticketRef, $from]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            $parentCommentId = $parent['comment_id'] ?? null;

            $inserted = $ticketModel->insertUserEmailReply(
                $ticketRef,
                $message,
                $from,
                $parentId
            );

            if ($inserted) {
                echo "Inserted reply for $ticketRef from $from under comment ID $parentId<br>";
            } else {
                echo "Ticket $ticketRef not found.<br>";
            }
        } else {
            echo "No ticket reference in subject: $subject<br>";
        }

        // Mark as read
        imap_setflag_full($inbox, $email_number, "\\Seen");
    }
} else {
    echo "No unread emails found.<br>";
}

imap_close($inbox);

