<?php
session_start();

// Check if an error message is passed in the URL
$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : "An unknown error occurred.";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <!-- Load the correct stylesheet based on the user's theme -->
    <link rel="stylesheet"href="styles/style.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <h2>Error</h2>
    <p><?php echo htmlspecialchars($error_message); ?></p>

    <a href="dashboard.php">
        <button>Go to Dashboard</button>
    </a>

    <a href="find_quiz.php">
        <button>Find a Quiz</button>
    </a>
</body>
</html>
