<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    try {
        $stmt = $pdo->prepare("INSERT INTO contacts (user_id, subject, message, status) VALUES (:user_id, :subject, :message, 'Not Reviewed')");
        $stmt->execute([':user_id' => $user_id, ':subject' => $subject, ':message' => $message]);

        $message = "Your contact message has been submitted successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="styles/contact_user.css">
</head>
<body>
    <div class="contact-container">
        <h1>Contact Us</h1>
        <?php if (isset($message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit" class="submit-button">Submit</button>
        </form>
        <a href="index.php" class="back-link">Back to Home</a>
    </div>
</body>
        
        <script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("contact.php")) {
        var newUrl = window.location.href.replace("contact.php", "contact");
        window.history.replaceState({}, '', newUrl);
    }
</script>
</html>
