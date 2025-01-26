<?php
session_start();
include 'db.php'; // Include your database connection
include 'taskbar.php'; // Include the taskbar navigation

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

try {
    // Fetch user's subscription details
    $stmt = $pdo->prepare("
        SELECT s.plan_name, s.max_quizzes, us.start_date, us.end_date, 
        (SELECT COUNT(*) FROM quiz_attempts WHERE user_id = us.user_id) AS quizzes_taken
        FROM user_subscriptions us
        JOIN subscriptions s ON us.subscription_id = s.id
        WHERE us.user_id = :user_id AND us.end_date > NOW()
    ");
    $stmt->execute(['user_id' => $user_id]);
    $subscription = $stmt->fetch();

    // Check if the user has a valid subscription
    if (!$subscription) {
        $message = "You don't have an active subscription. Please subscribe to access quizzes.";
    }
} catch (PDOException $e) {
    // Improved error message for debugging
    $message = "Database error: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Max Quizzes</title>
    <link rel="stylesheet" href="styles/max_quizzes.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <div class="container">
        <h1>Your Quiz Subscription</h1>
        
        <?php if ($message): ?>
            <!-- Show the error message or warning -->
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php else: ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($subscription['plan_name']); ?></h2>
                <p>Subscription Period:</p>
                <p>
                    <strong>Start:</strong> <?php echo date("F d, Y", strtotime($subscription['start_date'])); ?><br>
                    <strong>End:</strong> <?php echo date("F d, Y", strtotime($subscription['end_date'])); ?>
                </p>
                <p class="status">
                    <strong>Quizzes Allowed:</strong> <?php echo htmlspecialchars($subscription['max_quizzes']); ?><br>
                    <strong>Quizzes Taken:</strong> <?php echo htmlspecialchars($subscription['quizzes_taken']); ?><br>
                    <strong>Remaining:</strong> 
                    <?php 
                    $remaining_quizzes = $subscription['max_quizzes'] - $subscription['quizzes_taken'];
                    echo htmlspecialchars(max(0, $remaining_quizzes)); // Ensure no negative values 
                    ?>
                </p>
                
                <!-- Progress bar with dynamic width -->
                <div class="progress-bar">
                    <div class="progress" style="width: 
                        <?php echo min(100, ($subscription['quizzes_taken'] / $subscription['max_quizzes']) * 100); ?>%;">
                    </div>
                </div>

                <!-- Show appropriate message based on the quiz usage -->
                <p>
                    <?php if ($subscription['quizzes_taken'] >= $subscription['max_quizzes']): ?>
                        <span class="warning">You’ve reached your quiz limit for this month. Upgrade your plan for more quizzes.</span>
                    <?php else: ?>
                        <span class="success">You still have quizzes available! Keep going!</span>
                    <?php endif; ?>
                </p>
                
                <!-- Upgrade button -->
                <a href="change-plan.php" class="upgrade-btn">Upgrade Your Plan</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
