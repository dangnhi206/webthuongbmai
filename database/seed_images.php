<?php
// database/seed_images.php
// Script chay MOT LAN de sinh anh demo cho du lieu mau bang GD.

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

header('Content-Type: text/html; charset=utf-8');

$pdo = Database::getInstance();
$uploadDir = __DIR__ . '/../public/uploads/products/';
$thumbDir = $uploadDir . 'thumbs/';
$noImageDir = __DIR__ . '/../public/assets/img/';

foreach ([$uploadDir, $thumbDir, $noImageDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 1) Tao san anh mac dinh (no-image.png) neu chua co
$noImagePath = $noImageDir . 'no-image.png';
if (!file_exists($noImagePath)) {
    $img = imagecreatetruecolor(400, 400);
    $bg = imagecolorallocate($img, 230, 230, 230);
    imagefill($img, 0, 0, $bg);
    $gray = imagecolorallocate($img, 130, 130, 130);
    imagestring($img, 5, 140, 190, 'NO IMAGE', $gray);
    imagepng($img, $noImagePath);
    imagedestroy($img);
    echo "Da tao anh mac dinh: assets/img/no-image.png<br>";
}

// 2) Sinh anh demo cho tung san pham (tru 2 san pham co y de trong)
$products = $pdo->query('SELECT id, name FROM products ORDER BY id')->fetchAll();
$skipProductIds = [3, 17]; // co y khong co anh de kiem tra anh mac dinh
$palette = [
    [41, 128, 185], [39, 174, 96], [142, 68, 173],
    [230, 126, 34], [192, 57, 43], [26, 188, 156],
];

$insertStmt = $pdo->prepare(
    'INSERT INTO product_images
    (product_id, file_name, original_name, alt_text, is_primary, sort_order, created_at)
    VALUES
    (:product_id, :file_name, :original_name, :alt_text, :is_primary, :sort_order, NOW())'
);

foreach ($products as $product) {
    if (in_array($product['id'], $skipProductIds, true)) {
        echo "Bo qua san pham #{$product['id']} ({$product['name']}) - de trong de test anh mac dinh<br>";
        continue;
    }

    $imageCount = random_int(1, 3);
    for ($i = 0; $i < $imageCount; $i++) {
        $fileName = uniqid('seed_', true) . '.jpg';
        [$r, $g, $b] = $palette[array_rand($palette)];

        // Anh chinh
        $main = imagecreatetruecolor(IMAGE_MAIN_WIDTH, IMAGE_MAIN_WIDTH);
        $bgColor = imagecolorallocate($main, $r, $g, $b);
        imagefill($main, 0, 0, $bgColor);
        $white = imagecolorallocate($main, 255, 255, 255);
        $label = $product['name'] . ($imageCount > 1 ? ' (' . ($i + 1) . ')' : '');
        imagestring($main, 5, 40, (int) (IMAGE_MAIN_WIDTH / 2), $label, $white);
        imagejpeg($main, $uploadDir . $fileName, 85);

        // Anh thu nho
        $thumb = imagecreatetruecolor(IMAGE_THUMB_WIDTH, IMAGE_THUMB_WIDTH);
        imagecopyresampled(
            $thumb, $main,
            0, 0, 0, 0,
            IMAGE_THUMB_WIDTH, IMAGE_THUMB_WIDTH,
            IMAGE_MAIN_WIDTH, IMAGE_MAIN_WIDTH
        );
        imagejpeg($thumb, $thumbDir . $fileName, 85);

        imagedestroy($main);
        imagedestroy($thumb);

        $insertStmt->execute([
            'product_id' => $product['id'],
            'file_name' => $fileName,
            'original_name' => 'seed.jpg',
            'alt_text' => $product['name'],
            'is_primary' => $i === 0 ? 1 : 0,
            'sort_order' => $i,
        ]);
    }
    echo "Da tao {$imageCount} anh cho san pham #{$product['id']} - {$product['name']}<br>";
}

echo '<p><strong>Hoan tat sinh anh mau. Co the xoa hoac giu lai file nay, khong anh huong ung dung chinh.</strong></p>';
