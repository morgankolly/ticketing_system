<?php

require_once __DIR__ . '/config/connection.php';

$inbox = imap_open(
    "{imap.gmail.com:993/imap/ssl}INBOX",
    "morgankolly5@gmail.com",
    "fibzakrruxcoenjj"
);

if (!$inbox) {
    die('Cannot connect: ' . imap_last_error());
}

$emails = imap_search($inbox, 'UNSEEN');

if ($emails) {

    foreach ($emails as $email_number) {

        $overview = imap_fetch_overview($inbox, $email_number, 0);
        $structure = imap_fetchstructure($inbox, $email_number);

        $subject = $overview[0]->subject ?? '';
        $from    = $overview[0]->from ?? '';

        $body = imap_fetchbody($inbox, $email_number, 1);

        // 🔥 ATTACHMENTS HANDLING STARTS HERE
        if (isset($structure->parts)) {

            foreach ($structure->parts as $i => $part) {

                $isAttachment = false;
                $filename = '';

                if (isset($part->disposition) && strtolower($part->disposition) == 'attachment') {
                    $isAttachment = true;
                }

                if ($part->ifdparameters) {
                    foreach ($part->dparameters as $object) {
                        if (strtolower($object->attribute) == 'filename') {
                            $filename = $object->value;
                            $isAttachment = true;
                        }
                    }
                }

                if ($part->ifparameters) {
                    foreach ($part->parameters as $object) {
                        if (strtolower($object->attribute) == 'name') {
                            $filename = $object->value;
                            $isAttachment = true;
                        }
                    }
                }

                if ($isAttachment && $filename) {

                    $data = imap_fetchbody($inbox, $email_number, $i + 1);

                    if ($part->encoding == 3) {
                        $data = base64_decode($data);
                    } elseif ($part->encoding == 4) {
                        $data = quoted_printable_decode($data);
                    }

                    $newName = uniqid() . '_' . $filename;
                    $filePath = __DIR__ . '/uploads/' . $newName;

                    file_put_contents($filePath, $data);

                    // 👉 Save to DB
                    $stmt = $pdo->prepare("
                        INSERT INTO ticket_attachments 
                        (ticket_id, file_name, original_name, file_size, mime_type)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        1, // ⚠️ TEMP: replace with actual ticket_id
                        $newName,
                        $filename,
                        filesize($filePath),
                        mime_content_type($filePath)
                    ]);
                }
            }
        }
    }
}

imap_close($inbox);

foreach ($emails as $email_number) {

    $overview = imap_fetch_overview($inbox, $email_number, 0);
    $structure = imap_fetchstructure($inbox, $email_number);

    $subject = $overview[0]->subject ?? '';
    $from    = $overview[0]->from ?? '';

    // 👉 Extract ticket reference (like T-250231)
    preg_match('/T-\d+/', $subject . ' ' . $from, $matches);
    $ticketRef = $matches[0] ?? null;

    if (!$ticketRef) continue;

    // 👉 Get ticket ID
    $stmt = $pdo->prepare("SELECT ticket_id FROM tickets WHERE reference = ?");
    $stmt->execute([$ticketRef]);
    $ticket = $stmt->fetch();

    if (!$ticket) continue;

    $ticketId = $ticket['ticket_id'];

    // 👉 Get email body
    $body = imap_fetchbody($inbox, $email_number, 1);

    // 👉 SAVE COMMENT FIRST
    $stmt = $pdo->prepare("
        INSERT INTO ticket_comments (ticket_id, comment, commenter_email)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$ticketId, $body, $from]);

    $commentId = $pdo->lastInsertId();

    // 🔥 NOW PUT YOUR ATTACHMENT CODE HERE
    if (isset($structure->parts)) {

        foreach ($structure->parts as $i => $part) {

            $isAttachment = false;
            $filename = '';

            if (isset($part->disposition) && strtolower($part->disposition) == 'attachment') {
                $isAttachment = true;
            }

            if ($part->ifdparameters) {
                foreach ($part->dparameters as $obj) {
                    if (strtolower($obj->attribute) == 'filename') {
                        $filename = $obj->value;
                        $isAttachment = true;
                    }
                }
            }

            if ($part->ifparameters) {
                foreach ($part->parameters as $obj) {
                    if (strtolower($obj->attribute) == 'name') {
                        $filename = $obj->value;
                        $isAttachment = true;
                    }
                }
            }

            if ($isAttachment && $filename) {

                $data = imap_fetchbody($inbox, $email_number, $i + 1);

                if ($part->encoding == 3) {
                    $data = base64_decode($data);
                } elseif ($part->encoding == 4) {
                    $data = quoted_printable_decode($data);
                }

                $newName = uniqid() . '_' . $filename;
                $path = __DIR__ . '/uploads/' . $newName;

                file_put_contents($path, $data);

                // ✅ SAVE WITH comment_id (IMPORTANT)
                $stmt = $pdo->prepare("
                    INSERT INTO ticket_attachments 
                    (ticket_id, file_name, original_name, file_size, mime_type, comment_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $ticketId,
                    $newName,
                    $filename,
                    filesize($path),
                    mime_content_type($path),
                    $commentId
                ]);
            }
        }
    }
}