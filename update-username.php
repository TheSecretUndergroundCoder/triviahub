<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $userId = $_SESSION['user_id'];

    if (empty($newUsername)) {
        header("Location: account.php?error=Username cannot be empty");
        exit;
    }

    try {
        // Check if username is already taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $stmt->execute(['username' => $newUsername, 'id' => $userId]);
        if ($stmt->fetch()) {
            header("Location: account.php?error=Username already taken");
            exit;
        }

        // Update username
        $stmt = $pdo->prepare("UPDATE users SET username = :username WHERE id = :id");
        $stmt->execute(['username' => $newUsername, 'id' => $userId]);

        $_SESSION['user_name'] = $newUsername; // Update session
        header("Location: account.php?message=Username updated successfully");
    } catch (PDOException $e) {
        header("Location: account.php?error=" . urlencode($e->getMessage()));
    }
}
