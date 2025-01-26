<?php
ob_start(); // Start output buffering

session_start();
include 'db.php'; // Ensure $pdo is properly included from db.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    // Get the user's subscription
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT s.max_quizzes FROM user_subscriptions us JOIN subscriptions s ON us.subscription_id = s.id WHERE us.user_id = :user_id AND NOW() BETWEEN us.start_date AND us.end_date");
    $stmt->execute(['user_id' => $user_id]);

    if ($stmt->rowCount() === 0) {
        die("You need an active subscription to access the quiz system.");
    }

    $subscription = $stmt->fetch();
    $max_quizzes = $subscription['max_quizzes'];

    // Count the number of quizzes the user has taken
    $stmt = $pdo->prepare("SELECT COUNT(*) AS quiz_count FROM results WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $quiz_count = $stmt->fetch()['quiz_count'];

if ($quiz_count >= $max_quizzes) {
    // Redirect to the max_quizzes.php page
    header("Location: max_quizzes.php");
    exit; // Always include exit after header redirection to prevent further execution
}


    // Ensure quiz_id is passed via GET parameter
    if (isset($_GET['quiz_id'])) {
        $quiz_id = $_GET['quiz_id'];
    } else {
        header("Location: error.php?error_message=" . urlencode("Error: Quiz ID not provided."));
        exit;
    }

    // Check if the quiz_id exists in the database
    $stmt = $pdo->prepare("SELECT title FROM quizzes WHERE id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);

    if ($stmt->rowCount() === 0) {
        header("Location: error.php?error_message=" . urlencode("Error: Invalid Quiz ID. The quiz you're trying to access doesn't exist."));
        exit;
    }

    $quiz_title = $stmt->fetch()['title'];

    // Fetch quiz questions along with the correct option
    $stmt = $pdo->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option, type, short_answer_correct FROM questions WHERE quiz_id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check for POST submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $score = 0;

        foreach ($questions as $question) {
            $user_answer = $_POST["question_" . $question['id']] ?? null;

            switch ($question['type']) {
                case 'multiple_choice':
                    if ($user_answer === $question['correct_option']) {
                        $score++;
                    }
                    break;

                case 'true_false':
                    $correct_option = strtolower(trim($question['correct_option']));
                    $normalized_answer = strtolower(trim($user_answer ?? ''));

                    if ($correct_option === 't') $correct_option = 'true';
                    if ($correct_option === 'f') $correct_option = 'false';
                    if ($normalized_answer === 't') $normalized_answer = 'true';
                    if ($normalized_answer === 'f') $normalized_answer = 'false';

                    if ($normalized_answer === $correct_option) {
                        $score++;
                    }
                    break;

                case 'short_answer':
                    $normalized_answer = isset($_POST["question_" . $question['id']]) ? strtolower(trim($_POST["question_" . $question['id']] ?? '')) : '';
                    if (isset($question['short_answer_correct'])) {
                        $correct_answer = strtolower(trim($question['short_answer_correct'] ?? ''));
                        if ($normalized_answer === $correct_answer) {
                            $score++;
                        }
                    }
                    break;
            }
        }

        // Store the result in the database
        $stmt = $pdo->prepare("INSERT INTO results (user_id, quiz_id, score) VALUES (:user_id, :quiz_id, :score)");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'quiz_id' => $quiz_id,
            'score'    => $score,
        ]);

        // Redirect to score page with score and quiz_id
        header("Location: score.php?score=$score&quiz_id=$quiz_id");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

ob_end_clean(); // Clean output buffer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz</title>
    <link rel="stylesheet" href="styles/take_quiz.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <a href="index.php" class="button">Go Home</a>
    <h2><?php echo htmlspecialchars($quiz_title); ?></h2>
    <form method="post" action="quiz?quiz_id=<?php echo htmlspecialchars($quiz_id); ?>">
        <div class="question-container">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-item">
                    <h4><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?></h4>

                    <?php switch ($question['type']):
                        case 'multiple_choice': ?>
                            <label>
                                <input type="radio" name="question_<?php echo $question['id']; ?>" value="A">
                                <?php echo htmlspecialchars($question['option_a']); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="question_<?php echo $question['id']; ?>" value="B">
                                <?php echo htmlspecialchars($question['option_b']); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="question_<?php echo $question['id']; ?>" value="C">
                                <?php echo htmlspecialchars($question['option_c']); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="question_<?php echo $question['id']; ?>" value="D">
                                <?php echo htmlspecialchars($question['option_d']); ?>
                            </label><br>
                            <?php break;

                        case 'true_false': ?>
                            <label>
                                <input type="radio" name="question_<?php echo $question['id']; ?>" value="true"> True
                            </label><br>
                            <label>
                                <input type="radio" name="question_<?php echo $question['id']; ?>" value="false"> False
                            </label><br>
                            <?php break;

                        case 'short_answer': ?>
                            <input type="text" name="question_<?php echo $question['id']; ?>" placeholder="Your answer"><br>
                            <?php break;
                        endswitch; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="navigation-buttons">
            <button type="button" id="prevBtn" class="prev-btn" disabled>Previous</button>
            <button type="button" id="nextBtn" class="next-btn">Next</button>
            <button type="submit" id="submitBtn" class="submit-btn" style="display:none;">Submit Quiz</button>
        </div>
    </form>

    <script>
        let currentQuestionIndex = 0;
        const questions = document.querySelectorAll('.question-item');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');

        function showQuestion(index) {
            questions.forEach((question, idx) => {
                question.style.display = idx === index ? 'block' : 'none';
            });

            // Enable/Disable navigation buttons
            prevBtn.disabled = index === 0;
            nextBtn.style.display = index === questions.length - 1 ? 'none' : 'inline-block';
            submitBtn.style.display = index === questions.length - 1 ? 'inline-block' : 'none';
        }

        nextBtn.addEventListener('click', () => {
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                showQuestion(currentQuestionIndex);
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                showQuestion(currentQuestionIndex);
            }
        });

        showQuestion(currentQuestionIndex); // Initialize with the first question
    </script>
</body>
</html>
