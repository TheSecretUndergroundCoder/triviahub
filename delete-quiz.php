<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'owner')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized access.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $quizId = filter_var($data['quizId'], FILTER_VALIDATE_INT);

        if (!$quizId) {
            echo json_encode(['error' => 'Invalid or missing Quiz ID.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = :quizId");
        $stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Quiz has been deleted successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
