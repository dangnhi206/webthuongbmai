<?php
// app/core/Controller.php
// Lop Controller co so: ham render view theo layout va ham redirect
abstract class Controller
{
    /**
     * Render mot view, long vao trong 1 layout dung chung.
     */
    protected function view(string $view, array $data = [], string|false $layout = 'main'): void
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die('Khong tim thay view: ' . e($view));
        }

        if ($layout === false) {
            extract($data);
            require $viewFile;
            return;
        }

        $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            die('Khong tim thay layout: ' . e($layout));
        }

        $content = function () use ($viewFile, $data) {
            extract($data);
            require $viewFile;
        };
        require $layoutFile;
    }

    /** Chuyen huong toi mot URL noi bo (tinh tu BASE_URL) */
    protected function redirect(string $path): void
    {
        header('Location: ' . base_url($path));
        exit;
    }
}
