<?php
session_start();
include 'db.php'; // Ensure this includes the $pdo connection

// Initialize variables
$quizzes = [];
$quiz_found = false;
$error_message = "";

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve inputs safely
    $quiz_id = isset($_POST['quiz_id']) ? trim($_POST['quiz_id']) : null;
    $quiz_name = isset($_POST['quiz_name']) ? trim($_POST['quiz_name']) : null;

    // Sanitize input
    $quiz_name = preg_replace('/[^a-zA-Z0-9\s]/', '', $quiz_name);

    // Validate input
    if (empty($quiz_id) && empty($quiz_name)) {
        $quiz_found = false;
        $error_message = "Please enter either a Quiz ID or Name to search.";
    } else {
        try {
            // If quiz_id is provided, search by ID
            if ($quiz_id) {
                $stmt = $pdo->prepare("SELECT id, title FROM quizzes WHERE id = :quiz_id");
                $stmt->execute(['quiz_id' => $quiz_id]);
                $quizzes = $stmt->fetchAll();
            } 
            // If quiz_name is provided, search by name
            elseif ($quiz_name) {
                $stmt = $pdo->prepare("SELECT id, title FROM quizzes WHERE title LIKE :quiz_name");
                $stmt->execute(['quiz_name' => '%' . $quiz_name . '%']);
                $quizzes = $stmt->fetchAll();
            }

            // Check results
            if (count($quizzes) > 0) {
                $quiz_found = true;
            } else {
                $quiz_found = false;
                $error_message = "No quizzes found with the given criteria.";
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Quiz</title>
	<link rel="stylesheet" href="/styles/quiz.css">
	<link rel="icon" href="/icon-removebg-preview.png" type="image/x-icon">

</head>
<body>
	<?php include 'taskbar.php'; ?> <!-- Include Taskbar Script -->
    <h2>Find Quiz by ID or Name</h2>
    
    <form method="post" action="find">
        <label for="quiz_id">Enter Quiz ID (Optional):</label>
        <input type="text" name="quiz_id" id="quiz_id">
        <br><br>

        <label for="quiz_name">Or Enter Quiz Name (Optional):</label>
        <input type="text" name="quiz_name" id="quiz_name">
        <br><br>

        <button type="submit">Find Quiz</button>
    </form>

    <?php if (isset($quiz_found) && $quiz_found): ?>
        <h3>Quizzes Found:</h3>
        <ul>
            <?php foreach ($quizzes as $quiz): ?>
                <li>
                    <strong>Title:</strong> <?php echo htmlspecialchars($quiz['title']); ?>
                    <br>
                    <strong>Quiz ID:</strong> <?php echo htmlspecialchars($quiz['id']); ?>
                    <br>
                    <a href="quiz?quiz_id=<?php echo htmlspecialchars($quiz['id']); ?>">
                        <button>Take Quiz</button>
                    </a>
                    <br><br>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (isset($quiz_found) && !$quiz_found): ?>
        <div class="alert alert-danger alert-dismissible">
            <strong>Warning!</strong> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
<script>
if (window.location.pathname === "/find_quiz.php") {
    window.location.replace("/find");
}
</script>
</body>
</html>
