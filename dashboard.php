<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

require_once 'header.php';
?>

<div class="dashboard-container">
    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
    <div class="profile-section">
        <h3>Your Profile</h3>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Name: <?php echo htmlspecialchars($user['name']); ?></p>
    </div>
    
    <div class="actions">
        <a href="edit-profile.php" class="btn-primary">Edit Profile</a>
        <a href="logout.php" class="btn-secondary">Logout</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>