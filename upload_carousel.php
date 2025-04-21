<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// Get raw input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['image'])) {
    echo json_encode(["status" => "fail", "message" => "No image data"]);
    exit;
}

$base64Image = $data['image'];

// Extract base64 and file type
if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
    $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
    $type = strtolower($type[1]); // jpg, png, gif, etc.

    if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo json_encode(["status" => "fail", "message" => "Invalid image type"]);
        exit;
    }

    $base64Image = base64_decode($base64Image);

    if ($base64Image === false) {
        echo json_encode(["status" => "fail", "message" => "Base64 decode failed"]);
        exit;
    }
} else {
    echo json_encode(["status" => "fail", "message" => "Invalid base64 format"]);
    exit;
}

// Save image
$targetDir = "uploads/carousel/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$fileName = $targetDir . time() . "_" . uniqid() . "." . $type;
file_put_contents($fileName, $base64Image);

// Update DB
$sql = "UPDATE users SET carousel_images = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$carouselJson = json_encode([$fileName]);
$stmt->bind_param("si", $carouselJson, $user_id);
$stmt->execute();

echo json_encode(["status" => "success", "path" => $fileName]);
