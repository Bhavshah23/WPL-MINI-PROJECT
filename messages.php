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

// Check if a specific conversation is requested
$selected_conversation = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
$selected_user = isset($_GET['match_id']) ? intval($_GET['match_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - JainZ Dating App</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="messages.css">
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
                <a href="matching.php">Matching</a>
                <a href="messages.php" class="active">Messages</a>
            </div>
            <button class="logout-btn" id="logout-btn">Logout</button>
        </div>
        
        <div class="content-with-sidebar">
            <header class="app-header">
                <h1>Messages</h1>
            </header>
            
            <main class="messaging-container">
                <div class="conversations-panel">
                    <div class="search-container">
                        <input type="text" id="conversation-search" placeholder="Search conversations...">
                    </div>
                    <div id="conversations-list" class="conversations-list">
                        <!-- Conversations will be loaded here -->
                        <div class="loading-indicator">Loading conversations...</div>
                    </div>
                </div>
                
                <div class="messages-panel">
                    <div id="no-conversation-selected" class="no-conversation-message">
                        <p>Select a conversation to start messaging</p>
                    </div>
                    
                    <div id="conversation-container" class="conversation-container hidden">
                        <div class="conversation-header">
                            <div class="conversation-profile">
                                <img id="conversation-profile-pic" src="/placeholder.svg?height=40&width=40" alt="Profile picture">
                                <h2 id="conversation-name">User Name</h2>
                            </div>
                        </div>
                        
                        <div id="messages-list" class="messages-list">
                            <!-- Messages will be loaded here -->
                        </div>
                        
                        <div class="message-input-container">
                            <textarea id="message-input" placeholder="Type a message..."></textarea>
                            <button id="send-message-btn">Send</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Pass the current user ID to JavaScript
        window.currentUserId = <?php echo $current_user_id; ?>;
        window.selectedConversation = <?php echo $selected_conversation; ?>;
        window.selectedUser = <?php echo $selected_user; ?>;
        
        // Logout functionality
        document.getElementById('logout-btn').addEventListener('click', function() {
            // In a real app, this would send a request to a logout endpoint
            window.location.href = 'logout.php';
        });
    </script>
    <script src="messages.js"></script>
</body>
</html>
