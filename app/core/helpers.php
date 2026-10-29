<?php
// app/core/helpers.php
// Cac ham ho tro dung chung toan he thong (khong thuoc class nao)

/**
 * Chong XSS: escape chuoi truoc khi in ra HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Dung duong dan tuyet doi tu BASE_URL.
 */
function base_url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Dung duong dan toi file tinh trong public/assets/
 */
function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Dinh dang so tien theo kieu Viet Nam: 1990000 -> "1.990.000 đ"
 */
function format_vnd($number): string
{
    return number_format((float) $number, 0, ',', '.') . ' đ';
}

/**
 * HAM DUNG CHUNG DUY NHAT de lay dia chi anh san pham.
 */
function product_image_url(?string $fileName, string $size = 'thumb'): string
{
    if (empty($fileName)) {
        return asset('img/no-image.png');
    }
    $folder = $size === 'full' ? 'uploads/products/' : 'uploads/products/thumbs/';
    $diskPath = __DIR__ . '/../../public/' . $folder . $fileName;

    if (!file_exists($diskPath)) {
        return asset('img/no-image.png');
    }
    return base_url($folder . $fileName);
}
