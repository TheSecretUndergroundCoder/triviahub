<?php
// Include database connection
include 'db.php';

// Check if the quiz ID is provided
if (isset($_GET['quiz_id'])) {
    $quizId = $_GET['quiz_id'];

    // Prepare SQL to fetch questions for a specific quiz
    $stmt = $pdo->prepare("SELECT question, option_a, option_b, option_c, option_d, correct_option FROM questions WHERE quiz_id = :quiz_id");
    $stmt->execute(['quiz_id' => $quizId]);

    // Fetch all the questions
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the questions as JSON
    echo json_encode(['questions' => $questions]);
} else {
    // If no quiz ID is provided, return an error
    echo json_encode(['error' => 'No quiz ID provided']);
}
?>
