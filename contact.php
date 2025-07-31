<?php
// contact.php

// Database connection (adjust DB credentials)
$conn = new mysqli("localhost", "root", "", "dobu_db", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input
$name = htmlspecialchars(trim($_POST['name']));
$email = htmlspecialchars(trim($_POST['email']));
$subject = htmlspecialchars(trim($_POST['subject']));
$message = htmlspecialchars(trim($_POST['message']));
$created_at = date('Y-m-d H:i:s');

// Save to contact_messages table
$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $subject, $message, $created_at);

if ($stmt->execute()) {
    echo "<script>alert('Thank you! Your message has been sent.'); window.location.href = 'contact.html';</script>";
} else {
    echo "<script>alert('Oops! Something went wrong.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
