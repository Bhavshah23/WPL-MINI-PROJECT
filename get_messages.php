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

// Get conversation ID from query parameter
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

// Validate parameters
if ($current_user_id <= 0 || $conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Verify that the current user is part of this conversation
$verify_query = "SELECT 1 FROM conversations 
                WHERE id = ? AND (user_id_1 = ? OR user_id_2 = ?)";
$verify_stmt = mysqli_prepare($conn, $verify_query);
mysqli_stmt_bind_param($verify_stmt, "iii", $conversation_id, $current_user_id, $current_user_id);
mysqli_stmt_execute($verify_stmt);
$verify_result = mysqli_stmt_get_result($verify_stmt);

if (mysqli_num_rows($verify_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access to conversation']);
    exit;
}

// Get the other user's information
$user_query = "SELECT 
                CASE 
                    WHEN c.user_id_1 = ? THEN c.user_id_2
                    ELSE c.user_id_1
                END AS other_user_id,
                u.name AS other_user_name,
                u.profile_picture /* Profile picture is stored directly in the users table */
              FROM conversations c
              JOIN users u ON (c.user_id_1 = ? AND c.user_id_2 = u.id) OR (c.user_id_2 = ? AND c.user_id_1 = u.id)
              WHERE c.id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "iiii", $current_user_id, $current_user_id, $current_user_id, $conversation_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$other_user = mysqli_fetch_assoc($user_result);

// Query to get messages for this conversation
$query = "SELECT 
            m.id,
            m.sender_id,
            m.message_text,
            m.is_read,
            m.created_at
          FROM messages m
          WHERE m.conversation_id = ?
          ORDER BY m.created_at ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $conversation_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        'id' => $row['id'],
        'sender_id' => $row['sender_id'],
        'is_self' => $row['sender_id'] == $current_user_id,
        'message' => $row['message_text'],
        'is_read' => (bool)$row['is_read'],
        'time' => date('g:i a', strtotime($row['created_at'])),
        'date' => date('M j, Y', strtotime($row['created_at']))
    ];
}

// Mark all messages from the other user as read
$mark_read_query = "UPDATE messages 
                   SET is_read = 1 
                   WHERE conversation_id = ? 
                   AND sender_id = ? 
                   AND is_read = 0";
$mark_read_stmt = mysqli_prepare($conn, $mark_read_query);
mysqli_stmt_bind_param($mark_read_stmt, "ii", $conversation_id, $other_user['other_user_id']);
mysqli_stmt_execute($mark_read_stmt);

// Format profile picture URL - profile_picture is stored in the users table
$profile_picture = !empty($other_user['profile_picture']) 
    ? $other_user['profile_picture'] 
    : '/placeholder.svg?height=200&width=200&text=' . urlencode($other_user['other_user_name']);


// Return messages and conversation info as JSON
echo json_encode([
    'success' => true, 
    'messages' => $messages,
    'conversation' => [
        'id' => $conversation_id,
        'user_id' => $other_user['other_user_id'],
        'name' => $other_user['other_user_name'],
        'profile_picture' => $profile_picture
    ]
]);

// Close database connection
mysqli_close($conn);
?>
