<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$plan_name = $_GET['plan'] ?? null;
if (!$plan_name) {
    die("No membership plan specified.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $user_id = $_SESSION['user_id'];

        $conn = new mysqli("localhost", "root", "", "dobu_db", 3307);
        if ($conn->connect_error) {
            die("DB Connection failed: " . $conn->connect_error);
        }

        $stmtCheck = $conn->prepare("SELECT id FROM user_membership WHERE user_id = ?");
        $stmtCheck->bind_param("i", $user_id);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $stmtUpdate = $conn->prepare("UPDATE user_membership SET plan_name = ?, selected_at = NOW() WHERE user_id = ?");
            $stmtUpdate->bind_param("si", $plan_name, $user_id);
            $success = $stmtUpdate->execute();
            $stmtUpdate->close();
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO user_membership (user_id, plan_name, selected_at) VALUES (?, ?, NOW())");
            $stmtInsert->bind_param("is", $user_id, $plan_name);
            $success = $stmtInsert->execute();
            $stmtInsert->close();
        }

        $stmtCheck->close();
        $conn->close();

        if ($success) {
            ?>
            <!DOCTYPE html>
<html>
<head>
    <title>Membership Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            text-align: center;
            padding-top: 100px;
        }
        .container {
            background: white;
            padding: 40px;
            margin: auto;
            width: 50%;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 12px 24px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .homepage {
            background-color: #ff6600;
            color: white;
        }
        .logout {
            background-color: #333;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thank you for choosing your membership!</h2>
        <p>Your membership has been successfully recorded.</p>
        <a href="index.html"><button class="btn homepage">Go to Homepage</button></a>
        <a href="logout.php"><button class="btn logout">Logout</button></a>
    </div>
</body>
</html>
            <?php
            exit;
        } else {
            die("Failed to save membership.");
        }
    } else {
        header("Location: index.html");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Membership</title>
    <style>
        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 60px 20px;
        }

        h1 {
            font-size: 2.5rem;
            color: #ff6600;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 40px;
        }

        form button {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 12px 25px;
            margin: 10px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form button:hover {
            background-color: #e55d00;
        }
    </style>
</head>
<body>
    <h1>Confirm Your Membership Plan</h1>
    <p>Hello <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>, are you sure you want to choose the <strong style="color:#ff6600;"><?= htmlspecialchars($plan_name) ?></strong> plan?</p>
    <form method="POST" action="">
        <button type="submit" name="confirm" value="yes">Yes, Confirm</button>
        <button type="submit" name="confirm" value="no">No, Cancel</button>
    </form>
</body>
</html>
