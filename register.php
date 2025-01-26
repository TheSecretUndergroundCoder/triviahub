<?php
session_start();
include 'db.php';

function generateRecoveryKey() {
    return openssl_random_pseudo_bytes(32); // Generate a random 32-byte recovery key
}

function encryptRecoveryKey($key) {
    $encryption_key = "your-encryption-key"; // Use a secure key, keep it secret
    return openssl_encrypt($key, 'aes-256-cbc', $encryption_key, 0, substr($encryption_key, 0, 16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // Generate recovery key
        $recovery_key = generateRecoveryKey();
        $encrypted_recovery_key = encryptRecoveryKey($recovery_key);

        // Insert user into the database with encrypted recovery key
        $stmt = $pdo->prepare("INSERT INTO users (username, password, recovery_key) VALUES (:username, :password, :recovery_key)");
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'recovery_key' => $encrypted_recovery_key,
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Registration successful! Click Continue to set up your account.";
            $_SESSION['recovery_key'] = bin2hex($recovery_key); // Save recovery key for next page
            $_SESSION['user_id'] = $pdo->lastInsertId(); // Save user ID for setup
            $pdo->commit();
            header("Location: account_setup.php");
            exit;
        } else {
            $_SESSION['error_message'] = "An error occurred during registration. Please try again.";
            $pdo->rollBack();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            $_SESSION['error_message'] = "Username already exists. Please choose a different one.";
        } else {
            $_SESSION['error_message'] = "An unexpected error occurred. Please try again later.";
        }
    }
    header("Location: register.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles/loginandreg.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
    <style>
        .notification {
            padding: 0;
            max-height: 0;
            border: 1px solid transparent;
            margin-bottom: 20px;
            border-radius: 15px;
            overflow: hidden;
            transition: max-height 0.5s ease, padding 0.5s ease, border-color 0.5s ease;
        }
        .notification.show {
            padding: 10px;
            max-height: 200px;
            border-color: grey;
        }
        .notification.success {
            background-color: #dff0d8;
            border-color: #d6e9c6;
            color: #3c763d;
        }
        .notification.error {
            background-color: #f2dede;
            border-color: #ebccd1;
            color: #a94442;
        }
        .hidden {
            display: none;
        }
        .continue-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .continue-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<!-- Notification Section -->
<div class="notification 
    <?php 
        echo isset($_SESSION['error_message']) ? 'show error' : ''; 
        echo isset($_SESSION['success_message']) ? 'show success' : ''; 
    ?>">
    <?php if (isset($_SESSION['error_message'])) : ?>
        <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
        <?php unset($_SESSION['error_message']); ?>
    <?php elseif (isset($_SESSION['success_message'])) : ?>
        <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
    <?php endif; ?>
</div>

<h2>Register</h2>
<div class="form-content <?php echo isset($_SESSION['success_message']) ? 'hidden' : ''; ?>">
    <form method="post">
        <input type="text" name="username" placeholder="Username" class="input_login" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
</div>

<?php if (isset($_SESSION['success_message'])) : ?>
    <a href="setup_account.php?recovery_key=<?php echo $_SESSION['recovery_key']; ?>" class="continue-button">Continue</a>
    <?php unset($_SESSION['success_message'], $_SESSION['recovery_key']); // Only unset AFTER showing ?>
<?php endif; ?>

<p class="a_link">Have an account? <a href="login.php" class="underline-animation">Login now.</a></p>

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
    <p>Hey all TriviaHub Members. As our service is still under development it might be a bit buggy.</p>
    <p>I am trying my best to remove all bugs as fast as possible to keep you and your accounts safe.</p>
    <p>If you have any suggestions please <span class="vip">Contact us </span> from the <strong>Contact</strong> page.</p>
    <p>For more information or to see how we're protecting you, visit our news feed. (May not be uploaded yet).</p>
</div>

<script>
    if (window.location.href.endsWith("register.php")) {
        var newUrl = window.location.href.replace("register.php", "register");
        window.history.replaceState({}, '', newUrl);
    }
</script>

</body>
</html>
