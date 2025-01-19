<?php
// Disable error reporting and logging
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Function to safely get parameters from GET request
function getParam($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

// Get parameters from the request
$size = getParam('size', '300x150');
$title = getParam('title', 'Skytup');
$bg_color = getParam('bg_color','000');
$bg_image_url = getParam('bg_image');
$blur = max(0, intval(getParam('blur', 0)));
$text_color = getParam('text_color', 'FFFFFF');
$font_size = max(1, intval(getParam('font_size', 20)));
$rotation = intval(getParam('rotation', 0));
$opacity = min(100, max(0, intval(getParam('opacity', 100))));
$filter = getParam('filter');

// Parse size
list($width, $height) = explode('x', $size);
$width = min(2000, max(1, intval($width)));
$height = min(2000, max(1, intval($height)));

// Create image
$image = @imagecreatetruecolor($width, $height);

if (!$image) {
    exit("Failed to create image resource.");
}

// Enable alpha blending
imagealphablending($image, true);
imagesavealpha($image, true);

// Set background
if ($bg_image_url) {
    $bg_image = @imagecreatefromstring(@file_get_contents($bg_image_url));
    if ($bg_image) {
        imagecopyresampled($image, $bg_image, 0, 0, 0, 0, $width, $height, imagesx($bg_image), imagesy($bg_image));
        imagedestroy($bg_image);
    } else {
        $background_color = imagecolorallocate($image, 240, 240, 240);
        imagefill($image, 0, 0, $background_color);
    }
} elseif ($bg_color) {
    $rgb = sscanf($bg_color, "%02x%02x%02x");
    $background_color = imagecolorallocatealpha($image, $rgb[0], $rgb[1], $rgb[2], (int)(127 * (100 - $opacity) / 100));
    imagefill($image, 0, 0, $background_color);
} else {
    $background_color = imagecolorallocatealpha($image, 240, 240, 240, (int)(127 * (100 - $opacity) / 100));
    imagefill($image, 0, 0, $background_color);
}

// Apply blur if requested
if ($blur > 0) {
    for ($i = 0; $i < $blur; $i++) {
        @imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
    }
}

// Apply additional filters
if ($filter) {
    switch ($filter) {
        case 'grayscale':
            @imagefilter($image, IMG_FILTER_GRAYSCALE);
            break;
        case 'sepia':
            @imagefilter($image, IMG_FILTER_GRAYSCALE);
            @imagefilter($image, IMG_FILTER_COLORIZE, 100, 50, 0);
            break;
        case 'negative':
            @imagefilter($image, IMG_FILTER_NEGATE);
            break;
        case 'edge':
            @imagefilter($image, IMG_FILTER_EDGEDETECT);
            break;
        case 'emboss':
            @imagefilter($image, IMG_FILTER_EMBOSS);
            break;
    }
}

// Prepare text color
$rgb = sscanf($text_color, "%02x%02x%02x");
$text_color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);

// Add text to image
$font = __DIR__ . '/arial.ttf'; // Make sure this font file exists
if (!file_exists($font)) {
    $font = 5;
    $lines = explode("\n", wordwrap($title, (int)($width / (imagefontwidth($font) * 1.2)), "\n"));
    $y = (int)(($height - count($lines) * imagefontheight($font)) / 2);
    foreach ($lines as $line) {
        $text_width = imagefontwidth($font) * strlen($line);
        $x = (int)(($width - $text_width) / 2);
        imagestring($image, $font, $x, $y, $line, $text_color);
        $y += imagefontheight($font);
    }
} else {
    $max_width = $width - 20;
    $lines = [];
    $words = explode(' ', $title);
    $line = '';

    foreach ($words as $word) {
        $test_line = $line . ' ' . $word;
        $bbox = @imagettfbbox($font_size, $rotation, $font, $test_line);
        if ($bbox) {
            $line_width = $bbox[2] - $bbox[0];

            if ($line_width > $max_width) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $test_line;
            }
        }
    }
    $lines[] = $line;

    $total_height = count($lines) * ($font_size + 5);
    $y = (int)(($height - $total_height) / 2) + $font_size;

    foreach ($lines as $line) {
        $bbox = @imagettfbbox($font_size, $rotation, $font, $line);
        if ($bbox) {
            $line_width = $bbox[2] - $bbox[0];
            $x = (int)(($width - $line_width) / 2);
            @imagettftext($image, $font_size, $rotation, $x, $y, $text_color, $font, $line);
        }
        $y += $font_size + 5;
    }
}

// Ensure no output has been sent before headers
if (!headers_sent()) {
    // Set the content type header
    header('Content-Type: image/png');
}

// Output image
imagepng($image);

// Free memory
imagedestroy($image);
?>