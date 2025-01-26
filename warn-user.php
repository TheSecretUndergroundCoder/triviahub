<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ensure the user is an admin or owner
        if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'owner')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized access.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = filter_var($data['userId'], FILTER_VALIDATE_INT);

        if (!$userId) {
            echo json_encode(['error' => 'Invalid or missing User ID.']);
            exit;
        }

        // Increment the user's warning count
        $stmt = $pdo->prepare("UPDATE users SET warnings = warnings + 1 WHERE id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'User has been warned successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
