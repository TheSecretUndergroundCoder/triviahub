<?php
// Start the session to access any session variables
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="styles/education.css"> <!-- Your CSS file for styling -->
</head>
<body>
    <div class="container">
        <h1>Unauthorized Access</h1>
        <p>You do not have permission to view this page. Please contact your administrator if you believe this is an error.</p>
        <a href="login.php" class="button">Go to Login</a> <!-- Button to go back to login page -->
    </div>
</body>
</html>
