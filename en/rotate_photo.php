<?php

// Function to rotate an image by the specified degrees and save it back
function rotateEcobrickPhoto($sourcePath, $rotationDegrees, $targetPath = null) {
    // Log the start of the process
    error_log("Starting photo rotation for file: $sourcePath. Rotation degrees: $rotationDegrees");

    // Get image details
    list($width, $height, $type) = getimagesize($sourcePath);
    if (!$width || !$height || !$type) {
        error_log("Failed to get image details for file: $sourcePath");
        return false; // Exit if unable to get image details
    }

    // Load the source image based on its type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            error_log("Unsupported image type for file: $sourcePath");
            return false; // Unsupported image type
    }

    if (!$sourceImage) {
        error_log("Failed to create image resource for file: $sourcePath");
        return false; // Failed to load the image
    }

    // Rotate the image using the given degrees
    $rotatedImage = imagerotate($sourceImage, -$rotationDegrees, 0); // Negative for clockwise rotation
    if (!$rotatedImage) {
        error_log("Failed to rotate image for file: $sourcePath");
        return false; // Exit if rotation fails
    }

    // If no target path is provided, overwrite the original
    if ($targetPath === null) {
        $targetPath = $sourcePath;
    }

    // Save the rotated image back in its original format
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($rotatedImage, $targetPath, 90); // 90 is JPEG quality
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($rotatedImage, $targetPath);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($rotatedImage, $targetPath);
            break;
    }

    if ($success) {
        error_log("Image successfully rotated and saved to: $targetPath");
    } else {
        error_log("Failed to save rotated image to: $targetPath");
        return false; // Exit if saving fails
    }

    // Free memory
    imagedestroy($sourceImage);
    imagedestroy($rotatedImage);

    return true; // Indicate success
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoUrl = $_POST['photo_url'] ?? '';
    $thumbUrl = $_POST['thumb_url'] ?? ''; // Get the thumbnail URL from POST data
    $rotationDegrees = $_POST['rotation'] ?? 0;

    if (!empty($photoUrl) && $rotationDegrees) {
        // Try rotating the main photo using the server path
        $mainPhotoSuccess = rotateEcobrickPhoto($photoUrl, $rotationDegrees);
        $thumbPhotoSuccess = true; // Default success flag for thumbnail

        // If there's a thumbnail URL, rotate it as well
        if (!empty($thumbUrl)) {
            $thumbPhotoSuccess = rotateEcobrickPhoto($thumbUrl, $rotationDegrees);
        }

        if ($mainPhotoSuccess && $thumbPhotoSuccess) {
            echo "Image and thumbnail rotated successfully.";
        } else {
            echo "Failed to rotate the image or thumbnail.";
        }
    } else {
        echo "Invalid request data.";
    }
} else {
    echo "Invalid request method.";
}
?>
