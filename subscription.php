<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user subscription details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT s.plan_name, s.max_quizzes, us.start_date, us.end_date 
    FROM user_subscriptions us 
    JOIN subscriptions s ON us.subscription_id = s.id 
    WHERE us.user_id = ? AND NOW() BETWEEN us.start_date AND us.end_date
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$subscription = $result->fetch_assoc();
$stmt->close(); // Close the statement to free resources

// Fetch available subscription plans
$stmt = $conn->prepare("SELECT * FROM subscriptions");
$stmt->execute();
$result = $stmt->get_result(); // Ensure this result is fetched correctly
$plans = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close(); // Close the statement to free resources

// Handle form submission for changing the subscription plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $new_plan_id = $_POST['plan'];

    // Update the user's subscription
    $stmt = $conn->prepare("UPDATE user_subscriptions SET subscription_id = ? WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $new_plan_id, $user_id);
        $stmt->execute();
        $stmt->close(); // Close the statement to free resources

        // Redirect to the dashboard after changing the plan
        header("Location: index.php");
        exit;
    } else {
        echo "<p>Error preparing statement for updating subscription.</p>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Subscription</title>
    <link rel="stylesheet" type="text/css" href="dark-mode.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <h2>Subscription</h2>

    <?php if ($subscription): ?>
        <p>
            <span>Active Subscription:</span> <?php echo htmlspecialchars($subscription['plan_name']); ?><br>
            <span>Max Quizzes:</span> <?php echo htmlspecialchars($subscription['max_quizzes']); ?><br>
            <span>Subscription Validity:</span> <?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['start_date']))); ?> to <?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['end_date']))); ?>
        </p>

        <form method="post" action="subscription.php">
            <label for="plan">Choose a Plan:</label>
            <select name="plan" id="plan">
                <?php foreach ($plans as $plan): ?>
                    <option value="<?php echo htmlspecialchars($plan['id']); ?>"><?php echo htmlspecialchars($plan['plan_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <button type="submit">Change Plan</button>
        </form>
    <?php else: ?>
        <p>No active subscription. <a href="purchase.php">Purchase a subscription</a></p>
    <?php endif; ?>

</body>
</html>

