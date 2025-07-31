<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topic_id'])) {
    $topic_id = intval($_POST['topic_id']);
    $user_id = $_SESSION['user_id'];

    $conn = new mysqli("localhost", "root", "", "dobu_db", 3307);
    if ($conn->connect_error) {
        die("DB connection failed: " . $conn->connect_error);
    }

    // Only allow deletion if the topic belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM forum_topics WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $topic_id, $user_id);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    header("Location: forum.php");
    exit;
} else {
    echo "Invalid request.";
}
?>
