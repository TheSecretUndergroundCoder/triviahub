<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        .success-message {
            text-align: center;
            font-size: 24px;
            color: #4CAF50;
        }
        .success-message img {
            width: 80px;
            height: 80px;
        }
        a {
            font-size: 24px;
            color: #83c985;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
            
    </style>
</head>
<body>
    <div class="success-message">
        <img src="images/success.png" alt="Success">
        <h2>Setup Successful!</h2>
        <p>Your account has been successfully set up. You can now <a class="link" href="login.php">login</a>.</p>
    </div>
        
            <script>
        // Remove '.php' from the URL without reloading the page
        if (window.location.href.endsWith("success-setup.php")) {
            var newUrl = window.location.href.replace("success-setup.php", "successful-setup");
            window.history.replaceState({}, '', newUrl);
        }
    </script>
</body>
</html>
