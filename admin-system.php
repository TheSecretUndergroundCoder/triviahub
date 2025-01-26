<?php
session_start();
include 'db.php';

// Ensure user is admin or owner
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'owner')) {
    header('Location: index.php');
    exit();
}

try {
    $query_users = "SELECT id, username, email, type, warnings, banned FROM users";
    $query_quizzes = "SELECT id, title FROM quizzes";
    
    $users = $pdo->query($query_users)->fetchAll(PDO::FETCH_ASSOC);
    $quizzes = $pdo->query($query_quizzes)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/admin_icon.png" type="image/x-icon">
    <title>Admin System</title>
    <style>
        /* Basic styling for the admin page */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f9f9f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        .status {
            color: green;
            margin-top: 20px;
        }
            
        a {
            text-decoration: none;
            }
    </style>
</head>
<body>

<h1>Admin System</h1>
        <a class="btn"href="index.php">Go Home</a>

<h2>All User Accounts</h2>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Account Type</th>
            <th>Warnings</th>
            <th>Banned</th>
            <th>Ban Reason</th>
            <th>Actions</th>
        </tr>
    </thead>
<tbody>
    <?php foreach ($users as $user): ?>
        <tr id="user_<?= $user['id'] ?>">
            <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
            <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($user['type'] ?? '') ?></td>
            <td id="warning_<?= $user['id'] ?>"><?= htmlspecialchars($user['warnings'] ?? '') ?></td>
            <td id="banned_<?= $user['id'] ?>"><?= $user['banned'] ? 'Yes' : 'No' ?></td>
            <td id="ban_reason_<?= $user['id'] ?>">
                <?= htmlspecialchars($user['ban_reason'] ?? "No reason provided.") ?>
            </td>
            <td>
                <button class="btn" onclick="warnUser(<?= $user['id'] ?>)">Warn</button>
                <?php if (!$user['banned']): ?>
                    <button class="btn" onclick="banUser(<?= $user['id'] ?>)">Ban</button>
                <?php else: ?>
                    <button class="btn btn-danger" onclick="unbanUser(<?= $user['id'] ?>)">Unban</button>
                <?php endif; ?>
                <button class="btn btn-danger" onclick="deleteUser(<?= $user['id'] ?>)">Delete</button>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

</table>


<h2>All Quizzes</h2>
<table>
    <thead>
        <tr>
            <th>Quiz Title</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quizzes as $quiz): ?>
            <tr id="quiz_<?= $quiz['id'] ?>">
    			<td><?= htmlspecialchars($quiz['title'] ?? '') ?></td>
    			<td>
        			<button class="btn" onclick="viewQuiz(<?= $quiz['id'] ?>)">View Questions</button>
        			<button class="btn btn-danger" onclick="deleteQuiz(<?= $quiz['id'] ?>)">Delete Quiz</button>
    			</td>
			</tr>

        <?php endforeach; ?>
    </tbody>
</table>

<!-- Quiz Questions Section -->
<div id="quizQuestionsSection" style="display:none;">
    <h3>Quiz Questions</h3>
    <div id="quizQuestions"></div>
    <button class="btn" onclick="closeQuizSection()">Close</button>
</div>


<!-- Action Status Section -->
<div class="status" id="actionStatus"></div>

<script>
// View quiz questions in a popup
function viewQuiz(quizId) {
    // Fetch quiz questions using AJAX
    fetch('get-quiz-questions.php?quiz_id=' + quizId)
        .then(response => response.json())  // Parse the JSON response
        .then(data => {
            if (data.error) {
                // If there was an error (like no quiz ID), alert the user
                alert(data.error);
                return;
            }

            let quizQuestions = data.questions;
            let output = '<ul>';

            // Loop through each question
            quizQuestions.forEach(q => {
                // Display the question and the multiple choice options
                output += `<li><strong>${q.question}</strong><br>`;
                output += `<ul>
                    <li>A) ${q.option_a}</li>
                    <li>B) ${q.option_b}</li>
                    <li>C) ${q.option_c}</li>
                    <li>D) ${q.option_d}</li>
                </ul>`;
                output += `Correct Answer: ${q.correct_option}</li><br>`;
            });

            output += '</ul>';
            document.getElementById('quizQuestions').innerHTML = output;
            document.getElementById('quizPopup').style.display = 'block'; // Show the popup with questions
        })
        .catch(error => {
            // Handle any errors during the fetch request
            console.error('Error fetching quiz questions:', error);
            alert('There was an error fetching quiz questions.');
        });
}

// Close popup when the user is done
function closePopup() {
    document.getElementById('quizPopup').style.display = 'none';
}


    // Close quiz questions section
    function closeQuizSection() {
        document.getElementById('quizQuestionsSection').style.display = 'none';
    }

function warnUser(userId) {
    fetch('warn-user.php', { 
        method: 'POST', 
        body: JSON.stringify({ userId: userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to warn user');
        return response.text();
    })
    .then(message => {
        const warningCell = document.getElementById('warning_' + userId);
        let currentWarnings = parseInt(warningCell.innerText) || 0; // Fallback to 0 if NaN
        warningCell.innerText = currentWarnings + 1;
        document.getElementById('actionStatus').innerText = message;
    })
    .catch(error => {
        console.error('Error warning user:', error);
        document.getElementById('actionStatus').innerText = 'Failed to warn user';
    });
}




function banUser(userId) {
    const reason = prompt('Please provide a reason for banning this user:');
    if (!reason) {
        alert('You must provide a reason to ban the user.');
        return;
    }

    fetch('ban-user.php', {
        method: 'POST',
        body: JSON.stringify({ userId: userId, reason: reason }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('banned_' + userId).innerText = 'Yes';
            document.getElementById('ban_reason_' + userId).innerText = reason;
            document.getElementById('actionStatus').innerText = data.message;
        } else {
            alert(data.error || 'An error occurred.');
        }
    });
}

function unbanUser(userId) {
    if (!confirm('Are you sure you want to unban this user?')) return;

    fetch('unban-user.php', {
        method: 'POST',
        body: JSON.stringify({ userId: userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('banned_' + userId).innerText = 'No';
            document.getElementById('ban_reason_' + userId).innerText = 'N/A';
            document.getElementById('actionStatus').innerText = data.message;
        } else {
            alert(data.error || 'An error occurred.');
        }
    });
}


// Delete user
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('delete-user.php', { 
            method: 'POST', 
            body: JSON.stringify({ userId: userId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.text())
        .then(message => {
            document.getElementById('user_' + userId).remove();
            document.getElementById('actionStatus').innerText = message;
            refreshPage();
        });
    }
}

function deleteQuiz(quizId) {
    if (confirm('Are you sure you want to delete this quiz?')) {
        fetch('delete-quiz.php', { 
            method: 'POST', 
            body: JSON.stringify({ quizId: quizId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to delete quiz');
            return response.text();
        })
        .then(message => {
            const quizRow = document.getElementById('quiz_' + quizId);
            if (quizRow) quizRow.remove();
            document.getElementById('actionStatus').innerText = message;
        })
        .catch(error => {
            console.error('Error deleting quiz:', error);
            document.getElementById('actionStatus').innerText = 'Failed to delete quiz';
        });
    }
}

// Function to refresh the page
function refreshPage() {
    setTimeout(() => {
        location.reload();
    }, 1500);  // Page refresh after 1.5 seconds
}

</script>
        
<script>
        // Remove '.php' from the URL without reloading the page
        if (window.location.href.endsWith("admin-system.php")) {
            var newUrl = window.location.href.replace("admin-system.php", "admin-system");
            window.history.replaceState({}, '', newUrl);
        }
</script>

</body>
</html>
