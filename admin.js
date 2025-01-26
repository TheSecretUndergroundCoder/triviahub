function warnUser(userId) {
    fetch('warn-user.php', {
        method: 'POST',
        body: JSON.stringify({ userId: userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.text())
    .then(message => alert(message))
    .catch(error => console.error('Error:', error));
}

function banUser(userId) {
    const reason = prompt('Provide a reason to ban this user:');
    if (reason) {
        fetch('ban-user.php', {
            method: 'POST',
            body: JSON.stringify({ userId: userId, reason: reason }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => alert(data.message))
        .catch(error => console.error('Error:', error));
    }
}

function unbanUser(userId) {
    if (confirm('Are you sure you want to unban this user?')) {
        fetch('unban-user.php', {
            method: 'POST',
            body: JSON.stringify({ userId: userId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => alert(data.message))
        .catch(error => console.error('Error:', error));
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('delete-user.php', {
            method: 'POST',
            body: JSON.stringify({ userId: userId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.text())
        .then(message => alert(message))
        .catch(error => console.error('Error:', error));
    }
}

function deleteQuiz(quizId) {
    if (confirm('Are you sure you want to delete this quiz?')) {
        fetch('delete-quiz.php', {
            method: 'POST',
            body: JSON.stringify({ quizId: quizId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.text())
        .then(message => alert(message))
        .catch(error => console.error('Error:', error));
    }
}
