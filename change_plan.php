<?php
session_start();
include 'db.php'; // Ensure $pdo is properly included and initialized in db.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

try {
    // Check for update submission
    if (isset($_POST['update_plan'])) {
        $new_plan_id = $_POST['plan'];

        // Update user subscription in the database
        $stmt = $pdo->prepare("
            UPDATE user_subscriptions 
            SET subscription_id = :new_plan_id 
            WHERE user_id = :user_id
        ");
        $stmt->execute(['new_plan_id' => $new_plan_id, 'user_id' => $user_id]);

        $message = "Subscription updated successfully!";
    }

    // Fetch user subscription details
    $stmt = $pdo->prepare("
        SELECT s.plan_name, s.max_quizzes, us.start_date, us.end_date 
        FROM user_subscriptions us 
        JOIN subscriptions s ON us.subscription_id = s.id 
        WHERE us.user_id = :user_id AND NOW() BETWEEN us.start_date AND us.end_date
    ");
    $stmt->execute(['user_id' => $user_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch available subscription plans
    $stmt = $pdo->prepare("SELECT * FROM subscriptions");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription</title>
    <link rel="stylesheet" href="styles/change_plan.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
	<?php include 'taskbar.php'; ?> <!-- Include Taskbar Script -->
    <h2>Plan</h2>
    

    <?php if ($subscription): ?>
        <p>
            <span>Active Plan:</span> <?php echo htmlspecialchars($subscription['plan_name']); ?><br>
            <span>Max Quizzes:</span> <?php echo htmlspecialchars($subscription['max_quizzes']); ?><br>
            <span>Plan Validity:</span> 
            <?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['start_date']))); ?> to 
            <?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['end_date']))); ?>
        </p>

        <?php if (!empty($message)): ?>
            <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="plan">Choose a Plan:</label>
            <select name="plan" id="plan">
                <?php foreach ($plans as $plan): ?>
                    <option value="<?php echo $plan['id']; ?>">
                        <?php echo htmlspecialchars($plan['plan_name']); ?> (<?php echo htmlspecialchars($plan['price']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <button type="submit" name="update_plan">Update Plan</button>
        </form>
    <?php else: ?>
        <p>No active subscription. <a href="purchase.php">Purchase a subscription</a></p>
    <?php endif; ?>

    <script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("change_plan.php")) {
        var newUrl = window.location.href.replace("change_plan.php", "change-plan");
        window.history.replaceState({}, '', newUrl);
    }
    </script>
</body>
</html>
