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
        $userId = filter_var($data['userId'], FILTER_VALIDATE_INT);
        $banReason = htmlspecialchars($data['reason'] ?? '', ENT_QUOTES, 'UTF-8');

        if (!$userId || empty($banReason)) {
            echo json_encode(['error' => 'Invalid or missing User ID or Ban Reason.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET banned = 1, ban_reason = :banReason WHERE id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':banReason', $banReason, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'User has been banned successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
