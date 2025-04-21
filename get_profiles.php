<?php
// Include database configuration
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get current user ID (you would get this from session in a real app)
$current_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

// Query to get all other user profiles (excluding current user)
$query = "SELECT id, name, birth_date, gender, fun_fact, 
                 meaning_of_jain, looking_for, diet, babies, 
                 toxic_trait, vacation_spots, profile_picture, 
                 carousel_images
          FROM users 
          WHERE id != ?
          LIMIT 10";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$profiles = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Calculate age from birth_date
    $birth_date = new DateTime($row['birth_date']);
    $today = new DateTime();
    $age = $birth_date->diff($today)->y;

    // Get profile images
    $images = [];

    // Add profile picture as first image
    if (!empty($row['profile_picture'])) {
        $images[] = $row['profile_picture']; // already has 'uploads/' path
    } else {
        $images[] = '/placeholder.svg?height=400&width=400&text=' . urlencode($row['name']);
    }

    // Decode JSON for carousel images
    $carousel_json = $row['carousel_images'] ?? '';
    $carousel_images = json_decode($carousel_json, true);

    if (is_array($carousel_images)) {
        foreach ($carousel_images as $img_path) {
            $images[] = $img_path;
        }
    }

    // Add second placeholder if only one image
    if (count($images) < 2) {
        $images[] = '/placeholder.svg?height=400&width=400&text=' . urlencode($row['name'] . '+Photo');
    }

    // Collect up to 4 prompt answers from fields
    $prompt_fields = [
        'meaning_of_jain'   => 'What Jainism means to me',
        'looking_for'       => 'Looking for',
        'diet'              => 'Diet',
        'fun_fact'          => 'Fun fact',
        'babies'            => 'Thoughts on having children',
        'toxic_trait'       => 'My toxic trait',
        'vacation_spots'    => 'Favorite vacation spots'
    ];

    $prompts = [];

    foreach ($prompt_fields as $field => $question) {
        if (!empty($row[$field])) {
            $prompts[] = [
                'question' => $question,
                'answer' => $row[$field]
            ];
        }
        if (count($prompts) === 4) break; // limit to 4
    }

    // Default prompts if none exist
    if (empty($prompts)) {
        $prompts[] = ['question' => 'About me', 'answer' => 'I am interested in Jain values and traditions.'];
        $prompts[] = ['question' => 'Looking for', 'answer' => 'Someone who shares Jain values.'];
    }

    // Final profile array
    $profiles[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'age' => $age,
        'gender' => $row['gender'],
        'images' => $images,
        'prompts' => $prompts
    ];
}

// Return as JSON
echo json_encode($profiles);
mysqli_close($conn);
?>
