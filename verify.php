<?php
require_once 'config.php';

if (isset($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    $sql = "UPDATE users SET email_verified = 1, status = 'active' 
            WHERE verification_token = ? AND email_verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        redirect('login.php?success=Email verified successfully. You can now login.');
    } else {
        redirect('login.php?error=Invalid verification token or email already verified.');
    }
} else {
    redirect('login.php');
}
?>