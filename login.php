<?php
session_start();

$plan = $_GET['plan'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        die("Email and password are required.");
    }

    $conn = new mysqli("localhost", "root", "", "dobu_db", 3307);
    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $name, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;

            $redirectUrl = "membership_confirmation.php";
            if ($plan) {
                $redirectUrl .= "?plan=" . urlencode($plan);
            } else {
                $redirectUrl = "index.html"; // or homepage after login
            }
            header("Location: $redirectUrl");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
    $conn->close();

    if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
        echo "<p><a href='login.html'>Go back</a></p>";
    }
} else {
    header("Location: login.html");
    exit;
}
