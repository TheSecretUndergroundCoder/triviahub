<?php
session_start();
include 'db.php'; // Ensure you have the database connection

// Ensure user is logged in and has admin or owner rights
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'owner')) {
    header('Location: index.php'); // Redirect if the user is not admin/owner
    exit();
}

// Default query parts
$searchQuery = '';
$searchValue = '';

// Check if a search term was submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchValue = $_GET['search'];
    // Add WHERE clause if search term exists
    $searchQuery = " WHERE id LIKE :search OR username LIKE :search";
}

try {
    // Base query for fetching users, apply search if needed
    $query_users = "SELECT id, username, email, type, warnings, banned, ban_reason FROM users" . $searchQuery . " LIMIT 4";
    
    // Prepare the query
    $stmt = $pdo->prepare($query_users);
    
    if (!empty($searchValue)) {
        // Bind the parameter only if there's a search value
        $stmt->bindValue(':search', '%' . $searchValue . '%');
    }
    
    // Execute the query
    $stmt->execute();
    
    // Fetch all users
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch quizzes
    $query_quizzes = "SELECT id, title FROM quizzes";
    $quizzes = $pdo->query($query_quizzes)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Total Accounts
$totalAccountsStmt = $pdo->query('SELECT COUNT(*) FROM users');
$totalAccounts = $totalAccountsStmt->fetchColumn();

// Total Banned Accounts
$totalBannedStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE banned = "1"');
$totalBanned = $totalBannedStmt->fetchColumn();

// Total Warnings
$totalWarningsStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE warnings > 0');
$totalWarnings = $totalWarningsStmt->fetchColumn();

// Total Accounts with Email
$totalEmailLinkedStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE email IS NOT NULL');
$totalEmailLinked = $totalEmailLinkedStmt->fetchColumn();

// Total Users Completed Account Setup
$completedSetupStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE setup_complete = 1');
$completedSetup = $completedSetupStmt->fetchColumn();

// Total Quizzes
$totalQuizzesStmt = $pdo->query('SELECT COUNT(*) FROM quizzes');
$totalQuizzes = $totalQuizzesStmt->fetchColumn();

// Active Users (Logged in last 30 days)
$activeUsersStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE last_login > NOW() - INTERVAL 30 DAY');
$activeUsers = $activeUsersStmt->fetchColumn();

// Users with Pending Account Setup
$pendingSetupStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE setup_complete = 0');
$pendingSetup = $pendingSetupStmt->fetchColumn();

// Active Users (Logged in in the last 30 days)
$activeUsersStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE last_login > NOW() - INTERVAL 30 DAY');
$activeUsers = $activeUsersStmt->fetchColumn();

// Inactive Users (Not logged in for 6 months)
$inactiveUsersStmt = $pdo->query('SELECT COUNT(*) FROM users WHERE last_login < NOW() - INTERVAL 6 MONTH');
$inactiveUsers = $inactiveUsersStmt->fetchColumn();


// Top Scoring Users (Quiz Scores)
$topScoringUsersStmt = $pdo->query('SELECT user_id, MAX(score) AS top_score FROM results GROUP BY user_id ORDER BY top_score DESC LIMIT 10');
$topScoringUsers = $topScoringUsersStmt->fetchAll();


// Count the total number of quiz responses in the 'responses' table
$responseCountStmt = $pdo->query('SELECT COUNT(*) FROM results');
$responseCount = $responseCountStmt->fetchColumn();

// Users Who Have Taken a Quiz
$uniqueQuizUsersStmt = $pdo->query('SELECT COUNT(DISTINCT user_id) FROM results');
$uniqueQuizUsers = $uniqueQuizUsersStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles/style.css"> <!-- Include your CSS here -->
  	<link rel="icon" href="images/admin_icon.png" type="image/x-icon">
    <style>
            .stats-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin: 20px;
    justify-content: center;
}

.stat-box {
    background-color: #f4f4f4;
    border: 2px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    width: 200px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 40px;
    font-weight: bold;
    color: #333;
    margin: 10px 0;
}

.stat-label {
    font-size: 16px;
    color: #777;
    margin: 0;
}


</style>
</head>
<body>

    <?php include('taskbar.php'); ?> <!-- Include the taskbar with the Admin Panel link -->
    <div class="admin-navigation">

            <a href="admin_panel.php" class="underline-animation">Dashboard</a><strong>	|	</strong>
            <a href="admin-system.php" class="underline-animation">Manage Users</a><strong>	|	</strong>	
            <a href="admin-system.php" class="underline-animation">Manage Quizzes</a><strong>	|	</strong>
            <a href="create_post.php" class="underline-animation">Create News Post</a><strong>	|	</strong>
            <a href="admin_contact" class="underline-animation">View Contact Form Responses</a><strong>	|	</strong>
            <a href="https://cp1.runhosting.com/file-manager/www/triviahub.getenjoyment.net#admin_panel.php;action=edit" target="_blank" class="underline-animation">Hosting</a><strong>	|	</strong>
            <a href="https://supportindeed.com/phpMyAdmin/signon.php?action=logout" target="_blank" class="underline-animation">Database (phpMyAdmin)</a><strong>	|	</strong>
            <a href="logout.php" class="underline-animation">Logout</a>

    </div>
    <h1>Admin Panel - Statistics</h1>

    <div class="stats-container">
        <!-- Total Accounts -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $totalAccounts; ?></div>
            <div class="stat-label">Total Accounts</div>
        </div>

        <!-- Total Banned Accounts -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $totalBanned; ?></div>
            <div class="stat-label">Banned Accounts</div>
        </div>

        <!-- Total Warnings -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $totalWarnings; ?></div>
            <div class="stat-label">Total Warnings</div>
        </div>

        <!-- Total Accounts with Email -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $totalEmailLinked; ?></div>
            <div class="stat-label">Accounts with Email</div>
        </div>

        <!-- Total Completed Setup -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $completedSetup; ?></div>
            <div class="stat-label">Users Completed Setup</div>
        </div>

        <!-- Total Quizzes -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $totalQuizzes; ?></div>
            <div class="stat-label">Total Quizzes</div>
        </div>

        <!-- Active Users -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $activeUsers; ?></div>
            <div class="stat-label">Active Users (Last 30 Days)</div>
        </div>

        <!-- Users with Pending Setup -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $pendingSetup; ?></div>
            <div class="stat-label">Pending Account Setup</div>
        </div>

        <!-- Inactive Users -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $inactiveUsers; ?></div>
            <div class="stat-label">Inactive Users (Last 6 Months)</div>
        </div>

        <!-- Top Scoring Users -->
        <div class="stat-box">
            <div class="stat-number"><?php echo count($topScoringUsers); ?></div>
            <div class="stat-label">Top Scoring Users</div>
        </div>

        <!-- Total Quizzes Taken -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $responseCount; ?></div>
            <div class="stat-label">Total Quizzes Taken</div>
        </div>

        <!-- Users Who Took a Quiz -->
        <div class="stat-box">
            <div class="stat-number"><?php echo $uniqueQuizUsers; ?></div>
            <div class="stat-label">Users Who Took a Quiz</div>
        </div>
    </div>
        <footer class="footer glass">
        <p>&copy; 2024 TriviaHub</p>
        <a href="privacypolicy.php" class="underline-animation">Privacy Policy</a> | 
        <a href="safeguarding.html" class="underline-animation">Safeguarding</a> | 
        <a href="#" class="underline-animation">Terms of Service</a>
    </footer>
        
        <script>
        // Remove '.php' from the URL without reloading the page
        if (window.location.href.endsWith("admin_panel.php")) {
            var newUrl = window.location.href.replace("admin_panel.php", "admin-panel");
            window.history.replaceState({}, '', newUrl);
        }
</script>
</body>
</html>
