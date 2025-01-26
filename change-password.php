<?php
session_start();
include 'db.php'; // Ensure this includes the $pdo connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize error and success messages
$error = $success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId = $_SESSION['user_id'];

    try {
        // Fetch the current password hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $error = "The current password is incorrect.";
        } elseif (strlen($newPassword) < 8) {
            $error = "The new password must be at least 8 characters long.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "The new password and confirmation do not match.";
        } else {
            // Hash the new password and update it in the database
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute(['password' => $hashedPassword, 'id' => $userId]);

            $success = "Password changed successfully.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php include 'taskbar.php'; ?> <!-- Include Taskbar Script -->

<h2>Change Your Password</h2>

<div class="form-container glass">
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif ($success): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" action="change-password.php">
        <label for="current_password">Current Password: <span class="required_info">*</span></label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">New Password: <span class="required_info">*</span></label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirm New Password: <span class="required_info">*</span></label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Change Password</button>
    </form>
</div>
        
            <script>
        // Remove '.php' from the URL without reloading the page
        if (window.location.href.endsWith("change-password.php")) {
            var newUrl = window.location.href.replace("change-password.php", "password-reset");
            window.history.replaceState({}, '', newUrl);
        }
    </script>
</body>
</html>
