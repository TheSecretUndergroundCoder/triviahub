<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the user's ban information from the database
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT banned, ban_reason FROM users WHERE id = :userId");
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If the user doesn't exist or is not banned, redirect to homepage
if (!$user || !$user['banned']) {
    header("Location: index.php");
    exit();
}

// Get the ban reason (with fallback if it's null)
$banReason = !empty($user['ban_reason']) ? $user['ban_reason'] : "No specific reason provided.";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banned</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #d9534f; /* Red color to indicate alert */
        }
        p {
            font-size: 18px;
        }
        .btn {
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

    <h1>Account Banned</h1>
    <p>You have been banned from accessing this site.</p>
    <p><strong>Reason:</strong> <?php echo htmlspecialchars($banReason); ?></p>
    <p>If you believe this is an error, please contact support.</p>
    <button class="btn" onclick="window.location.href='index.php'">Go to Home</button>
    <script>
        // Remove '.php' from the URL without reloading the page
        if (window.location.href.endsWith("banned.php")) {
            var newUrl = window.location.href.replace("banned.php", "account-restricted");
            window.history.replaceState({}, '', newUrl);
        }
    </script>
</body>
</html>
