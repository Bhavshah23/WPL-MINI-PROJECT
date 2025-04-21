<?php
require_once 'config.php'; // ✅ Includes session_start and DB connection

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$query = "SELECT name, email, gender, birth_date FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo json_encode($user);
} else {
    echo json_encode(["error" => "User not found"]);
}
?>
