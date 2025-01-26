<?php
$user_id = $_SESSION['user_id']; // Assuming logged-in student

try {
    $stmt = $pdo->prepare("
        SELECT h.id, q.title, h.due_date 
        FROM homework h
        JOIN class_members cm ON h.class_id = cm.class_id
        JOIN quizzes q ON h.quiz_id = q.id
        WHERE cm.user_id = :user_id AND cm.role = 'student'
    ");
    $stmt->execute(['user_id' => $user_id]);
    $homework = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
