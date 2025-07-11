<?php
require_once '../earthenAuth_helper.php';
startSecureSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';
require_once '../scripts/photo-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$training_id = intval($_POST['training_id'] ?? 0);
if ($training_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid training ID']);
    exit();
}

$upload_dir = '../trainings/photos/';
$thumbnail_dir = '../trainings/tmbs/';

$db_fields = [];
$db_values = [];
$db_types = '';

$response = ['success' => true];

for ($i = 1; $i <= 3; $i++) {
    $field = "feature_photo{$i}_main";
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $new_name = "feature-{$training_id}-{$i}.webp";
        $target = $upload_dir . $new_name;
        if (resizeAndConvertToWebP($_FILES[$field]['tmp_name'], $target, 1000, 88)) {
            $thumbPath = $thumbnail_dir . $new_name;
            createTrainingThumbnail($target, $thumbPath, 250, 250, 77);
            $db_fields[] = $field;
            $db_values[] = $target;
            $db_types .= 's';

            $thumb_field = "feature_photo{$i}_tmb";
            $db_fields[] = $thumb_field;
            $db_values[] = $thumbPath;
            $db_types .= 's';

            $response[$field] = $target;
            $response[$thumb_field] = $thumbPath;
        } else {
            echo json_encode(['success' => false, 'error' => "Error processing image {$i}."]);
            exit();
        }
    }
}

if (!empty($db_fields)) {
    $set_clause = implode(',', array_map(fn($f) => "$f=?", $db_fields));
    $db_values[] = $training_id;
    $db_types .= 'i';
    $stmt = $gobrik_conn->prepare("UPDATE tb_trainings SET {$set_clause} WHERE training_id=?");
    if ($stmt) {
        $stmt->bind_param($db_types, ...$db_values);
        $stmt->execute();
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit();
    }
}

echo json_encode($response);
