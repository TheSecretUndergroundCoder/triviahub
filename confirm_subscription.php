<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['plan_id'])) {
    header("Location: change_plan.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$plan_id = $_POST['plan_id'];

// Get the selected plan details
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id = ?");
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if ($plan) {
    // Set new subscription start and end date
    $start_date = date("Y-m-d H:i:s");
    $end_date = date("Y-m-d H:i:s", strtotime("+1 month"));

    // Insert new subscription
    $stmt = $conn->prepare("INSERT INTO user_subscriptions (user_id, subscription_id, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $plan_id, $start_date, $end_date);
    
    if ($stmt->execute()) {
        // Redirect to home page after successful payment
        header("Location: index.php?message=Subscription updated successfully");
        exit;
    } else {
        echo "Error updating subscription.";
    }
} else {
    echo "Invalid plan.";
}
?>
