<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user input to prevent XSS and other vulnerabilities
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    
    // Set the recipient email address
    $to = "finnscoggins2@gmail.com";  // Replace with your email address
    
    // Set email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Construct the email body
    $body = "<strong>Name:</strong> $name<br>
             <strong>Email:</strong> $email<br>
             <strong>Subject:</strong> $subject<br>
             <strong>Message:</strong><br>$message";
    
    // Send the email
    if (mail($to, $subject, $body, $headers)) {
        // Redirect to a success page or show a success message
        echo "Thank you for contacting us! We will get back to you shortly.";
    } else {
        // Handle failure to send email
        echo "There was an error sending your message. Please try again.";
    }
}
?>