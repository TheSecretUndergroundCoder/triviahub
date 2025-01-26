<?php
session_start(); // Move session_start() to the top

include 'db.php'; // Assuming 'db.php' contains database connection details

// Validate user login and redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Quiz</title>
    <link rel="stylesheet" href="styles/quiz.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
    <style>
        .notification {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .notification.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .notification.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .notifbutton {
            float: right;
            margin-left: 10px;
            border: none;
            background-color: transparent;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <a href="index.php" class="underline-animation">Go Home</a>
    <h2>Create Quiz</h2>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize user input to prevent SQL injection
        $quiz_title = filter_input(INPUT_POST, 'quiz_title', FILTER_SANITIZE_STRING);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO quizzes (title) VALUES (?)");
            $stmt->execute([$quiz_title]);
            $quiz_id = $pdo->lastInsertId();

            for ($i = 0; $i < count($_POST['question']); $i++) {
                $question_text = filter_input(INPUT_POST, 'question[' . $i . ']', FILTER_SANITIZE_STRING);
                $type = filter_input(INPUT_POST, 'type[' . $i . ']', FILTER_SANITIZE_STRING);

                $option_a = $option_b = $option_c = $option_d = null;
                $correct_option = "";

                if ($type === 'multiple_choice') {
                    $option_a = filter_input(INPUT_POST, 'option_a[' . $i . ']', FILTER_SANITIZE_STRING);
                    $option_b = filter_input(INPUT_POST, 'option_b[' . $i . ']', FILTER_SANITIZE_STRING);
                    $option_c = filter_input(INPUT_POST, 'option_c[' . $i . ']', FILTER_SANITIZE_STRING);
                    $option_d = filter_input(INPUT_POST, 'option_d[' . $i . ']', FILTER_SANITIZE_STRING);
                    $correct_option = filter_input(INPUT_POST, 'correct_option[' . $i . ']', FILTER_SANITIZE_STRING);
                } elseif ($type === 'true_false') {
                    $correct_option = filter_input(INPUT_POST, 'correct_option[' . $i . ']', FILTER_SANITIZE_STRING);
                } elseif ($type === 'short_answer') {
                    $correct_answer = filter_input(INPUT_POST, 'correct_answer[' . $i . ']', FILTER_SANITIZE_STRING);
                }

                $stmt = $pdo->prepare("
                    INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, type)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $quiz_id,
                    $question_text,
                    $option_a,
                    $option_b,
                    $option_c,
                    $option_d,
                    $correct_option,
                    $type
                ]);
            }

$pdo->commit();
echo "<div class='notification success'>Quiz created successfully! <button class='notifbutton' onclick='dismissNotification(this)'>x</button></div>";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<div class='notification error'>Error creating quiz: " . $e->getMessage() . " <button class='notifbutton' onclick='dismissNotification(this)'>x</button></div>";
}
} else {
    echo "<div class='notification error'>Error: Could not create quiz (invalid request method). <button class='notifbutton' onclick='dismissNotification(this)'>x</button></div>";
}


    ?>

    <form method="post">
        <input type="text" name="quiz_title" placeholder="Quiz Title" required><br>

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
                <input type="text" name="option_a[]" placeholder="Option A" required><br>
                <input type="text" name="option_b[]" placeholder="Option B" required><br>
                <input type="text" name="option_c[]" placeholder="Option C" required><br>
                <input type="text" name="option_d[]" placeholder="Option D" required><br>
                <input type="text" name="correct_option[]" placeholder="Correct Option (A, B, C, or D)" required><br>
            </div>

            <div class="true-false-options" style="display: none;">
                <input type="radio" name="correct_option[]" value="true"> True<br>
                <input type="radio" name="correct_option[]" value="false"> False<br>
            </div>

            <div class="short-answer-options" style="display: none;">
                <input type="text" name="correct_answer[]" placeholder="Correct Answer (optional)"><br>
            </div>
        </div>

        <button type="button" id="add-question">Add Another Question</button>
        <button type="submit">Create Quiz</button>
    </form>

    <script>
        document.getElementById('add-question').addEventListener('click', function() {
            let questionBlock = document.querySelector('.question-block').cloneNode(true);
            questionBlock.querySelector('h4').innerText = `Question ${document.querySelectorAll('.question-block').length + 1}`;
            document.querySelector('form').insertBefore(questionBlock, this);
        });

        document.querySelectorAll('select[name="type[]"]').forEach(function (select) {
            select.addEventListener('change', function () {
                const parentBlock = select.closest('.question-block');
                const type = select.value;

                parentBlock.querySelector('.multiple-choice-options').style.display = type === 'multiple_choice' ? 'block' : 'none';
                parentBlock.querySelector('.true-false-options').style.display = type === 'true_false' ? 'block' : 'none';
                parentBlock.querySelector('.short-answer-options').style.display = type === 'short_answer' ? 'block' : 'none';
            });
        });

        function dismissNotification(button) {
            button.parentNode.parentNode.removeChild(button.parentNode);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("create_quiz_new.php")) {
        var newUrl = window.location.href.replace("create_quiz_new.php", "create_new");
        window.history.replaceState({}, '', newUrl);
    }
</script>
</body>
</html>
