<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php');
    exit();
}

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the encrypted recovery key from the database
$stmt = $pdo->prepare("SELECT recovery_key FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

// Ensure the recovery key is available
if (!$user) {
    echo "Error: User not found in the database.";
    exit();
}

// Function to encrypt the email
function encryptEmail($email) {
    $encryption_key = "your-encryption-key"; // Make sure this key is secure
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')); // Generate a random IV
    $encrypted_email = openssl_encrypt($email, 'aes-256-cbc', $encryption_key, 0, $iv); // Encrypt the email

    // Combine encrypted email and IV, and encode it for storage
    return base64_encode($encrypted_email . '::' . $iv);
}

// If the form is submitted, proceed with account setup
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure account_type is set before using it
    if (isset($_POST['account_type'])) {
        $account_type = $_POST['account_type']; // Correcting the name here
    } else {
        echo "Error: Please select an account type.";
        exit();
    }

    // Optional email field
    $email = isset($_POST['email']) ? $_POST['email'] : null;

    // Encrypt the email if it is provided
    if ($email) {
        $email = encryptEmail($email);
    }

    // Use PDO to prepare and execute the update query
    $query = "UPDATE users SET type = ?, setup_complete = TRUE" . ($email ? ", email = ?" : "") . " WHERE id = ?";

    try {
        // Prepare the query using PDO
        $stmt = $pdo->prepare($query);

        // Bind parameters
        $stmt->bindParam(1, $account_type, PDO::PARAM_STR);
        if ($email) {
            $stmt->bindParam(2, $email, PDO::PARAM_STR);
            $stmt->bindParam(3, $user_id, PDO::PARAM_INT);
        } else {
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
        }

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to success page after successful setup
            header('Location: success-setup.php');
            exit();
        } else {
            echo "Error: Unable to update account.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        .account-type-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin: 5%;
        }
        .account-type-button {
            border: none;
            background: none;
            cursor: pointer;
            background-color: #f2f2f2;
            padding: 5%;
            border-radius: 20px;
            border: 2px solid #e6e6e6;
        }
        .account-type-button img {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            transition: transform 0.2s ease;
            padding: 3px;
        }
        .account-type-button img:hover {
            transform: scale(1.1);
        }
        .account-type-label {
            text-align: center;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }
        .personal {
            background-color: #cce6ff;
            border-color: #b3d9ff;
        }
        .student {
            background-color: #ccffe6;
            border-color: #b3ffd9;
        }
        .teacher {
            background-color: #ffe6cc;
            border-color: #ffd9b3;
        }
        .email-container {
            margin-top: 20px;
            border-radius: 20px;
            background-color: #f2f2f2;
            padding: 5%;
            margin: 5%;
        }
        .email-container input {
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 80%;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <h1>Setup Your Account</h1>
    <h3>What type of account do you need?</h3>

    <form method="POST" action="account_setup.php">
        <!-- Hidden input to store the selected account type -->
        <input type="hidden" id="account_type" name="account_type">

        <div class="account-type-container">
            <!-- Account type buttons. When clicked, they set the value of the hidden input -->
            <button type="button" class="account-type-button personal" onclick="setAccountType('personal')">
                <img src="images/personal.png" alt="Personal">
                <div class="account-type-label">Personal Account</div>
            </button>
            <button type="button" class="account-type-button teacher" onclick="setAccountType('teacher')">
                <img src="images/teacher.png" alt="Teacher">
                <div class="account-type-label">Teacher Account</div>
            </button>
            <button type="button" class="account-type-button student" onclick="setAccountType('student')">
                <img src="images/student.png" alt="Student">
                <div class="account-type-label">Student Account</div>
            </button>
        </div>
        
        <h3>Would you like to connect your email to this account? If yes, input it below. If not, just click complete setup.</h3>
        <div class="email-container">
            <label for="email">Email (Optional):</label>
            <input type="email" id="email" name="email" placeholder="Enter your email (optional)">
        </div>

        <button type="submit" style="padding: 10px 20px; font-size: 16px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;">
            Complete Setup
        </button>
    </form>

    <script>
        // Function to set the account type when a button is clicked
        function setAccountType(accountType) {
            document.getElementById('account_type').value = accountType;
        }
    </script>
</body>
</html>
