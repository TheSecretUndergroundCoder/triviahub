<?php
// Start the session and enable error reporting
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'db.php';

// Validate user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use htmlspecialchars for sanitization
    $quiz_title = htmlspecialchars($_POST['quiz_title'], ENT_QUOTES, 'UTF-8');
    $quiz_topic = htmlspecialchars($_POST['quiz_topic'], ENT_QUOTES, 'UTF-8');

    // Define valid topics for validation
    $valid_topics = ['Maths', 'English', 'Physics', 'Chemistry', 'Biology', 'Geography', 'History', 'Computing', 'General Knowledge'];

    if (!in_array($quiz_topic, $valid_topics)) {
        echo "<div class='notification error'>Invalid topic selected! <button onclick='dismissNotification(this)'>X</button></div>";
        exit;
    }

    try {
        // Begin database transaction
        $pdo->beginTransaction();

        // Insert the quiz title and topic into the quizzes table
        $stmt = $pdo->prepare("INSERT INTO quizzes (title, topic) VALUES (?, ?)");
        $stmt->execute([$quiz_title, $quiz_topic]);
        $quiz_id = $pdo->lastInsertId();

        // Insert questions and options
        for ($i = 0; $i < count($_POST['question']); $i++) {
            // Sanitize each question and options using htmlspecialchars
            $question_text = isset($_POST['question'][$i]) ? htmlspecialchars($_POST['question'][$i], ENT_QUOTES, 'UTF-8') : '';
            $type = isset($_POST['type'][$i]) ? htmlspecialchars($_POST['type'][$i], ENT_QUOTES, 'UTF-8') : '';
            $correct_option = null;

            $option_a = $option_b = $option_c = $option_d = null;

            if ($type === 'multiple_choice') {
                $option_a = htmlspecialchars($_POST['option_a'][$i], ENT_QUOTES, 'UTF-8');
                $option_b = htmlspecialchars($_POST['option_b'][$i], ENT_QUOTES, 'UTF-8');
                $option_c = htmlspecialchars($_POST['option_c'][$i], ENT_QUOTES, 'UTF-8');
                $option_d = htmlspecialchars($_POST['option_d'][$i], ENT_QUOTES, 'UTF-8');
                $correct_option = htmlspecialchars($_POST['correct_option_mc'][$i], ENT_QUOTES, 'UTF-8');
            } elseif ($type === 'true_false') {
                $correct_option = htmlspecialchars($_POST['correct_option_tf'][$i] ?? '', ENT_QUOTES, 'UTF-8');
            } elseif ($type === 'short_answer') {
                $short_answer_correct = htmlspecialchars($_POST['correct_answer'][$i], ENT_QUOTES, 'UTF-8');
                $correct_option = null; // Not used for short answers anymore
            } else {
                $short_answer_correct = null;
            }

            // Insert the question into the database
            $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, short_answer_correct, type) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $quiz_id,
                $question_text,
                ($type === 'multiple_choice' && !empty($option_a)) ? $option_a : null,
                ($type === 'multiple_choice' && !empty($option_b)) ? $option_b : null,
                ($type === 'multiple_choice' && !empty($option_c)) ? $option_c : null,
                ($type === 'multiple_choice' && !empty($option_d)) ? $option_d : null,
                $correct_option,
                $short_answer_correct,
                $type
            ]);
        }

        // Commit transaction
        $pdo->commit();
        echo "<div class='notification success'>Quiz created successfully! <button onclick='dismissNotification(this)'>X</button></div>";
    } catch (PDOException $e) {
        // Rollback transaction if error occurs
        $pdo->rollBack();
        echo "<div class='notification error'>Error creating quiz: " . $e->getMessage() . " <button onclick='dismissNotification(this)'>X</button></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
    <link rel="stylesheet" href="styles/quiz.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <?php include 'taskbar.php'; ?> <!-- Include Taskbar Script -->
    <h2>Create Quiz</h2>

    <form method="post">
        <!-- Quiz Title Input -->
        <input type="text" name="quiz_title" placeholder="Quiz Title" required><br>

        <!-- Topic Dropdown -->
        <label for="quiz_topic">Select Topic:</label>
        <select name="quiz_topic" id="quiz_topic" required>
            <option value="Maths">Maths</option>
            <option value="English">English</option>
            <option value="Physics">Physics</option>
            <option value="Chemistry">Chemistry</option>
            <option value="Biology">Biology</option>
            <option value="Geography">Geography</option>
            <option value="History">History</option>
            <option value="Computing">Computing</option>
            <option value="General Knowledge">General Knowledge</option>
        </select><br>

        <div class="question-block">
            <h4>Question 1</h4>
            <textarea name="question[]" placeholder="Question" required></textarea><br>

            <label for="question_type">Question Type:</label>
            <select name="type[]" id="question_type" class="question_type">
                <option value="multiple_choice">Multiple Choice</option>
                <option value="true_false">True/False</option>
                <option value="short_answer">Short Answer</option>
            </select><br>

            <div class="multiple-choice-options">
                <input type="text" name="option_a[]" placeholder="Option A"><br>
                <input type="text" name="option_b[]" placeholder="Option B"><br>
                <input type="text" name="option_c[]" placeholder="Option C"><br>
                <input type="text" name="option_d[]" placeholder="Option D"><br>
                <input type="text" name="correct_option_mc[]" placeholder="Correct Option (A, B, C, or D)"><br>
            </div>

            <div class="true-false-options" style="display: none;">
                <label>
                    <input type="radio" name="correct_option_tf[0]" value="true"> True
                </label><br>
                <label>
                    <input type="radio" name="correct_option_tf[0]" value="false"> False
                </label><br>
            </div>

            <div class="short-answer-options" style="display: none;">
                <input type="text" name="correct_answer[]" placeholder="Correct Answer"><br>
            </div>
        </div>

        <button type="button" id="add-question" class="button">Add Another Question</button>
        <button type="submit" class="button">Create Quiz</button>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let questionCount = 1;

            // Add new question block
            document.getElementById('add-question').addEventListener('click', function () {
                let questionBlock = document.querySelector('.question-block').cloneNode(true);

                questionCount++;
                questionBlock.querySelector('h4').innerText = `Question ${questionCount}`;
                questionBlock.querySelectorAll('textarea, input[type="text"]').forEach(input => input.value = '');

                // Reset radio buttons
                questionBlock.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);

                // Reset select dropdown and hide options
                let select = questionBlock.querySelector('select');
                let multipleChoiceOptions = questionBlock.querySelector('.multiple-choice-options');
                let trueFalseOptions = questionBlock.querySelector('.true-false-options');
                let shortAnswerOptions = questionBlock.querySelector('.short-answer-options');

                select.value = 'multiple_choice'; // Default to multiple choice
                multipleChoiceOptions.style.display = 'block';
                trueFalseOptions.style.display = 'none';
                shortAnswerOptions.style.display = 'none';

                document.querySelector('form').insertBefore(questionBlock, this);
            });

            // Event delegation for dynamically changing question type
            document.querySelector('form').addEventListener('change', function (e) {
                if (e.target && e.target.classList.contains('question_type')) {
                    const select = e.target;
                    const questionBlock = select.closest('.question-block');

                    const multipleChoiceOptions = questionBlock.querySelector('.multiple-choice-options');
                    const trueFalseOptions = questionBlock.querySelector('.true-false-options');
                    const shortAnswerOptions = questionBlock.querySelector('.short-answer-options');

                    // Show/hide options based on selected type
                    const type = select.value;
                    multipleChoiceOptions.style.display = type === 'multiple_choice' ? 'block' : 'none';
                    trueFalseOptions.style.display = type === 'true_false' ? 'block' : 'none';
                    shortAnswerOptions.style.display = type === 'short_answer' ? 'block' : 'none';
                }
            });

            // Dismiss notification
            window.dismissNotification = function (button) {
                button.closest('.notification').style.display = 'none';
            }
        });
    </script>
</body>
</html>
