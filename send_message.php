<?php
// Include database configuration
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Get current user ID (from session or POST data for testing)
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($data['user_id']) ? intval($data['user_id']) : 0);

// Validate required fields
if (!isset($data['recipient_id']) || !isset($data['message']) || $current_user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$recipient_id = intval($data['recipient_id']);
$message_text = trim($data['message']);
$conversation_id = isset($data['conversation_id']) ? intval($data['conversation_id']) : 0;

// Validate message
if (empty($message_text)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

// Check if users are matched
$match_query = "SELECT 1 FROM matches 
                WHERE (user_id_1 = ? AND user_id_2 = ?) 
                OR (user_id_1 = ? AND user_id_2 = ?)";
$match_stmt = mysqli_prepare($conn, $match_query);
$user_id_min = min($current_user_id, $recipient_id);
$user_id_max = max($current_user_id, $recipient_id);
mysqli_stmt_bind_param($match_stmt, "iiii", $user_id_min, $user_id_max, $user_id_max, $user_id_min);
mysqli_stmt_execute($match_stmt);
$match_result = mysqli_stmt_get_result($match_stmt);

if (mysqli_num_rows($match_result) === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'You can only message users you have matched with'
    ]);
    exit;
}

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // If conversation_id is not provided, check if a conversation already exists
    if ($conversation_id <= 0) {
        // Sort user IDs to ensure consistent ordering
        $user_id_1 = min($current_user_id, $recipient_id);
        $user_id_2 = max($current_user_id, $recipient_id);
        
        // Check if conversation exists
        $check_query = "SELECT id FROM conversations WHERE user_id_1 = ? AND user_id_2 = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "ii", $user_id_1, $user_id_2);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if ($row = mysqli_fetch_assoc($check_result)) {
            $conversation_id = $row['id'];
        } else {
            // Create new conversation
            $create_query = "INSERT INTO conversations (user_id_1, user_id_2) VALUES (?, ?)";
            $create_stmt = mysqli_prepare($conn, $create_query);
            mysqli_stmt_bind_param($create_stmt, "ii", $user_id_1, $user_id_2);
            mysqli_stmt_execute($create_stmt);
            $conversation_id = mysqli_insert_id($conn);
        }
    } else {
        // Verify that the current user is part of this conversation
        $verify_query = "SELECT 1 FROM conversations 
                        WHERE id = ? AND (user_id_1 = ? OR user_id_2 = ?)";
        $verify_stmt = mysqli_prepare($conn, $verify_query);
        mysqli_stmt_bind_param($verify_stmt, "iii", $conversation_id, $current_user_id, $current_user_id);
        mysqli_stmt_execute($verify_stmt);
        $verify_result = mysqli_stmt_get_result($verify_stmt);
        
        if (mysqli_num_rows($verify_result) === 0) {
            throw new Exception('Unauthorized access to conversation');
        }
    }
    
    // Insert message
    $message_query = "INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)";
    $message_stmt = mysqli_prepare($conn, $message_query);
    mysqli_stmt_bind_param($message_stmt, "iis", $conversation_id, $current_user_id, $message_text);
    mysqli_stmt_execute($message_stmt);
    $message_id = mysqli_insert_id($conn);
    
    // Update conversation timestamp
    $update_query = "UPDATE conversations SET updated_at = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $conversation_id);
    mysqli_stmt_execute($update_stmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Message sent successfully',
        'data' => [
            'message_id' => $message_id,
            'conversation_id' => $conversation_id,
            'sender_id' => $current_user_id,
            'message' => $message_text,
            'time' => date('g:i a'),
            'date' => date('M j, Y'),
            'is_read' => false
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send message: ' . $e->getMessage()
    ]);
}

// Close database connection
mysqli_close($conn);
?>
