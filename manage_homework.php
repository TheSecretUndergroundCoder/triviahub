<?php
session_start();
include 'db.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the user type (teacher, owner, admin, school_admin, or student)
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT type FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user_type = $stmt->fetchColumn();

// Allow teachers, owners, admins, and school admins to assign homework
$can_assign_homework = in_array($user_type, ['teacher', 'owner', 'admin', 'school_admin']);

// Assign Homework
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_homework']) && $can_assign_homework) {
    $class_id = $_POST['class_id'];
    $quiz_id = $_POST['quiz_id'];
    $due_date = $_POST['due_date'];

    try {
        // Insert homework into the homework table without explicitly setting the created_at field
        $stmt = $pdo->prepare("
            INSERT INTO homework (class_id, quiz_id, due_date) 
            VALUES (:class_id, :quiz_id, :due_date)
        ");
        $stmt->execute([
            'class_id' => $class_id,
            'quiz_id' => $quiz_id,
            'due_date' => $due_date,
        ]);

        // Check if the row was successfully inserted
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Homework assigned successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to assign homework. Please try again.";
        }

        header("Location: manage_homework.php");
        exit;
    } catch (PDOException $e) {
        // Capture SQL errors
        $_SESSION['error_message'] = "Error assigning homework: " . $e->getMessage();
        header("Location: manage_homework.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Homework</title>
    <link rel="stylesheet" href="styles/education.css">
</head>
<body>

    <?php include 'taskbar.php'; ?> <!-- Include Taskbar -->

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success">
            <?= htmlspecialchars($_SESSION['success_message']); ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error">
            <?= htmlspecialchars($_SESSION['error_message']); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="container">
        <h2>Manage Homework</h2>

        <?php if (in_array($user_type, ['teacher', 'owner', 'admin', 'school_admin'])): ?>
            <!-- Form to assign homework -->
            <form method="POST" action="manage_homework.php" class="form-group">
                <label for="class_id">Class:</label>
                <select name="class_id" required class="form-group">
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT classes.id, classes.name 
                        FROM classes 
                        INNER JOIN class_members ON classes.id = class_members.class_id 
                        WHERE class_members.user_id = :user_id AND class_members.role IN ('teacher', 'owner', 'admin', 'school_admin')
                    ");
                    $stmt->execute(['user_id' => $user_id]);
                    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($classes as $class): ?>
                        <option value="<?= htmlspecialchars($class['id']) ?>"><?= htmlspecialchars($class['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="quiz_id">Quiz:</label>
                <select name="quiz_id" required class="form-group">
                    <?php
                    $stmt = $pdo->query("SELECT id, title FROM quizzes");
                    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($quizzes as $quiz): ?>
                        <option value="<?= htmlspecialchars($quiz['id']) ?>"><?= htmlspecialchars($quiz['title']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="due_date">Due Date:</label>
                <input type="date" name="due_date" required class="form-group">

                <button type="submit" name="assign_homework" class="button">Assign Homework</button>
            </form>
        <?php endif; ?>

        <!-- Tabs for viewing homework -->
        <div class="tabs">
            <button class="tab-button" onclick="showTab('due-homework')">Due Homework</button>
            <button class="tab-button" onclick="showTab('past-homework')">Past Homework</button>
        </div>

        <!-- Due Homework -->
        <div id="due-homework" class="tab-content">
            <h3>Due Homework</h3>
            <ul>
                <?php
                $stmt = $pdo->prepare("
                    SELECT h.id, h.due_date, c.name AS class_name, q.title AS quiz_title 
                    FROM homework h 
                    INNER JOIN classes c ON h.class_id = c.id 
                    INNER JOIN quizzes q ON h.quiz_id = q.id 
                    WHERE h.due_date >= CURDATE() AND c.id IN (
                        SELECT class_id FROM class_members WHERE user_id = :user_id
                    )
                ");
                $stmt->execute(['user_id' => $user_id]);
                $homeworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($homeworks as $hw): ?>
                    <li>
                        Class: <?= htmlspecialchars($hw['class_name']) ?>, 
                        Quiz: <?= htmlspecialchars($hw['quiz_title']) ?>, 
                        Due: <?= htmlspecialchars($hw['due_date']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Past Homework -->
        <div id="past-homework" class="tab-content" style="display:none;">
            <h3>Past Homework</h3>
            <ul>
                <?php
                $stmt = $pdo->prepare("
                    SELECT h.id, h.due_date, c.name AS class_name, q.title AS quiz_title 
                    FROM homework h 
                    INNER JOIN classes c ON h.class_id = c.id 
                    INNER JOIN quizzes q ON h.quiz_id = q.id 
                    WHERE h.due_date < CURDATE() AND c.id IN (
                        SELECT class_id FROM class_members WHERE user_id = :user_id
                    )
                ");
                $stmt->execute(['user_id' => $user_id]);
                $homeworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($homeworks as $hw): ?>
                    <li>
                        Class: <?= htmlspecialchars($hw['class_name']) ?>, 
                        Quiz: <?= htmlspecialchars($hw['quiz_title']) ?>, 
                        Due: <?= htmlspecialchars($hw['due_date']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
            document.getElementById(tabId).style.display = 'block';
        }
    </script>

</body>
</html>
