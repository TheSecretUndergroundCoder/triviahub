<?php
session_start(); // Start the session
include 'db.php'; // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is an admin or owner
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT type FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure user is either admin or owner
if ($user['type'] != 'admin' && $user['type'] != 'owner') {
    die("Access denied: You do not have permission to create schools.");
}

// Handle creating a school
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_school'])) {
    $school_name = $_POST['school_name'];

    try {
        // Insert the new school
        $stmt = $pdo->prepare("INSERT INTO schools (name, created_at) VALUES (:name, NOW())");
        $stmt->execute(['name' => $school_name]);

        $_SESSION['success_message'] = "School created successfully!";
        header("Location: manage_schools.php"); // Redirect after success
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: manage_schools.php"); // Redirect to the same page after failure
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Schools</title>
    <link rel="stylesheet" href="styles/education.css">
</head>
<body>
    <h1>Manage Schools</h1>

    <!-- Display success or error message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success"><?= $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?= $_SESSION['error_message']; ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Form to create a new school -->
    <h2>Create a New School</h2>
    <form method="POST" action="manage_schools.php">
        <label for="school_name">School Name:</label>
        <input type="text" name="school_name" required>

        <button type="submit" name="create_school">Create School</button>
    </form>

    <!-- Optionally, list all schools if required -->
    <h2>Existing Schools</h2>
    <table>
        <thead>
            <tr>
                <th>School Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                // Fetch all schools if the user has the right privileges
                $stmt = $pdo->query("SELECT * FROM schools");
                $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($schools)) {
                    foreach ($schools as $school) {
                        echo "<tr>
                                <td>" . htmlspecialchars($school['name']) . "</td>
                                <td><a href='edit_school.php?id=" . $school['id'] . "'>Edit</a> | 
                                    <a href='delete_school.php?id=" . $school['id'] . "' onclick=\"return confirm('Are you sure you want to delete this school?');\">Delete</a></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No schools found.</td></tr>";
                }
            } catch (PDOException $e) {
                die("Error: " . $e->getMessage());
            }
            ?>
        </tbody>
    </table>
</body>
</html>
