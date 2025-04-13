<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'dating_app';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// Simulated email sending
function sendEmail($email, $subject, $message) {
    $_SESSION['reset_link'] = [
        'email' => $email,
        'link' => strip_tags(explode(' ', $message)[3])
    ];
    return true;
}
?>