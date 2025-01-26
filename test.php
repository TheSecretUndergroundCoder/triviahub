<?php
include 'db.php'; // Include your database connection

// Function to encrypt the email
function encryptEmail($email) {
    $encryption_key = "your-encryption-key"; // Ensure this key is secure
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')); // Generate a random IV
    $encrypted_email = openssl_encrypt($email, 'aes-256-cbc', $encryption_key, 0, $iv); // Encrypt the email

    // Combine encrypted email and IV, and encode it for storage
    return base64_encode($encrypted_email . '::' . $iv);
}

// Button click to trigger email encryption
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch all users with emails
    $stmt = $pdo->query("SELECT id, email FROM users WHERE email IS NOT NULL");
    $users = $stmt->fetchAll();

    foreach ($users as $user) {
        // Encrypt the email
        $encrypted_email = encryptEmail($user['email']);

        // Update the user's email with the encrypted version
        $updateStmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $updateStmt->execute([$encrypted_email, $user['id']]);
    }

    echo "All emails have been encrypted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt Emails</title>
</head>
<body>
    <h1>Encrypt All User Emails</h1>
    <form method="POST">
        <button type="submit" style="padding: 10px 20px; font-size: 16px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;">
            Encrypt Emails
        </button>
    </form>
</body>
</html>
