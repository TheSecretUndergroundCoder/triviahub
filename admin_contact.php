<?php
session_start();
include 'db.php';

// Check if the user is logged in and has an appropriate type (admin or owner)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the user type
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT type FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    $user_type = $user['type'];
    
    // Restrict access to admin and owner types only
    if ($user_type !== 'admin' && $user_type !== 'owner') {
        header("Location: index.php");  // Redirect to the homepage or an error page
        exit;
    }

    // Fetch all contacts
    $contacts = $pdo->query("SELECT contacts.*, users.username 
                             FROM contacts 
                             JOIN users ON contacts.user_id = users.id")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching contacts: " . $e->getMessage());
}

// Handle actions (update status, flag, delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_id'])) {
        // Delete contact
        $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
        if ($delete_id) {
            $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = :id");
            $stmt->execute([':id' => $delete_id]);
        }
    } elseif (isset($_POST['flag_id'])) {
        // Flag contact
        $flag_id = filter_input(INPUT_POST, 'flag_id', FILTER_VALIDATE_INT);
        if ($flag_id) {
            $stmt = $pdo->prepare("UPDATE contacts SET flagged = CASE WHEN flagged = 1 THEN 0 ELSE 1 END WHERE id = :id");
            $stmt->execute([':id' => $flag_id]);
        }
    } elseif (isset($_POST['status_id'], $_POST['status'])) {
        // Update status
        $status_id = filter_input(INPUT_POST, 'status_id', FILTER_VALIDATE_INT);
        $status = in_array($_POST['status'], ['Not Reviewed', 'In Progress', 'Completed'])
            ? $_POST['status'] : 'Not Reviewed';
        if ($status_id) {
            $stmt = $pdo->prepare("UPDATE contacts SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $status,
                ':id' => $status_id
            ]);
        }
    }

    // Redirect to avoid refresh issues (PRG pattern)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Contact Management</title>
    <link rel="stylesheet" href="styles/contact.css">
    <style>
/* General Body Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7f9;
    margin: 0;
    padding: 0;
}

/* Container */
.container {
    width: 90%;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Header */
h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

/* Table */
.contact-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.contact-table th,
.contact-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.contact-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.contact-table tr:hover {
    background-color: #f1f1f1;
}

/* Flag Icon */
.flag-icon {
    color: red;
    font-size: 18px;
}

/* Status Dot */
.status-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

/* Buttons */
button, select {
    padding: 8px 12px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
}

.delete-button {
    background-color: #dc3545;
    color: white;
}

.delete-button:hover {
    background-color: #c82333;
}

/* Message Preview */
.message-preview p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.message-full {
    display: none;
    padding: 10px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
    margin-top: 10px;
}

/* Back Link */
.back-link {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.back-link:hover {
    background-color: #0056b3;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-table th,
    .contact-table td {
        font-size: 12px;
        padding: 8px;
    }

    button, select {
        font-size: 12px;
        padding: 6px 10px;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Contact Submissions</h1>
        <table class="contact-table">
            <tr>
                <th>Flag</th>
                <th>Status</th>
                <th>ID</th>
                <th>User</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Submitted At</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($contacts as $contact): ?>
                <tr>
                    <!-- Flag Column -->
                    <td>
                        <?php if ($contact['flagged']): ?>
                            <span class="flag-icon">🚩</span>
                        <?php endif; ?>
                    </td>

                    <!-- Status Indicator -->
                    <td>
                        <?php
                        // Status dot color based on status
                        $status_dot_color = '';
                        switch ($contact['status']) {
                            case 'Not Reviewed':
                                $status_dot_color = 'red';
                                break;
                            case 'In Progress':
                                $status_dot_color = 'orange';
                                break;
                            case 'Completed':
                                $status_dot_color = 'green';
                                break;
                            default:
                                $status_dot_color = 'gray';
                        }
                        ?>
                        <span class="status-dot" style="background-color: <?php echo $status_dot_color; ?>;"></span>
                    </td>

                    <td><?php echo $contact['id']; ?></td>
                    <td><?php echo htmlspecialchars($contact['username']); ?></td>
                    <td><?php echo htmlspecialchars($contact['subject']); ?></td>

                    <!-- Message Preview -->
                    <td>
                        <div class="message-preview" id="message-preview-<?php echo $contact['id']; ?>">
                            <p><?php echo htmlspecialchars(substr($contact['message'], 0, 80)); ?>...</p>
                            <button onclick="toggleMessage(<?php echo $contact['id']; ?>)">Read More</button>
                        </div>
                        <div class="message-full" id="message-full-<?php echo $contact['id']; ?>">
                            <p><?php echo nl2br(htmlspecialchars($contact['message'])); ?></p>
                            <button onclick="toggleMessage(<?php echo $contact['id']; ?>)">Read Less</button>
                        </div>
                    </td>

                    <td><?php echo htmlspecialchars($contact['submitted_at']); ?></td>
                    <td>
                        <!-- Actions -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $contact['id']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="flag_id" value="<?php echo $contact['id']; ?>">
                            <button type="submit"><?php echo $contact['flagged'] ? 'Unflag' : 'Flag'; ?></button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="status_id" value="<?php echo $contact['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option <?php echo $contact['status'] == 'Not Reviewed' ? 'selected' : ''; ?>>Not Reviewed</option>
                                <option <?php echo $contact['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option <?php echo $contact['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="index.php">Back to Home</a>
    </div>

    <script>
        function toggleMessage(contactId) {
            var preview = document.getElementById('message-preview-' + contactId);
            var fullMessage = document.getElementById('message-full-' + contactId);

            if (fullMessage.style.display === 'none') {
                fullMessage.style.display = 'block';
                preview.style.display = 'none';
            } else {
                fullMessage.style.display = 'none';
                preview.style.display = 'block';
            }
        }
<script>
// Remove '.php' extension dynamically
if (window.location.href.includes('.php')) {
    const newUrl = window.location.href.replace('.php', '');
    window.history.replaceState({}, '', newUrl);
}
</script>
</body>
</html>
