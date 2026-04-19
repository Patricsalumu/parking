<?php
// Simple generator for PNG PWA icons using GD. Run this from browser or CLI.
if (!function_exists('imagecreatetruecolor')) {
    http_response_code(500);
    echo "GD extension not available.";
    exit;
}

function create_icon($w, $h, $outPath) {
    $img = imagecreatetruecolor($w, $h);
    imagesavealpha($img, true);
    $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $trans);

    // Colors
    $bg = imagecolorallocate($img, 13, 110, 253); // #0d6efd
    $white = imagecolorallocate($img, 255,255,255);
    $black = imagecolorallocate($img, 0,0,0);

    // Background filled rect
    imagefilledrectangle($img, 0, 0, $w, $h, $bg);

    // Scaled truck body
    $pad = (int)($w * 0.08);
    $bodyW = $w - 2 * $pad;
    $bodyH = (int)($h * 0.28);
    $bodyX = $pad;
    $bodyY = (int)($h * 0.46);
    // main body (white)
    imagefilledrectangle($img, $bodyX, $bodyY, $bodyX + $bodyW, $bodyY + $bodyH, $white);

    // cab (rear) - slightly raised
    $cabW = (int)($bodyW * 0.26);
    $cabH = (int)($bodyH * 0.9);
    $cabX = $bodyX + (int)($bodyW * 0.68);
    $cabY = $bodyY - (int)($bodyH * 0.36);
    imagefilledrectangle($img, $cabX, $cabY, $cabX + $cabW, $cabY + $cabH, $white);

    // windows in cab (bg color to appear cutout)
    $winW = (int)($cabW * 0.8);
    $winH = (int)($cabH * 0.4);
    $winX = $cabX + (int)(($cabW - $winW) / 2);
    $winY = $cabY + (int)($cabH * 0.08);
    imagefilledrectangle($img, $winX, $winY, $winX + $winW, $winY + $winH, $bg);

    // wheels
    $wheelR = (int)($h * 0.08);
    $wheelY = $bodyY + $bodyH + $wheelR - (int)($h * 0.02);
    $wheel1X = $bodyX + (int)($bodyW * 0.22);
    $wheel2X = $bodyX + (int)($bodyW * 0.72);
    imagefilledellipse($img, $wheel1X, $wheelY, $wheelR * 2, $wheelR * 2, $black);
    imagefilledellipse($img, $wheel2X, $wheelY, $wheelR * 2, $wheelR * 2, $black);

    // Draw stylized 'P' overlay on truck body (using bg color to cut into body)
    $pW = (int)($bodyW * 0.18);
    $pH = (int)($bodyH * 0.92);
    $pX = $bodyX + (int)($bodyW * 0.08);
    $pY = $bodyY + (int)($bodyH * 0.04);
    // vertical bar
    imagefilledrectangle($img, $pX, $pY, $pX + $pW, $pY + $pH, $bg);
    // top bowl (ellipse in bg)
    $bowlW = (int)($pW * 2.1);
    $bowlH = (int)($pH * 0.58);
    $bowlCX = $pX + (int)($pW * 1.05);
    $bowlCY = $pY + (int)($bowlH * 0.45);
    imagefilledellipse($img, $bowlCX, $bowlCY, $bowlW, $bowlH, $bg);
    // inner hole (white) to form the P shape
    $holeW = (int)($bowlW * 0.7);
    $holeH = (int)($bowlH * 0.55);
    imagefilledellipse($img, $bowlCX, $bowlCY, $holeW, $holeH, $white);

    // slight shadow under truck (semi-transparent black)
    $shadow = imagecolorallocatealpha($img, 0,0,0,80);
    imagefilledellipse($img, $wheel1X, $wheelY + (int)($wheelR * 0.35), $wheelR * 2, (int)($wheelR * 0.7), $shadow);
    imagefilledellipse($img, $wheel2X, $wheelY + (int)($wheelR * 0.35), $wheelR * 2, (int)($wheelR * 0.7), $shadow);

    // save PNG
    imagepng($img, $outPath, 6);
    imagedestroy($img);
}

$dir = __DIR__ . DIRECTORY_SEPARATOR;
$png192 = $dir . 'icon-192.png';
$png512 = $dir . 'icon-512.png';

try {
    create_icon(192,192,$png192);
    create_icon(512,512,$png512);
    echo "Generated icons: icon-192.png, icon-512.png";
} catch (Exception $e) {
    http_response_code(500);
    echo "Generation failed: " . $e->getMessage();
}
