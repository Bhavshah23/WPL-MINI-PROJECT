<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $user = $conn->query("SELECT * FROM users WHERE email='$email'")->fetch_assoc();
    
    if ($user) {
        $reset_token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $conn->query("UPDATE users SET reset_token='$reset_token', reset_expires='$expires' WHERE id={$user['id']}");
        
        $reset_link = "http://localhost/reset-password.php?token=$reset_token";
        sendEmail($email, "Password Reset", "Reset link: $reset_link");
        
        $_SESSION['message'] = "Password reset link generated!";
        $_SESSION['test_reset_link'] = $reset_link; // Store for display
        redirect('forgot-password.php');
    } else {
        $_SESSION['error'] = "Email not found!";
        redirect('forgot-password.php');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2 class="form-title">Reset Password</h2>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['message'] ?>
                <?php if(isset($_SESSION['test_reset_link'])): ?>
                    <div class="test-link">
                        Test Reset Link: 
                        <a href="<?= $_SESSION['test_reset_link'] ?>">
                            <?= $_SESSION['test_reset_link'] ?>
                        </a>
                    </div>
                    <?php unset($_SESSION['test_reset_link']); ?>
                <?php endif; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit">Generate Reset Link</button>
        </form>
        <p class="login-link"><a href="login.php">Remember your password? Login</a></p>
    </div>
</body>
</html>