<?php


$user_id = $_SESSION['user_id']; 


if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access');
}

if (isset($_POST['submitTicket'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $category_id = $_POST['category_id'];
    $contact = $_POST['contact'];
    $support_email = $_POST['support_email'];

    $stmt = $pdo->prepare(
        "INSERT INTO tickets 
        (title, description, email, status, priority, category_id, contact, support_email)
        VALUES (:title, :description, :email, :status, :priority, :category_id, :contact, :support_email)"
    );
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':priority', $priority);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':support_email', $support_email);

    if ($stmt->execute()) {
        echo "<div style='color:green;'>Ticket submitted successfully!</div>";
    } else {
        echo "<div style='color:red;'>Failed to submit ticket.</div>";
    }
}