<?php
session_start();

// If the user is already authenticated, redirect to the main site
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Define your PIN (change this to whatever you want)
    $correct_pin = '1234';

    // Check if the submitted PIN matches the correct PIN
    if (isset($_POST['pin']) && $_POST['pin'] === $correct_pin) {
        // Set session variable to indicate successful authentication
        $_SESSION['authenticated'] = true;

        // Redirect to the main site (index.php)
        header("Location: index.php");
        exit();
    } else {
        // Display error message if the PIN is incorrect
        $error_message = "Incorrect PIN. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Under Development</title>
    <style>
        /* Add your custom styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #1e3c72;
            color: white;
            text-align: center;
            padding: 50px;
        }
        input {
            padding: 10px;
            font-size: 1.2em;
            margin-top: 20px;
            border: 1px solid #a1c4fd;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            font-size: 1.2em;
            background-color: #1e3c72;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2a5298;
        }
        .error {
            color: red;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Oops... We're Building Something Great!</h1>
    <p>Enter the PIN to access the website:</p>

    <form action="under-development.php" method="POST">
        <input type="password" name="pin" placeholder="Enter PIN" required>
        <button type="submit">Submit</button>
    </form>

    <?php if (isset($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>
</body>
</html>
