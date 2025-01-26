<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f0f0;
        }

        .container {
            text-align: center;
            padding: 50px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #f00;
            font-size: 4rem;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        img {
            max-width: 300px;
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
        }

        a:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="404-image.jpg" alt="404 Image">
        <h1>404 - Page Not Found</h1>
        <p>The page you are looking for does not exist.</p>
        <a href="/">Go to Homepage</a>
    </div>

    <script>
        // Redirect after 5 seconds
        setTimeout(function() {
            window.location.href = "/";
        }, 5000);
    </script>
</body>
</html>