<?php
session_start();
include 'db.php'; // Ensure this includes the $pdo connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Allowed user types
$allowed_user_types = ['student', 'teacher', 'school-admin', 'owner', 'admin'];

try {
    // Fetch the user's type from the database
    $stmt = $pdo->prepare("SELECT type FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Check if the user exists and their type is allowed
    if (!$user || !in_array($user['type'], $allowed_user_types)) {
        header("Location: unauthorized.php"); // Redirect to unauthorized access page
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education</title>
    <link rel="stylesheet" href="styles/education.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <?php include 'taskbar.php'; ?> <!-- Include Taskbar Script -->
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
<div class="container">
    <h1>Welcome to Your Dashboard</h1>

    <!-- Navigation Links -->
    <div class="nav-links">
        <a href="manage_homework.php" class="button">Manage Tasks</a>
        <a href="join_class.php" class="button">Join a Class</a>
    </div>

<!-- Teacher's Classes -->
<?php if (in_array($user['type'], ['teacher', 'owner', 'admin'])): ?>
    <?php
    try {
        $stmt = $pdo->prepare("
            SELECT c.id, c.name 
            FROM classes c
            JOIN class_members cm ON cm.class_id = c.id
            WHERE cm.user_id = :user_id AND cm.role = 'teacher'
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
    ?>
    <h2>Your Classes</h2>
    <?php if (!empty($classes)): ?>
        <ul>
            <?php foreach ($classes as $class): ?>
                
                    <strong><?= htmlspecialchars($class['name']) ?></strong>
                    - <a href="manage_homework.php?class_id=<?= $class['id'] ?>" class="button">Manage Homework</a>
                
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You don't manage any classes.</p>
    <?php endif; ?>
<?php endif; ?>


    <!-- Student's Homework -->
    <?php if ($user['type'] === 'student'): ?>
        <?php
        try {
            $stmt = $pdo->prepare("
                SELECT h.id, q.title, h.due_date 
                FROM homework h
                JOIN class_members cm ON h.class_id = cm.class_id
                JOIN quizzes q ON h.quiz_id = q.id
                WHERE cm.user_id = :user_id AND cm.role = 'student'
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $homework = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
        ?>
        <h2>Your Homework</h2>
        <?php if (!empty($homework)): ?>
            <ul>
                <?php foreach ($homework as $task): ?>
                    <li>
                        <strong><?= htmlspecialchars($task['title']) ?></strong>
                        - Due: <?= htmlspecialchars($task['due_date']) ?>
                        - <a href="take_quiz.php?quiz_id=<?= $task['id'] ?>">Take Quiz</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have no homework assigned.</p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Admin Panel -->
    <?php if (in_array($user['type'], ['school-admin', 'admin', 'owner', 'teacher'])): ?>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin_manage.php">Manage Schools</a></li>
            <li><a href="manage_classes.php">Manage Classes</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
        </ul>
    <?php endif; ?>
</div>

<script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("education.php")) {
        var newUrl = window.location.href.replace("education.php", "education");
        window.history.replaceState({}, '', newUrl);
    }
</script>
</body>
</html>
