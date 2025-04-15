<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'jainz_matrimony';

// Create database connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper functions
function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(stripslashes(trim($conn->real_escape_string($data))));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// Email configuration and function
function sendEmail($to, $subject, $message) {
    // For development/testing purposes
    $_SESSION['email_sent'] = [
        'to' => $to,
        'subject' => $subject,
        'message' => $message
    ];
    
    // In production, replace with actual email sending code
    // Example using PHP mail() function:
    $headers = "From: noreply@jainz.com\r\n";
    $headers .= "Reply-To: support@jainz.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Security functions
function generateToken() {
    return bin2hex(random_bytes(32));
}

function checkCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>