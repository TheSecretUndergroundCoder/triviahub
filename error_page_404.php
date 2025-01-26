<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
        }

        .error-container {
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            font-size: 36px;
            color: #333;
        }

        p {
            font-size: 18px;
            color: #666;
        }

        img {
            max-width: 400px;
            margin-top: 20px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oops! Page Not Found</h1>
        <p>The page you're looking for doesn't exist.</p>
        <img src="404-image.jpg" alt="404 Error Image">
        <p>You can:</p>
        <ul>
            <li><a href="/">Go back to the homepage</a></li>
            <li><a href="#report-bug">Report this issue</a></li>
        </ul>

        <div id="report-bug">
            <h2>Report a Bug</h2>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Process the form data here
                $email = $_POST['email'];
                $description = $_POST['description'];

                // Send an email, log to a file, or use a bug tracking system
                // Example: Send an email
                mail("finnscoggins2@example.com", "Bug Report", "Email: $email\nDescription: $description");

                echo "<p>Thank you for your report. We'll look into it.</p>";
            } else {
                ?>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <label for="email">Your Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="description">Description of the Issue:</label>
                    <textarea id="description" name="description" required></textarea>

                    <button type="submit">Submit Report</button>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>