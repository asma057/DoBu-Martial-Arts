<?php
session_start();

if (!isset($_POST['plan_name'])) {
    die("No membership plan selected.");
}

$plan_name = $_POST['plan_name'];

if (!isset($_SESSION['user_id'])) {
    // User not logged in - redirect to login.html with plan as GET
    header("Location: login.html?plan=" . urlencode($plan_name));
    exit;
}

// User logged in - save membership directly
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "dobu_db", 3307);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Check if user has membership already
$stmtCheck = $conn->prepare("SELECT id FROM user_membership WHERE user_id = ?");
$stmtCheck->bind_param("i", $user_id);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    // Update membership
    $stmtUpdate = $conn->prepare("UPDATE user_membership SET plan_name = ?, selected_at = NOW() WHERE user_id = ?");
    $stmtUpdate->bind_param("si", $plan_name, $user_id);
    if (!$stmtUpdate->execute()) {
        die("Error updating membership: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();
} else {
    // Insert new membership
    $stmtInsert = $conn->prepare("INSERT INTO user_membership (user_id, plan_name, selected_at) VALUES (?, ?, NOW())");
    $stmtInsert->bind_param("is", $user_id, $plan_name);
    if (!$stmtInsert->execute()) {
        die("Error saving membership: " . $stmtInsert->error);
    }
    $stmtInsert->close();
}

$stmtCheck->close();
$conn->close();

// Redirect to confirmation page
header("Location: membership_confirmation.php?plan=" . urlencode($plan_name));
exit;
