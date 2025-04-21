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

// Validate user ID
if ($current_user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Replace the main query in get_conversations.php with this updated version that checks for matches
$query = "SELECT 
            c.id AS conversation_id,
            CASE 
                WHEN c.user_id_1 = ? THEN c.user_id_2
                ELSE c.user_id_1
            END AS other_user_id,
            u.name AS other_user_name,
            u.profile_picture,
            c.updated_at,
            (
                SELECT m.message_text 
                FROM messages m 
                WHERE m.conversation_id = c.id 
                ORDER BY m.created_at DESC 
                LIMIT 1
            ) AS last_message,
            (
                SELECT m.created_at 
                FROM messages m 
                WHERE m.conversation_id = c.id 
                ORDER BY m.created_at DESC 
                LIMIT 1
            ) AS last_message_time,
            (
                SELECT COUNT(*) 
                FROM messages m 
                WHERE m.conversation_id = c.id 
                AND m.sender_id != ? 
                AND m.is_read = 0
            ) AS unread_count,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM matches m 
                    WHERE (m.user_id_1 = ? AND m.user_id_2 = u.id) 
                    OR (m.user_id_1 = u.id AND m.user_id_2 = ?)
                ) THEN 1
                ELSE 0
            END AS is_matched
          FROM conversations c
          JOIN users u ON (c.user_id_1 = ? AND c.user_id_2 = u.id) OR (c.user_id_2 = ? AND c.user_id_1 = u.id)
          WHERE c.user_id_1 = ? OR c.user_id_2 = ?
          ORDER BY is_matched DESC, c.updated_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iiiiiiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$conversations = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Format profile picture URL
    $profile_picture = !empty($row['profile_picture']) 
    ? $row['profile_picture']
    : '/placeholder.svg?height=200&width=200&text=' . urlencode($row['other_user_name']);

    
    // Format last message time
    $last_message_time = !empty($row['last_message_time']) 
        ? date('M j, g:i a', strtotime($row['last_message_time'])) 
        : date('M j, g:i a', strtotime($row['updated_at']));
    
    $conversations[] = [
        'id' => $row['conversation_id'],
        'user_id' => $row['other_user_id'],
        'name' => $row['other_user_name'],
        'profile_picture' => $profile_picture,
        'last_message' => $row['last_message'] ?? 'Start a conversation',
        'last_message_time' => $last_message_time,
        'unread_count' => intval($row['unread_count']),
        'is_matched' => intval($row['is_matched'])
    ];
}

// Return conversations as JSON
echo json_encode(['success' => true, 'conversations' => $conversations]);

// Close database connection
mysqli_close($conn);
?>
