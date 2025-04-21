<?php
// Start session if not already started
session_start();

// In a real app, you would check if the user is logged in
// and redirect to login page if not
$logged_in = false;
$current_user_id = 0;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $logged_in = true;
    $current_user_id = $_SESSION['user_id'];
} else {
    // For demo purposes, we'll use user ID 1
    // In a real app, you would redirect to login
    $current_user_id = 1;
}

// Include database configuration
require_once 'config.php';

// Get user info if logged in
$user_name = "User";
$profile_picture = "/placeholder.svg?height=200&width=200&text=User";

if ($logged_in) {
    $query = "SELECT name, profile_picture FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $current_user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $user_name = $row['name'];
        
        if (!empty($row['profile_picture'])) {
            $profile_picture = $row['profile_picture']; // No need to add 'uploads/' again
        } else {
            $profile_picture = "/placeholder.svg?height=200&width=200&text=" . urlencode($user_name);
        }
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JainZ Dating App - Find Your Match</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="matching.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="site-name">JainZ</div>
            <div class="profile-pic-container">
                <img class="profile-pic" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="sidebar-links">
                <a href="profile_page.php">Profile</a>
                <a href="matching.php" class="active">Matching</a>
                <a href="messages.php">Messages</a>
            </div>
            <button class="logout-btn" id="logout-btn">Logout</button>
        </div>
        
        <div class="content-with-sidebar">
            <header class="app-header">
                <h1>Find Your Match</h1>
            </header>
            
            <main class="match-container">
                <div id="no-profiles" class="no-profiles-message hidden">
                    <h2>No more profiles available right now!</h2>
                    <p>Check back later for new matches</p>
                </div>
                
                <div id="profile-cards-container">
                    <!-- Profile cards will be inserted here by JavaScript -->
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Pass the current user ID to JavaScript
        window.currentUserId = <?php echo $current_user_id; ?>;
        
        // Logout functionality
        document.getElementById('logout-btn').addEventListener('click', function() {
            // In a real app, this would send a request to a logout endpoint
            window.location.href = 'logout.php';
        });
    </script>
    <script src="matching.js"></script>
</body>
</html>
