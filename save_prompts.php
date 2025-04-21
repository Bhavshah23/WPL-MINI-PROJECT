<?php
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !is_array($data)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit;
    }

    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    // List only the expected keys (add more if needed)
    $fields = [
        'education', 'profession', 'fun_fact', 'meaning_of_jain', 
        'looking_for', 'diet', 'babies', 'toxic_trait', 'vacation_spots'
    ];

    // Build dynamic query
    $placeholders = [];
    $types = "";
    $values = [];

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $placeholders[] = "$field = ?";
            $types .= "s";
            $values[] = sanitizeInput($data[$field]);
        }
    }

    // Add user_id to the binding
    $types .= "i";
    $values[] = $user_id;

    $sql = "UPDATE users SET " . implode(", ", $placeholders) . " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'DB update failed']);
    }

    $stmt->close();
}
?>
