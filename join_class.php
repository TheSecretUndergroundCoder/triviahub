<?php
session_start(); // Start the session to use session variables for messages

// Ensure PDO connection is established
require_once 'pdo_connection.php'; // Adjust the path to your actual PDO connection

// Join Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_class'])) {
    // Validate that class_id is set and is a number
    if (isset($_POST['class_id']) && is_numeric($_POST['class_id'])) {
        $class_id = $_POST['class_id'];
        $user_id = $_SESSION['user_id'] ?? null; // Ensure the user is logged in

        if (!$user_id) {
            // If user is not logged in, redirect to login page
            $_SESSION['error_message'] = "You must be logged in to join a class.";
            header("Location: login.php");
            exit();
        }

        try {
            // Check if the user is already a member of the class
            $checkStmt = $pdo->prepare("SELECT * FROM class_members WHERE class_id = :class_id AND user_id = :user_id");
            $checkStmt->execute(['class_id' => $class_id, 'user_id' => $user_id]);

            if ($checkStmt->rowCount() > 0) {
                // User is already a member
                $_SESSION['error_message'] = "You are already a member of this class.";
                header("Location: education.php");
                exit();
            }

            // Insert into class_members table
            $stmt = $pdo->prepare("INSERT INTO class_members (class_id, user_id, role) VALUES (:class_id, :user_id, 'student')");
            $stmt->execute(['class_id' => $class_id, 'user_id' => $user_id]);

            // Success message and redirect to education page
            $_SESSION['success_message'] = "Joined class successfully!";
            header("Location: education.php");
            exit();

        } catch (PDOException $e) {
            // Handle errors gracefully
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header("Location: education.php");
            exit();
        }
    } else {
        // Invalid or missing class_id
        $_SESSION['error_message'] = "Please provide a valid class ID.";
        header("Location: education.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join a Class</title>
    <link rel="stylesheet" href="styles/education.css"> <!-- Link to your CSS file -->
</head>
<body>
	<?php include 'taskbar.php'; ?>

    <div class="container">
        <!-- Display success or error message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); // Clear the message ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error">
                <?php echo $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); // Clear the message ?>
        <?php endif; ?>

        <h2>Join a Class</h2>
        
        <!-- Form to join a class -->
        <form method="POST" action="join_class.php" class="form-group">
            <label for="class_id">Enter Class ID:</label>
            <input type="number" name="class_id" required class="form-group">
            
            <button type="submit" name="join_class" class="button">Join Class</button>
        </form>
    </div>

</body>
</html>
