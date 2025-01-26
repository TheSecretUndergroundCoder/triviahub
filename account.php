<?php
session_start();
include 'db.php'; // Ensure this includes the $pdo connection

if (isset($_GET['message'])) {
    echo "<p>" . htmlspecialchars($_GET['message']) . "</p>";
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];

try {
    // Fetch user's name, email, password, and user type from the database
    $stmt = $pdo->prepare("SELECT username, email, password, type FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    // If user is found, store user info in session
    if ($user) {
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_type'] = $user['type'];

        // Mask the password (No need to decrypt as it's hashed)
        $maskedPassword = str_repeat('*', strlen($user['password']));

        // Decrypt the email
        $decryptedEmail = decryptEmail($user['email']);
        $_SESSION['user_email'] = $decryptedEmail;
    }

    // Fetch user subscription details
    $stmt = $pdo->prepare("
        SELECT s.plan_name, s.max_quizzes, us.start_date, us.end_date 
        FROM user_subscriptions us 
        JOIN subscriptions s ON us.subscription_id = s.id 
        WHERE us.user_id = :id AND NOW() BETWEEN us.start_date AND us.end_date
    ");
    $stmt->execute(['id' => $user_id]);
    $subscription = $stmt->fetch();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Function to decrypt the email
function decryptEmail($encrypted_email) {
    $encryption_key = "your-encryption-key"; // Use your encryption key
    list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_email), 2); // Extract encrypted data and IV
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv); // Decrypt the email
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=block" />
</head>
<body>
<?php include 'taskbar.php'; ?> <!-- Include Taskbar Script -->
<h2>Welcome <?php echo htmlspecialchars($_SESSION['user_name']); ?>, to your account</h2>
        
<div class="view-info glass">
    <h2>Here's your account details:</h2>
    <p>Username: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
    <p>Password: <?php echo htmlspecialchars($maskedPassword); ?></p>
    <a href="change-password.php" class="underline-animation">Change Password</a>
</div><br>

<div class="change-info glass">
    <h3>Update Your Username</h3>
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <form action="update-username.php" method="POST">
        <label for="username">New Username: <span class="required_info">*</span></label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
        <button type="submit">Update Username</button>
    </form>
</div>

<div class="extra-info glass">
    <?php if ($subscription): ?>
        <p>
            <span>Active Subscription:</span> <?php echo htmlspecialchars($subscription['plan_name']); ?><br>
            <span>Max Quizzes:</span> <?php echo htmlspecialchars($subscription['max_quizzes']); ?><br>
            <span>Subscription Validity:</span> 
            <?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['start_date']))); ?> to 
			<?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['end_date']))); ?>
        </p>
        <a href="change-plan.php" class="underline-animation">Change Subscription</a>
    <?php else: ?>
        <p>No active subscription. <a href="purchase.php" class="underline-animation">Purchase a subscription</a></p>
    <?php endif; ?>
</div>

<script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("account.php")) {
        var newUrl = window.location.href.replace("account.php", "account");
        window.history.replaceState({}, '', newUrl);
    }
</script>
</body>
</html>
