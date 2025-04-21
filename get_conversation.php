<?php
// Include database configuration
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user ID (from session or query parameter for testing)
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_GET['user_id']) ? intval($_GET['user_id']) : 0);

// Get other user ID from query parameter
$other_user_id = isset($_GET['other_user_id']) ? intval($_GET['other_user_id']) : 0;

// Validate parameters
if ($current_user_id <= 0 || $other_user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Sort user IDs to ensure consistent ordering
$user_id_1 = min($current_user_id, $other_user_id);
$user_id_2 = max($current_user_id, $other_user_id);

// Add this code after validating parameters but before checking if conversation exists
// Check if users are matched
$match_query = "SELECT 1 FROM matches 
                WHERE (user_id_1 = ? AND user_id_2 = ?) 
                OR (user_id_1 = ? AND user_id_2 = ?)";
$match_stmt = mysqli_prepare($conn, $match_query);
mysqli_stmt_bind_param($match_stmt, "iiii", $user_id_1, $user_id_2, $user_id_2, $user_id_1);
mysqli_stmt_execute($match_stmt);
$match_result = mysqli_stmt_get_result($match_stmt);

if (mysqli_num_rows($match_result) === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'You can only message users you have matched with'
    ]);
    exit;
}

// Check if conversation exists
$check_query = "SELECT id FROM conversations WHERE user_id_1 = ? AND user_id_2 = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "ii", $user_id_1, $user_id_2);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

$conversation_id = 0;

if ($row = mysqli_fetch_assoc($check_result)) {
    $conversation_id = $row['id'];
} else {
    // Create new conversation
    $create_query = "INSERT INTO conversations (user_id_1, user_id_2) VALUES (?, ?)";
    $create_stmt = mysqli_prepare($conn, $create_query);
    mysqli_stmt_bind_param($create_stmt, "ii", $user_id_1, $user_id_2);
    
    if (mysqli_stmt_execute($create_stmt)) {
        $conversation_id = mysqli_insert_id($conn);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create conversation']);
        exit;
    }
}

// Get the other user's information
$user_query = "SELECT name, profile_picture FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $other_user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$other_user = mysqli_fetch_assoc($user_result);

// Format profile picture URL
$profile_picture = !empty($row['profile_picture']) 
    ? $row['profile_picture']
    : '/placeholder.svg?height=200&width=200&text=' . urlencode($row['other_user_name']);


// Return conversation info as JSON
echo json_encode([
    'success' => true, 
    'conversation' => [
        'id' => $conversation_id,
        'user_id' => $other_user_id,
        'name' => $other_user['name'],
        'profile_picture' => $profile_picture
    ]
]);

// Close database connection
mysqli_close($conn);
?>
