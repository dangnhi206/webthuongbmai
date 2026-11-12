<?php
// app/core/helpers.php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $cleanPath = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($cleanPath !== '' ? '/' . $cleanPath : '');
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function format_vnd(float|int $amount): string
{
    return number_format($amount, 0, ',', '.') . ' đ';
}

function product_image_url(?string $fileName, string $type = 'main'): string
{
    if (empty($fileName)) {
        return asset('images/no-image.png');
    }
    $folder = $type === 'thumb' ? 'products/thumbs/' : 'products/';
    return base_url('uploads/' . $folder . $fileName);
}

// ---------------------------------------------------------------
// Bo sung Buoi 2: chong CSRF va thong bao flash (session-based)
// ---------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(string $token): void
{
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(419);
        die('Phien lam viec da het han hoac yeu cau khong hop le. Vui long tai lai trang va thu lai.');
    }
}

function flash(string $key, ?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
        return null;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $data = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $data;
    }
    return null;
}
