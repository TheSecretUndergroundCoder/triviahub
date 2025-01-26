<?php
session_start();
include 'db.php'; // Ensure database connection is included

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the user's school_id from the database
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT school_id FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['school_id']) {
        $_SESSION['error_message'] = "You are not associated with any school.";
        header("Location: login.php");
        exit;
    }

    $user_school_id = $user['school_id'];
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Step 1: Handle School ID Validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_school_id'])) {
    $input_school_id = $_POST['school_id'];

    if ($input_school_id == $user_school_id) {
        $_SESSION['school_id_valid'] = true;
        $_SESSION['school_id'] = $input_school_id;
    } else {
        $_SESSION['error_message'] = "Invalid school ID. You do not have permissions for this school.";
        $_SESSION['school_id_valid'] = false;
    }
}

// Step 2: Handle Class Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    if (!isset($_SESSION['school_id_valid']) || !$_SESSION['school_id_valid']) {
        $_SESSION['error_message'] = "You must validate your school ID first.";
        header("Location: manage_classes.php");
        exit;
    }

    $name = $_POST['class_name'];
    $description = $_POST['class_description'];
    $school_id = $_SESSION['school_id'];

    try {
        // Insert the new class
        $stmt = $pdo->prepare("INSERT INTO classes (name, description, school_id) VALUES (:name, :description, :school_id)");
        $stmt->execute(['name' => $name, 'description' => $description, 'school_id' => $school_id]);

        $class_id = $pdo->lastInsertId();

        // Add teacher to the class
        $stmt = $pdo->prepare("INSERT INTO class_members (class_id, user_id, role) VALUES (:class_id, :user_id, 'teacher')");
        $stmt->execute(['class_id' => $class_id, 'user_id' => $user_id]);

        $_SESSION['success_message'] = "Class created successfully!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error creating class: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a Class</title>
    <link rel="stylesheet" href="styles/education.css">
</head>
<body>

    <?php include 'taskbar.php'; ?> <!-- Include taskbar -->

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error">
            <?php echo $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="container">
        <?php if (!isset($_SESSION['school_id_valid']) || !$_SESSION['school_id_valid']): ?>
            <!-- School ID Input Form -->
            <h2>Enter School ID</h2>
            <form method="POST">
                <label for="school_id">School ID:</label>
                <input type="text" name="school_id" required>
                <button type="submit" name="validate_school_id">Submit</button>
            </form>
        <?php else: ?>
            <!-- Class Creation Form -->
            <h2>Create a Class</h2>
            <form method="POST" class="form-group">
                <label for="class_name">Class Name:</label>
                <input type="text" name="class_name" required class="form-group">
                
                <label for="class_description">Description:</label>
                <textarea name="class_description" required class="form-group"></textarea>
                
                <button type="submit" name="create_class" class="button">Create Class</button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
