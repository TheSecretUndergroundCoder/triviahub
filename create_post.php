<?php
session_start();

// Include the database connection
require 'db.php';

// Redirect unauthorized users back to the PIN input page
if (!isset($_SESSION['pin_verified']) || $_SESSION['pin_verified'] !== true) {
    header('Location: pin-input.php');
    exit();
}

// Initialize error and success messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate form inputs
    if ($title === '' || $content === '') {
        $error = 'Both title and content are required.';
    } else {
        // Insert the post into the database
        try {
            $stmt = $pdo->prepare('INSERT INTO news_posts (title, content) VALUES (:title, :content)');
            $stmt->execute(['title' => $title, 'content' => $content]);
            $success = 'News post created successfully!';
        } catch (Exception $e) {
            $error = 'Error creating post: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create News Post</title>
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            max-width: 600px;
            margin: 0 auto 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .button:hover {
            background-color: #0056b3;
            text-decoration: none;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Create a New News Post</h1>

    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Form to create a news post -->
    <form method="POST" action="create_post.php">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required>
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea name="content" id="content" rows="10" required></textarea>
        </div>

        <div class="form-group">
            <button type="submit">Create Post</button>
            <p><a href="news.php" class="button">Back to News Page</a></p>
        </div>
    </form>



    <script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("create_post.php")) {
        var newUrl = window.location.href.replace("create_post.php", "create_post");
        window.history.replaceState({}, '', newUrl);
    }
</script>
</body>
</html>
