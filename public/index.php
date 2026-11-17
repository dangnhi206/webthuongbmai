<?php
// public/index.php
session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/AdminController.php';
require_once __DIR__ . '/../app/core/Auth.php';

// Services
require_once __DIR__ . '/../app/services/ImageService.php';

// Models
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/ProductImage.php';
require_once __DIR__ . '/../app/models/Contact.php';

// Controllers
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/CategoryController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../app/controllers/PolicyController.php';
require_once __DIR__ . '/../app/controllers/ContactController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AccountController.php';
require_once __DIR__ . '/../app/controllers/admin/AdminAuthController.php';
require_once __DIR__ . '/../app/controllers/admin/AdminDashboardController.php';
require_once __DIR__ . '/../app/controllers/admin/AdminUserController.php';
require_once __DIR__ . '/../app/controllers/admin/AdminCategoryController.php';
require_once __DIR__ . '/../app/controllers/admin/AdminProductController.php';
require_once __DIR__ . '/../app/controllers/admin/AdminProductImageController.php';
require_once __DIR__ . '/../app/controllers/admin/AdminContactController.php';

// Simple Router
$url = $_GET['url'] ?? '';
$url = trim($url, '/');
$parts = $url === '' ? [] : explode('/', $url);

if (empty($parts)) {
    (new HomeController())->index();
    exit;
}

if ($parts[0] === 'category') {
    $action = $parts[1] ?? 'show';
    $param = $parts[2] ?? '';
    $ctrl = new CategoryController();
    if (method_exists($ctrl, $action)) {
        $ctrl->$action($param);
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
    exit;
}

if ($parts[0] === 'product') {
    $action = $parts[1] ?? 'detail';
    $param = $parts[2] ?? 0;
    $ctrl = new ProductController();
    if (method_exists($ctrl, $action)) {
        $ctrl->$action($param);
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
    exit;
}

if ($parts[0] === 'policy') {
    (new PolicyController())->index();
    exit;
}

if ($parts[0] === 'contact') {
    $action = $parts[1] ?? 'index';
    $ctrl = new ContactController();
    if (method_exists($ctrl, $action)) {
        $ctrl->$action();
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
    exit;
}

if ($parts[0] === 'auth') {
    $action = $parts[1] ?? 'login';
    $ctrl = new AuthController();
    if (method_exists($ctrl, $action)) {
        $ctrl->$action();
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
    exit;
}

if ($parts[0] === 'account') {
    $action = $parts[1] ?? 'profile';
    $ctrl = new AccountController();
    if (method_exists($ctrl, $action)) {
        $ctrl->$action();
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
    exit;
}

if ($parts[0] === 'admin') {
    $resource = $parts[1] ?? 'dashboard';
    $action = $parts[2] ?? 'index';
    $param = $parts[3] ?? null;

    $ctrl = match ($resource) {
        'auth' => new AdminAuthController(),
        'dashboard' => new AdminDashboardController(),
        'user' => new AdminUserController(),
        'category' => new AdminCategoryController(),
        'product' => new AdminProductController(),
        'productImage' => new AdminProductImageController(),
        'contact' => new AdminContactController(),
        default => null
    };

    if ($ctrl && method_exists($ctrl, $action)) {
        if ($param !== null) {
            $ctrl->$action($param);
        } else {
            $ctrl->$action();
        }
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
    exit;
}

http_response_code(404);
require __DIR__ . '/../app/views/errors/404.php';
