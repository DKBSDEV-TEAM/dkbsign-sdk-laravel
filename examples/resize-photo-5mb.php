<?php

declare(strict_types=1);

/**
 * Resize/compress a photo to fit under 5 MB (common admin requirement).
 *
 * Usage:
 *   php examples/resize-photo-5mb.php "C:\path\to\input.png" "C:\path\to\output.jpg"
 */

$maxBytes = 5 * 1024 * 1024;
$input = $argv[1] ?? null;
$output = $argv[2] ?? (getcwd().DIRECTORY_SEPARATOR.'photo-5mb.jpg');

if ($input === null || ! is_readable($input)) {
    fwrite(STDERR, "Usage: php examples/resize-photo-5mb.php INPUT_IMAGE [OUTPUT.jpg]\n");
    exit(1);
}

if (! extension_loaded('gd')) {
    fwrite(STDERR, "Extension PHP GD requise.\n");
    exit(1);
}

$info = @getimagesize($input);
if ($info === false) {
    fwrite(STDERR, "Fichier image non reconnu.\n");
    exit(1);
}

[$width, $height, $type] = $info;

$source = match ($type) {
    IMAGETYPE_JPEG => imagecreatefromjpeg($input),
    IMAGETYPE_PNG => imagecreatefrompng($input),
    IMAGETYPE_WEBP => imagecreatefromwebp($input),
    default => null,
};

if ($source === false || $source === null) {
    fwrite(STDERR, "Format non supporté (utilise JPG, PNG ou WEBP).\n");
    exit(1);
}

// White background for JPEG (ID photos)
$canvas = imagecreatetruecolor($width, $height);
$white = imagecolorallocate($canvas, 255, 255, 255);
imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);
imagedestroy($source);

$quality = 92;
$scale = 1.0;
$bestPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'photo-5mb-tmp.jpg';

do {
    $w = max(1, (int) round($width * $scale));
    $h = max(1, (int) round($height * $scale));
    $resized = imagecreatetruecolor($w, $h);
    $white = imagecolorallocate($resized, 255, 255, 255);
    imagefilledrectangle($resized, 0, 0, $w, $h, $white);
    imagecopyresampled($resized, $canvas, 0, 0, 0, 0, $w, $h, $width, $height);
    imagejpeg($resized, $bestPath, $quality);
    imagedestroy($resized);
    $size = filesize($bestPath) ?: PHP_INT_MAX;

    if ($size <= $maxBytes) {
        break;
    }

    if ($quality > 60) {
        $quality -= 5;
    } elseif ($scale > 0.5) {
        $scale -= 0.05;
        $quality = 88;
    } else {
        break;
    }
} while (true);

copy($bestPath, $output);
@unlink($bestPath);
imagedestroy($canvas);

$final = filesize($output) ?: 0;
$mb = round($final / 1024 / 1024, 2);

echo "Output: {$output}\n";
echo "Size: {$final} bytes ({$mb} MB)\n";

if ($final > $maxBytes) {
    fwrite(STDERR, "Impossible de descendre sous 5 Mo avec GD seul. Réduis la résolution source.\n");
    exit(1);
}

exit(0);
