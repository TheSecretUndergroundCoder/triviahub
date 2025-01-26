<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
    font-family: 'Open Sans', sans-serif;
    background-color: #f5f5f5;
    text-align: center;
    margin: 0;
    padding: 0;
}

.error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

h1 {
    font-size: 100px;
    color: #ff5722;
}

h2 {
    font-size: 36px;
    color: #9e9e9e;
}

p {
    font-size: 20px;
    color: #616161;
    margin-bottom: 20px;
}

.home-button {
    background-color: #4CAF50;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
}
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>Oops! It seems like you're lost in the digital universe. The page you were looking for doesn't exist.</p>
        <a href="index.php" class="home-button">Take me home</a>
    </div>
    <script>
    // Remove '.php' from the URL without reloading the page
    if (window.location.href.endsWith("dashboard.php")) {
        var newUrl = window.location.href.replace("dashboard.php", "404");
        window.history.replaceState({}, '', newUrl);
    }
</script>
</body>
</html>