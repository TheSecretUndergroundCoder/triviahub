<?php
session_start();
include 'db.php'; // Include the database connection

// Ensure score and quiz_id are passed in the URL
if (isset($_GET['score']) && isset($_GET['quiz_id'])) {
    $score = $_GET['score'];
    $quiz_id = $_GET['quiz_id'];
} else {
    die("Error: Missing score or quiz ID.");
}

// Fetch the total number of questions for the quiz
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_questions FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $total_questions = $result['total_questions'];
    } else {
        die("Error: Could not fetch total number of questions.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Quiz Score</title>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <h2>Your Score</h2>
    <p>You scored: <?php echo htmlspecialchars($score); ?> / <?php echo htmlspecialchars($total_questions); ?></p>
    
    <a href="take_quiz.php?quiz_id=<?php echo htmlspecialchars($quiz_id); ?>">
        <button>Take Quiz Again</button>
    </a>
    
    <a href="index.php">
        <button>Go to Home</button>
    </a>
</body>
</html>
