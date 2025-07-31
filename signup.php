<?php
session_start();

$plan = $_GET['plan'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm_password) {
        die("All fields are required.");
    }
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    $conn = new mysqli("localhost", "root", "", "dobu_db", 3307);
    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        die("Email is already registered. <a href='login.html'>Login here</a>.");
    }
    $stmtCheck->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmtInsert = $conn->prepare("INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
    $stmtInsert->bind_param("sss", $name, $email, $password_hash);
    if ($stmtInsert->execute()) {
        $_SESSION['user_id'] = $stmtInsert->insert_id;
        $_SESSION['user_name'] = $name;

        $redirectUrl = "membership_confirmation.php";
        if ($plan) {
            $redirectUrl .= "?plan=" . urlencode($plan);
        } else {
            $redirectUrl = "index.html"; // or homepage
        }
        header("Location: $redirectUrl");
        exit;
    } else {
        die("Signup failed: " . $stmtInsert->error);
    }
    $stmtInsert->close();
    $conn->close();
} else {
    header("Location: login.html");
    exit;
}
