<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['plan'])) {
    header("Location: change_plan.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$plan_id = $_POST['plan'];

// Get the plan details for display on the payment page
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id = ?");
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <link rel="stylesheet" type="text/css" href="styles/payment.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <h2>Confirm Payment</h2>
    <p>You have selected the <strong><?php echo htmlspecialchars($plan['plan_name']); ?></strong> plan for <strong>$<?php echo htmlspecialchars($plan['price']); ?></strong>.</p>

    <!-- Simulated payment form -->
    <form method="post" action="confirm_subscription.php">
        <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
        <label for="card_number">Card Number:</label>
        <input type="text" id="card_number" name="card_number" required><br>

        <label for="expiry_date">Expiry Date:</label>
        <input type="text" id="expiry_date" name="expiry_date" required><br>

        <label for="cvv">CVV:</label>
        <input type="text" id="cvv" name="cvv" required><br>

        <button type="submit">
    <i class="material-icons">credit_card</i> Confirm Payment
</button>

    </form>

    <a href="change_plan.php">Cancel and Go Back</a>
</body>
</html>

