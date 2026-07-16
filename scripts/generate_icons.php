<?php
/**
 * MyWisata - Icon Generation Script
 * 
 * Generates placeholder icons for development
 * Run: php scripts/generate_icons.php
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

// Define icon sizes
$iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];
$iconDir = __DIR__ . '/../public/assets/icons';

// Create directory if not exists
if (!is_dir($iconDir)) {
    mkdir($iconDir, 0755, true);
}

// Generate placeholder icons
foreach ($iconSizes as $size) {
    $filename = $iconDir . '/icon-' . $size . 'x' . $size . '.png';
    generatePlaceholderIcon($filename, $size, '#3b82f6', 'MW');
}

// Generate maskable icons
foreach ([192, 512] as $size) {
    $filename = $iconDir . '/maskable-icon-' . $size . 'x' . $size . '.png';
    generatePlaceholderIcon($filename, $size, '#3b82f6', 'MW', true);
}

// Generate shortcut icons
$shortcuts = ['search', 'booking', 'favorites', 'ai'];
foreach ($shortcuts as $shortcut) {
    $filename = $iconDir . '/shortcut-' . $shortcut . '.png';
    generatePlaceholderIcon($filename, 96, '#8b5cf6', strtoupper(substr($shortcut, 0, 1)));
}

// Generate badge
$badgeFile = $iconDir . '/badge-72x72.png';
generatePlaceholderIcon($badgeFile, 72, '#ec4899', '★');

echo "Placeholder icons generated successfully!\n";
echo "Location: " . $iconDir . "\n";
echo "\nNote: These are placeholder icons for development.\n";
echo "For production, replace with professionally designed icons.\n";

/**
 * Generate placeholder icon
 * 
 * @param string $filename Output filename
 * @param int $size Icon size
 * @param string $backgroundColor Background color
 * @param string $text Text to display
 * @param bool $maskable Whether icon is maskable
 */
function generatePlaceholderIcon($filename, $size, $backgroundColor, $text, $maskable = false) {
    $image = imagecreatetruecolor($size, $size);
    
    // Parse background color
    $bgColor = hexToRgb($backgroundColor);
    $bg = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
    
    // Fill background
    imagefill($image, 0, 0, $bg);
    
    // If maskable, create safe zone
    if ($maskable) {
        $safeZoneSize = $size * 0.7;
        $safeZoneX = ($size - $safeZoneSize) / 2;
        $safeZoneY = ($size - $safeZoneSize) / 2;
        
        $safeZoneColor = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, $safeZoneX, $safeZoneY, $safeZoneX + $safeZoneSize, $safeZoneY + $safeZoneSize, $safeZoneColor);
        
        // Add text in safe zone
        $textColor = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        $fontSize = $size * 0.3;
        $angle = 0;
        $x = $size / 2;
        $y = $size / 2 + $fontSize / 3;
        
        imagettftext($image, $fontSize, $angle, $x, $y, $textColor, __DIR__ . '/arial.ttf', $text);
    } else {
        // Add text normally
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $fontSize = $size * 0.4;
        $angle = 0;
        $x = $size / 2;
        $y = $size / 2 + $fontSize / 3;
        
        // Try to use TTF font, fallback to default
        $fontPath = __DIR__ . '/arial.ttf';
        if (file_exists($fontPath)) {
            imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontPath, $text);
        } else {
            // Use built-in font as fallback
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($text);
            $textHeight = imagefontheight($font);
            imagestring($image, $font, $x - $textWidth / 2, $y - $textHeight / 2, $text, $textColor);
        }
    }
    
    // Save as PNG
    imagepng($image, $filename);
    imagedestroy($image);
}

/**
 * Convert hex color to RGB
 * 
 * @param string $hex Hex color code
 * @return array
 */
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    return ['r' => $r, 'g' => $g, 'b' => $b];
}
