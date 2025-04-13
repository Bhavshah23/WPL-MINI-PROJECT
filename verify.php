<?php
require 'config.php';

if (isset($_GET['code'])) {
    $code = sanitizeInput($_GET['code']);
    $conn->query("UPDATE users SET verified=1 WHERE verification_code='$code'");
    $_SESSION['message'] = "Email verified successfully! You can now login.";
    redirect('login.php');
}
?>