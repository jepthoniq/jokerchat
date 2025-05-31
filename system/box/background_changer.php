<?php
// Define directories
$backgroundDir = '../../upload/background/';
$thumbDir = '../../upload/background/thumbs/';

// Create thumbnails directory if it doesn't exist
if (!file_exists($thumbDir)) {
    mkdir($thumbDir, 0755, true);
}

// Get all files from the background directory
$files = array_diff(scandir($backgroundDir), ['.', '..']);

$result = [];
$htmlOutput = '<div class="thumbnail-gallery box_height600 pad10">'; // Start the thumbnail gallery container

foreach ($files as $file) {
    $filePath = $backgroundDir . $file;
    $thumbFile = $thumbDir . 'thumb_' . pathinfo($file, PATHINFO_FILENAME) . '.webp';

    // Skip directories and process only files
    if (is_file($filePath)) {
        // Check if thumbnail already exists
        if (!doesThumbnailExist($thumbFile)) {
            // Generate thumbnail
            $success = generateThumbnail($filePath, $thumbFile);
        } else {
            $success = true; // Thumbnail already exists
        }

        $result[] = [
            'file' => [
                'path' => $filePath,
                'url' => str_replace('../../', '/', $filePath) // Adjust URL path
            ],
            'thumb' => [
                'path' => $thumbFile,
                'url' => str_replace('../../', '/', $thumbFile) // Adjust URL path
            ],
            'success' => $success
        ];

        // Generate HTML output for each file
        
        $thumb =  $result[count($result) - 1]['thumb']['url']; 
        $back =  $result[count($result) - 1]['file']['url'];
        $htmlOutput .= '
         <div class="thumbnail-item">
            <label>
                <input type="radio" name="background" value=".' . $back . '" class="radio-input" onclick="change_background($(this))">
                <span class="checkmark">
                    <img src=".' . $thumb. '" alt="Thumbnail Image">
                </span>
            </label>
        </div>';
    }
}

$htmlOutput .= '</div>'; // End the thumbnail gallery container

// Function to check if thumbnail exists
function doesThumbnailExist($thumbFile) {
    return file_exists($thumbFile);
}

// Function to generate thumbnail
function generateThumbnail($sourceFile, $destFile) {
    $success = false;

    // Determine the file type and create image resource accordingly
    $imageType = exif_imagetype($sourceFile);
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($sourceFile);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($sourceFile);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($sourceFile);
            break;
        default:
            return $success; // Unsupported file type
    }

    if ($image) {
        $width = imagesx($image);
        $height = imagesy($image);

        // Define thumbnail dimensions
        $thumbWidth = 225; // Desired thumbnail width
        $thumbHeight = 225; // Desired thumbnail height

        // Create thumbnail with a transparent background
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127); // Transparent background
        imagefill($thumb, 0, 0, $transparent);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);

        // Calculate the scaling and cropping dimensions
        $srcAspectRatio = $width / $height;
        $thumbAspectRatio = $thumbWidth / $thumbHeight;

        if ($srcAspectRatio > $thumbAspectRatio) {
            // Source image is wider than the thumbnail
            $newHeight = $thumbWidth / $srcAspectRatio;
            $yOffset = ($thumbHeight - $newHeight) / 2;
            imagecopyresampled($thumb, $image, 0, $yOffset, 0, 0, $thumbWidth, $newHeight, $width, $height);
        } else {
            // Source image is taller than the thumbnail or same aspect ratio
            $newWidth = $thumbHeight * $srcAspectRatio;
            $xOffset = ($thumbWidth - $newWidth) / 2;
            imagecopyresampled($thumb, $image, $xOffset, 0, 0, 0, $newWidth, $thumbHeight, $width, $height);
        }

        // Save thumbnail as .webp
        if (imagewebp($thumb, $destFile)) {
            $success = true;
        }
        imagedestroy($image);
        imagedestroy($thumb);
    }
    
    return $success;
}

// Output JSON result
header('Content-Type: application/json');
echo json_encode([
    'result' => $result,
    'html' => $htmlOutput
]);
?>
