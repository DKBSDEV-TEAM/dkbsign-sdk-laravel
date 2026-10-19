<?php

declare(strict_types=1);

$path = __DIR__.'/storage/signature.png';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "Extension GD requise pour générer signature.png\n");
    exit(1);
}

$width = 400;
$height = 120;
$image = imagecreatetruecolor($width, $height);
$white = imagecolorallocate($image, 255, 255, 255);
$ink = imagecolorallocate($image, 25, 55, 140);
imagefilledrectangle($image, 0, 0, $width, $height, $white);
imagesetthickness($image, 2);

for ($i = 0; $i < 220; $i++) {
    $x1 = 30 + $i;
    $y1 = 65 + (int) (sin($i / 14) * 22);
    $x2 = 31 + $i;
    $y2 = 65 + (int) (sin(($i + 1) / 14) * 22);
    imageline($image, $x1, $y1, $x2, $y2, $ink);
}

imagestring($image, 4, 32, 88, 'Signature test', $ink);
imagepng($image, $path);
imagedestroy($image);

echo "Created: {$path}\n";
