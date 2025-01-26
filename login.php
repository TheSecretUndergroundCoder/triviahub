<?php
session_start();
include 'db.php'; // Ensure $pdo is included and correctly initialized

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password, banned, ban_reason, type FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR); 
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC); 

        // Check if the user is banned
        if ($user['banned'] == 1) {
            $_SESSION['message'] = "You have been banned by the system administrator.";
            $_SESSION['ban_reason'] = $user['ban_reason'] ?? "No specific reason provided.";
            header("Location: banned.php");
            exit();
        }

        // Check if the password is correct
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $user['type']; // If 'type' column exists in the database

            // Regenerate session ID for improved security
            session_regenerate_id(true); 

            // Use $_SESSION['user_id'] instead of $userId
            $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :user_id');
            $stmt->execute(['user_id' => $_SESSION['user_id']]); // Corrected here

            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "User not found.";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaHub - Login</title>
    <link rel="stylesheet" href="styles/loginandreg.css"> 
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
    <style>
     /* Custom Styles for the error message */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

            
        .dissmiss_button {
        	background: none;
            color: white;
            transition: 0.5s;
        }
            
        .dissmiss_button:hover {
        	background: #ffb3b3;
            color: white;
        }

        /* Add a class to trigger the fade-out animation */
        .error-message.hide {
            opacity: 0;
            transform: translateY(-20px);
        }

    </style>
</head>
<body>
    <div class="container"> 
        <h2>Login</h2>

        <!-- Display error messages dynamically -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <span><?php echo $_SESSION['error_message']; ?></span>
                <button onclick="dismissError()" class="dissmiss_button">X</button>
            </div>
            <?php unset($_SESSION['error_message']); ?> <!-- Clear the error message after displaying it -->
        <?php endif; ?>

        <form method="post">
            <input type="text" name="username" placeholder="Username" class="input_login" required><br>
            <input type="password" name="password" placeholder="Password" class="input_login" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
    <p class="a_link">Don't have an account?<a href="register.php" class="footer-links underline-animation">Sign up, today.</a></p>

    <footer class="footer"> 
        <div class="footer-content">
            <p>&copy; 2024 TriviaHub. All rights reserved.</p>
            <ul class="footer-links">
                <a href="privacypolicy.php" class="underline-animation">Privacy Policy</a> |
                <a href="#" class="underline-animation">Terms of Service</a> |
                <a href="#" class="underline-animation">Contact Us</a>
            </ul>
        </div>
    </footer>

    <div class="closure">
        <h1>⚠ Important ⚠</h1>
        <p>Hey all TriviaHub Members. As our service is still under development it still might be a bit buggy.</p>
        <p>I am trying my best to remove all the bugs as fast as possible to keep you and your accounts safe.</p>
        <p>If you have any suggestions please <span class="vip">Contact us </span> from the <strong>Contact</strong> page.</p>
        <p>For any more information or to see how we're protecting you go to our news feed to find out more. (May not be uploaded at the moment).</p>
    </div>

    <script>
        function dismissError() {
            const errorMessage = document.querySelector('.error-message');
            errorMessage.classList.add('hide');

            // Wait for the animation to finish before completely hiding it
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 500); // Matches the duration of the fade-out animation (0.5s)
        }

        // Remove '.php' from the URL without reloading the page
        if (window.location.href.endsWith("login.php")) {
            var newUrl = window.location.href.replace("login.php", "login");
            window.history.replaceState({}, '', newUrl);
        }
    </script>
</body>
</html>
