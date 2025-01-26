<?php
session_start();

include 'db.php'; // Ensure this includes the $pdo connection

if (isset($_GET['message'])) {
    echo "<p>" . htmlspecialchars($_GET['message']) . "</p>";
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];

try {
    // Fetch user's name
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    $user_name = $user['username'];

    // Fetch user subscription details
    $stmt = $pdo->prepare("
        SELECT s.plan_name, s.max_quizzes, us.start_date, us.end_date 
        FROM user_subscriptions us 
        JOIN subscriptions s ON us.subscription_id = s.id 
        WHERE us.user_id = :id AND NOW() BETWEEN us.start_date AND us.end_date
    ");
    $stmt->execute(['id' => $user_id]);
    $subscription = $stmt->fetch();

    // Fetch quiz results for the user, including total questions
    $stmt = $pdo->prepare("
        SELECT 
            quizzes.title, 
            results.score, 
            results.date,
            (SELECT COUNT(*) FROM questions WHERE questions.quiz_id = quizzes.id) AS total_questions
        FROM results 
        JOIN quizzes ON results.quiz_id = quizzes.id 
        WHERE results.user_id = :id
    ");
    $stmt->execute(['id' => $user_id]);
    $quiz_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaHub - Home</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=block" />
</head>
<body>
<?php include('taskbar.php'); ?> <!-- Include the taskbar -->

    <!-- Welcome Section -->
    <h1> ✨ Welcome, <?php echo htmlspecialchars($user_name); ?> ✨ </h1>

    <!-- Subscription Bar -->
    <div class="subscription-bar glass">
        <?php if ($subscription): ?>
            <p>
                <span>Active Subscription:</span> <?php echo htmlspecialchars($subscription['plan_name']); ?><br>
                <span>Max Quizzes:</span> <?php echo htmlspecialchars($subscription['max_quizzes']); ?><br>
                <span>Subscription Validity:</span> 
                <?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['start_date']))); ?> to 
                <?php echo htmlspecialchars(date("F j, Y", strtotime($subscription['end_date']))); ?>
            </p>
            <a href="change_plan.php" class="underline-animation">Change Plan</a>
        <?php else: ?>
            <p>No active subscription. <a href="purchase.php" class="underline-animation">Purchase a subscription</a></p>
        <?php endif; ?>
    </div>
<br>
<div class="analysis-container glass">
    <h3 style="font-size: 2.2rem; font-weight: bold; text-align: center; margin-bottom: 30px; color: #333;">Your Quiz Performance Analysis</h3>
    <?php
    // Analyze quiz results and suggest resources
    $weak_areas = [];
    foreach ($quiz_results as $result) {
        if ($result['score'] < $result['total_questions'] * 0.6) {
            $weak_areas[$result['title']] = $result['score'] . '/' . $result['total_questions'];
        }
    }

    $suggestions = [];
    foreach ($weak_areas as $quiz_title => $score) {
        $stmt = $pdo->prepare("SELECT resource_url FROM resources WHERE topic COLLATE utf8mb4_general_ci = (SELECT topic COLLATE utf8mb4_general_ci FROM quizzes WHERE title = :quiz_title LIMIT 1)");
        $stmt->execute(['quiz_title' => $quiz_title]);
        $resources = $stmt->fetchAll();
        if ($resources) {
            $suggestions[$quiz_title] = $resources;
        }
    }
    ?>

    <?php if (!empty($weak_areas)): ?>
        <div class="weak-areas">
            <h4>Weak Areas</h4>
            <ul>
                <?php foreach ($weak_areas as $quiz_title => $score): ?>
                    <li class="weak-area-item">
                        <strong><?php echo htmlspecialchars($quiz_title); ?>:</strong>
                        <span>Your score: <?php echo htmlspecialchars($score); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="suggestions">
            <h4>Suggested Resources</h4>
            <ul>
                <?php foreach ($suggestions as $quiz_title => $resources): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($quiz_title); ?>:</strong>
                        <ul>
                            <?php foreach ($resources as $resource): ?>
                                <li><a href="<?php echo htmlspecialchars($resource['resource_url']); ?>" target="_blank" class="resource_links">Resource</a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p style="text-align: center; font-size: 1.5rem; color: #28a745;">No weak areas found. Keep up the great work!</p>
    <?php endif; ?>
</div>






<!-- Quiz Results Section -->
<h2>Your Quiz Results</h2>
<div class="glass">
    <?php if (empty($quiz_results)): ?>
        <p>Take a quiz and view more information <a href="find_quiz.php" class="underline-animation">here</a>.</p>
    <?php else: ?>
        <table border="2">
            <tr>
                <th>Quiz Title</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
            <?php foreach ($quiz_results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['title']); ?></td>
                    <td><?php echo htmlspecialchars($result['score']) . '/' . htmlspecialchars($result['total_questions']); ?></td>
                    <td><?php echo htmlspecialchars(date("F j, Y", strtotime($result['date']))); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>


    <!-- Footer Section -->
    <footer class="footer glass">
        <p>&copy; 2024 TriviaHub</p>
        <a href="privacypolicy.php" class="underline-animation">Privacy Policy</a> | 
        <a href="safeguarding.html" class="underline-animation">Safeguarding</a> | 
        <a href="#" class="underline-animation">Terms of Service</a>
    </footer>

<script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("index.php")) {
        var newUrl = window.location.href.replace("index.php", "home");
        window.history.replaceState({}, '', newUrl);
    }
</script>

</body>
</html>
