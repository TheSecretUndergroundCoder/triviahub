<?php
session_start();

// Define the correct PIN
define('CORRECT_PIN', '6377');

// Handle PIN submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_pin = $_POST['pin'];

    // Sanitize user input for security
    $entered_pin = htmlspecialchars($entered_pin);

    // Check if the entered PIN is correct
    if ($entered_pin === CORRECT_PIN) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set a session variable to indicate correct PIN
        $_SESSION['pin_verified'] = true;
        
        // Redirect to the create_post page
        header('Location: create_post.php');
        exit();
    } else {
        $error_message = "Invalid PIN. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter PIN</title>
    <link rel="icon" href="icon-removebg-preview.png" type="image/x-icon">
    <style>
        /* Center the container */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9;
        }

        .pin-container {
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 300px;
        }

        .pin-display {
            font-size: 1.5em;
            letter-spacing: 0.5em;
            margin-bottom: 20px;
            background: #e9ecef;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }

        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .keypad button {
            background: #007bff;
            color: #fff;
            font-size: 1.2em;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .keypad button:hover {
            background: #0056b3;
        }

        .keypad button:active {
            background: #003f7f;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .loading {
            display: none;
            margin-top: 10px;
            color: #28a745;
        }

        .loading.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="pin-container">
        <h2>Enter PIN</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form id="pinForm" action="pin-input.php" method="POST" onsubmit="return submitPin();">
            <input type="hidden" id="pin" name="pin">
            <div class="pin-display" id="pinDisplay">****</div>
            <div class="keypad">
                <!-- Keypad buttons -->
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <button type="button" onclick="appendPin('<?php echo $i; ?>')"><?php echo $i; ?></button>
                <?php endfor; ?>
                <button type="button" onclick="clearPin()">C</button>
                <button type="button" onclick="appendPin('0')">0</button>
                <button type="button" onclick="submitPin()">OK</button>
            </div>
        </form>
        <div id="loading" class="loading">Verifying...</div>
    </div>

    <script>
        const pinInput = document.getElementById('pin');
        const pinDisplay = document.getElementById('pinDisplay');
        const loadingIndicator = document.getElementById('loading');
        let currentPin = '';

        // Append digit to the PIN input
        function appendPin(digit) {
            if (currentPin.length < 4) {
                currentPin += digit;
                updateDisplay();
            }
        }

        // Clear the PIN input
        function clearPin() {
            currentPin = '';
            updateDisplay();
        }

        // Update the PIN display with asterisks
        function updateDisplay() {
            pinDisplay.textContent = currentPin.padEnd(4, '*');
        }

        // Submit the form after checking the length
function submitPin() {
    if (currentPin.length === 4) {
        pinInput.value = currentPin;
        loadingIndicator.classList.add('show');  // Show the loading indicator

        // Delay the form submission by a small amount to give the user time to see the indicator
        setTimeout(function() {
            document.getElementById('pinForm').submit();
        }, 500); // Adjust the delay time if needed

        return false;  // Prevent immediate form submission to allow the delay
    } else {
        alert('Please enter a 4-digit PIN.');
        return false;  // Prevent form submission if PIN is invalid
    }
}

    </script>

    <script>
        // Remove '.php' from the URL without reloading the page
        if (window.location.href.endsWith("pin-input.php")) {
            var newUrl = window.location.href.replace("pin-input.php", "security");
            window.history.replaceState({}, '', newUrl);
        }
    </script>
</body>
</html>
