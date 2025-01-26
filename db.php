<?php
$servername = "fdb1027.runhosting.com";
$username = "4559952_quizdb";
$password = "^DK8t^9Z4cQrbl:G"; // Default XAMPP MySQL password is usually empty
$dbname = "4559952_quizdb"; // Replace with your actual database name

// Set up DSN (Data Source Name)
$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";

// Set PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Create a PDO connection
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
