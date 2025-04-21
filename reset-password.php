<?php
require_once 'config.php';

if (isset($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    $sql = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        redirect('login.php?error=Invalid or expired reset link');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $token = $_POST['token'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $conn->begin_transaction();
        try {
            // Get user email from reset token
            $sql = "SELECT email FROM password_resets WHERE token = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $email = $stmt->get_result()->fetch_assoc()['email'];
            
            // Update password
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $email);
            $stmt->execute();
            
            // Mark token as used
            $sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $conn->commit();
            redirect('login.php?success=Password updated successfully');
        } catch (Exception $e) {
            $conn->rollback();
            $error = "An error occurred. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - JainZ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2 class="form-title">Reset Password</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
            
            <div class="form-group">
                <input type="password" name="password" placeholder="New Password" required minlength="8">
                <i class="input-icon fas fa-lock"></i>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <i class="input-icon fas fa-lock"></i>
            </div>
            
            <button type="submit" class="btn-primary">
                Reset Password <i class="fas fa-key"></i>
            </button>
        </form>
        
        <div class="footer">
            <p>Remember your password? <a href="login.php">Login here <i class="fas fa-arrow-right"></i></a></p>
        </div>
    </div>
</body>
</html>