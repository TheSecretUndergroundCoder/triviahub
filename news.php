<?php
// Include the database connection
require 'db.php';
session_start();  // Ensure session is started

// Initialize variables
$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';
$newsPosts = [];
$individualPost = null;

// Check if a specific post ID is requested in the URL
if (isset($_GET['post_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE id = :id');
    $stmt->execute(['id' => $_GET['post_id']]);
    $individualPost = $stmt->fetch();
} else {
    // If a search query is provided, search by title or ID
    if ($searchQuery !== '') {
        $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE title LIKE :query OR id = :query_id');
        $stmt->execute(['query' => "%$searchQuery%", 'query_id' => $searchQuery]);
        $newsPosts = $stmt->fetchAll();
    } else {
        // If no search query, display all news posts
        $stmt = $pdo->query('SELECT * FROM news_posts ORDER BY created_at DESC');
        $newsPosts = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News and Updates</title>
    <link rel="stylesheet" href="styles/news.css">
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
</head>
<body>

<?php include 'taskbar.php'; ?> <!-- Include Taskbar Script -->
<h1>News and Updates</h1>


    <form method="GET" class="search-form" action="news">
        <!-- Add the query parameter to the action URL, so it remains when submitting the form -->
        <input type="text" name="query" placeholder="Search by title or ID" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit">Search</button>   
    </form>


<?php if ($individualPost): ?>
    <!-- Display individual post -->
    <div class="news-post">
        <h2><?php echo htmlspecialchars($individualPost['title']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($individualPost['content'])); ?></p>
        <p><small>Posted on <?php echo htmlspecialchars($individualPost['created_at']); ?></small></p>
    </div>
    <!-- Link to go back to all posts with the search query included -->
    <p><a href="news?query=<?php echo urlencode($searchQuery); ?>" class="underline-animation">Back to all posts</a></p>
<?php else: ?>
    <!-- Display all or searched news posts -->
    <?php if ($newsPosts): ?>
        <?php foreach ($newsPosts as $post): ?>
            <div class="news-post">
                <h2>
                    <!-- Link to individual post with the search query included -->
                    <a class="underline-animation" href="news?post_id=<?php echo $post['id']; ?>&query=<?php echo urlencode($searchQuery); ?>">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a>
                </h2>
                <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 100))); ?>...</p>
                <p><small>Posted on <?php echo htmlspecialchars($post['created_at']); ?></small></p>
                <a class="underline-animation" href="news?post_id=<?php echo $post['id']; ?>&query=<?php echo urlencode($searchQuery); ?>">Read more</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No news posts found.</p>
    <?php endif; ?>
<?php endif; ?>

<footer class="footer glass">
    <p>&copy; 2024 TriviaHub</p>
    <a href="privacypolicy" class="underline-animation">Privacy Policy</a> | 
    <a href="safeguarding" class="underline-animation">Safeguarding</a> | 
    <a href="#" class="underline-animation">Terms of Service</a>
</footer>
<script>
    // Check if the current page URL contains 'news.php'
    if (window.location.pathname === '/news.php') {
        // Remove '.php' from the URL and redirect to the clean version
        var newUrl = window.location.pathname.replace('.php', '');
        window.history.replaceState(null, null, newUrl);
    }
</script>

</body>
</html>
