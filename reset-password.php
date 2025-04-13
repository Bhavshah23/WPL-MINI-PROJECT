<?php
require 'config.php';

$token = $_GET['token'] ?? '';
$user = $conn->query("SELECT * FROM users WHERE reset_token='$token' AND reset_expires > NOW()")->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "Invalid or expired token!";
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password='$new_password', reset_token=NULL, reset_expires=NULL WHERE id={$user['id']}");
    $_SESSION['message'] = "Password updated successfully!";
    redirect('login.php');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Set New Password</h2>
        <form method="POST">
            <div class="form-group">
                <input type="password" name="password" placeholder="New Password" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>