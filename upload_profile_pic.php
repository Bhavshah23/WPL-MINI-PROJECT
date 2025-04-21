<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "fail", "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if a file is uploaded
if (isset($_FILES['profile_pic'])) {
    // Set the target directory and filename
    $targetDir = "uploads/profile_pics/";
    
    // Ensure the directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Create directory with permissions if it doesn't exist
    }
    
    // Get the file name and set the target file path
    $fileName = basename($_FILES["profile_pic"]["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;
    
    // Check file size (optional)
    if ($_FILES["profile_pic"]["size"] > 5000000) { // 5MB max size
        echo json_encode(["status" => "fail", "message" => "File is too large"]);
        exit();
    }
    
    // Attempt to move the uploaded file
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
        // Update user profile with new picture path
        $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            echo json_encode(["status" => "fail", "message" => "Database error"]);
            exit();
        }

        $stmt->bind_param("si", $targetFilePath, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "path" => $targetFilePath]);
        } else {
            echo json_encode(["status" => "fail", "message" => "Failed to update database"]);
        }
    } else {
        // Handle the file move failure
        echo json_encode(["status" => "fail", "message" => "Failed to move uploaded file"]);
    }
} else {
    echo json_encode(["status" => "fail", "message" => "No file uploaded"]);
}
?>

