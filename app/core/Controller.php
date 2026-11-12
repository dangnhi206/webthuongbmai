<?php
// app/core/Controller.php
abstract class Controller
{
    protected function view(string $viewPath, array $data = [], string|false $layout = 'main'): void
    {
        extract($data);
        $content = function() use ($viewPath, $data) {
            extract($data);
            $filePath = __DIR__ . '/../views/' . $viewPath . '.php';
            if (file_exists($filePath)) {
                require $filePath;
            } else {
                echo "View không tồn tại: " . htmlspecialchars($viewPath);
            }
        };

        if ($layout === false) {
            $content();
        } else {
            $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                $content();
            }
        }
    }

    protected function redirect(string $uri): void
    {
        header('Location: ' . base_url($uri));
        exit;
    }
}
