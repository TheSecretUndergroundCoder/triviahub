<?php
ob_start();
session_start();
include 'db.php'; // Include the database connection
include 'taskbar.php'; // Include the taskbar navigation

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
        $user_id = $_SESSION['user_id'];
        $subscription_id = $_POST['subscription_id']; // Get the selected subscription ID from the form

        // Check if the user already has an active subscription
        $stmt = $pdo->prepare("
            SELECT id FROM user_subscriptions 
            WHERE user_id = :user_id AND end_date > NOW()
        ");
        $stmt->execute(['user_id' => $user_id]);
        $active_subscription = $stmt->fetch();

        if (!$active_subscription) {
            // Add the selected subscription to the user
            $start_date = date("Y-m-d H:i:s");
            $end_date = date("Y-m-d H:i:s", strtotime("+1 month"));

            $stmt = $pdo->prepare("
                INSERT INTO user_subscriptions (user_id, subscription_id, start_date, end_date) 
                VALUES (:user_id, :subscription_id, :start_date, :end_date)
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'subscription_id' => $subscription_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            $message = "Subscription added successfully!";
        } else {
            $message = "You already have an active subscription.";
        }
    }

    // Fetch available subscriptions from the database
    $stmt = $pdo->query("SELECT id, plan_name, max_quizzes, price FROM subscriptions");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe</title>
    <link rel="stylesheet" href="styles/subscription.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <div class="container">
        <h2>Pick Your Subscription Plan</h2>
        <p>Select the plan that best suits your needs.</p>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="plans">
            <?php foreach ($subscriptions as $subscription): ?>
                <div class="plan">
                    <h3><?php echo htmlspecialchars($subscription['plan_name']); ?></h3>
                    <p class="price">
                        <?php echo $subscription['price'] == 0.00 ? 'FREE' : '$' . htmlspecialchars($subscription['price']); ?> / month
                    </p>
                    <p>Max Quizzes: <?php echo htmlspecialchars($subscription['max_quizzes']); ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="subscription_id" value="<?php echo htmlspecialchars($subscription['id']); ?>">
                        <button type="submit" name="subscribe">Select Plan</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
