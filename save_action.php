<?php
// Include database configuration
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['user_id']) || !isset($data['profile_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$user_id = intval($data['user_id']);
$profile_id = intval($data['profile_id']);
$action = $data['action']; // 'like' or 'dislike'

// Validate action
if ($action !== 'like' && $action !== 'dislike') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Save the action to the database
$query = "INSERT INTO user_actions (user_id, profile_id, action, created_at) 
          VALUES (?, ?, ?, NOW())
          ON DUPLICATE KEY UPDATE action = ?, created_at = NOW()";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iiss", $user_id, $profile_id, $action, $action);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    // Check if it's a match (both users liked each other)
    $is_match = false;
    
    if ($action === 'like') {
        $match_query = "SELECT 1 FROM user_actions 
                        WHERE user_id = ? AND profile_id = ? AND action = 'like'";
        $match_stmt = mysqli_prepare($conn, $match_query);
        mysqli_stmt_bind_param($match_stmt, "ii", $profile_id, $user_id);
        mysqli_stmt_execute($match_stmt);
        $match_result = mysqli_stmt_get_result($match_stmt);
        
        if (mysqli_num_rows($match_result) > 0) {
            $is_match = true;
            
            // Create a match record
            $create_match_query = "INSERT INTO matches (user_id_1, user_id_2, matched_at)
                                  VALUES (?, ?, NOW())
                                  ON DUPLICATE KEY UPDATE matched_at = NOW()";
            $create_match_stmt = mysqli_prepare($conn, $create_match_query);
            
            // Ensure smaller ID is always first for consistency
            $user_id_1 = min($user_id, $profile_id);
            $user_id_2 = max($user_id, $profile_id);
            
            mysqli_stmt_bind_param($create_match_stmt, "ii", $user_id_1, $user_id_2);
            mysqli_stmt_execute($create_match_stmt);

            // Add this code to automatically create a conversation when users match
            // Check if a conversation already exists
            $check_conv_query = "SELECT id FROM conversations WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)";
            $check_conv_stmt = mysqli_prepare($conn, $check_conv_query);
            mysqli_stmt_bind_param($check_conv_stmt, "iiii", $user_id_1, $user_id_2, $user_id_2, $user_id_1);
            mysqli_stmt_execute($check_conv_stmt);
            $check_conv_result = mysqli_stmt_get_result($check_conv_stmt);
            
            if (mysqli_num_rows($check_conv_result) == 0) {
                // Create a new conversation
                $create_conv_query = "INSERT INTO conversations (user_id_1, user_id_2, created_at) VALUES (?, ?, NOW())";
                $create_conv_stmt = mysqli_prepare($conn, $create_conv_query);
                mysqli_stmt_bind_param($create_conv_stmt, "ii", $user_id_1, $user_id_2);
                mysqli_stmt_execute($create_conv_stmt);
                
                // Get the new conversation ID
                $conversation_id = mysqli_insert_id($conn);
                
                // Add a system message to the conversation
                $system_message = "You are now matched! Say hello to start the conversation.";
                $add_msg_query = "INSERT INTO messages (conversation_id, sender_id, message_text, is_read, created_at) VALUES (?, 0, ?, 0, NOW())";
                $add_msg_stmt = mysqli_prepare($conn, $add_msg_query);
                mysqli_stmt_bind_param($add_msg_stmt, "is", $conversation_id, $system_message);
                mysqli_stmt_execute($add_msg_stmt);
            }
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Action saved successfully',
        'is_match' => $is_match
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save action: ' . mysqli_error($conn)
    ]);
}

// Close database connection
mysqli_close($conn);
?>
