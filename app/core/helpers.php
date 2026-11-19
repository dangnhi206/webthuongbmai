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
    if (!empty($fileName)) {
        $folder = ($type === 'thumb') ? 'products/thumbs/' : 'products/';
        $diskPath = __DIR__ . '/../../public/uploads/' . $folder . $fileName;
        if (file_exists($diskPath)) {
            return base_url('uploads/' . $folder . $fileName);
        }
    }
    return "data:image/svg+xml;charset=UTF-8," . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">' .
        '<rect width="400" height="400" fill="#e9ecef"/>' .
        '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="20" fill="#6c757d">Chưa có ảnh</text>' .
        '</svg>'
    );
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

// ---------------------------------------------------------------
// Bo sung Buoi 4: HTML the san pham dung chung cho nhieu khoi/trang
// ---------------------------------------------------------------
function product_card_html(array $product): string
{
    $url = e(base_url('product/detail/' . $product['id']));
    $img = e(product_image_url($product['image'] ?? null, 'thumb'));
    $name = e($product['name']);
    $price = format_vnd($product['price']);
    return <<<HTML
<div class="col">
    <a href="{$url}" class="text-decoration-none text-dark">
        <div class="card h-100 shadow-sm">
            <img src="{$img}" class="card-img-top" style="aspect-ratio: 1/1; object-fit: cover;" loading="lazy" alt="{$name}">
            <div class="card-body d-flex flex-column">
                <h3 class="h6 card-title">{$name}</h3>
                <p class="text-danger fw-bold mt-auto mb-0">{$price}</p>
            </div>
        </div>
    </a>
</div>
HTML;
}

// ---------------------------------------------------------------
// Bo sung Buoi 5: giu tham so loc/sap xep tren URL + thanh phan trang
// ---------------------------------------------------------------
/**
 * Tra ve URL trang tim kiem voi toan bo tham so GET hien tai duoc GIU NGUYEN,
 * chi ghi de nhung khoa co trong $override. Dung cho lien ket phan trang / doi sap xep
 * de khong lam mat dieu kien loc nguoi dung da chon.
 */
function query_url(array $override = []): string
{
    $params = $_GET;
    unset($params['url']); // Loai bo tham so 'url' tu rewrite rule cua Apache (.htaccess)
    $params = array_merge($params, $override);
    $params = array_filter($params, function ($v) {
        return $v !== '' && $v !== null && $v !== [];
    });
    $query = http_build_query($params);
    return base_url('product/search') . ($query !== '' ? '?' . $query : '');
}

/**
 * Tra ve HTML thanh phan trang Bootstrap. $page: trang hien tai (tu 1),
 * $totalPages: tong so trang. Tra ve chuoi rong neu chi co 1 trang.
 */
function pagination_html(int $page, int $totalPages): string
{
    if ($totalPages <= 1) {
        return '';
    }
    $html = '<nav aria-label="Phân trang"><ul class="pagination justify-content-center">';
    $prevDisabled = $page <= 1 ? ' disabled' : '';
    $prevUrl = e(query_url(['page' => max(1, $page - 1)]));
    $html .= "<li class="page-item{$prevDisabled}"><a class="page-link" href="{$prevUrl}">&laquo; Trước</a></li>";

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $page ? ' active' : '';
        $url = e(query_url(['page' => $i]));
        $html .= "<li class="page-item{$active}"><a class="page-link" href="{$url}">{$i}</a></li>";
    }

    $nextDisabled = $page >= $totalPages ? ' disabled' : '';
    $nextUrl = e(query_url(['page' => min($totalPages, $page + 1)]));
    $html .= "<li class="page-item{$nextDisabled}"><a class="page-link" href="{$nextUrl}">Sau &raquo;</a></li>";
    $html .= '</ul></nav>';
    return $html;
}
