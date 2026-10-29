<?php
// app/controllers/HomeController.php
class HomeController extends Controller
{
    public function index(): void
    {
        $sql = "SELECT p.*, pi.file_name AS image
                FROM products p
                LEFT JOIN product_images pi
                ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE p.status = 'active'
                ORDER BY p.created_at DESC";
        $products = Database::getInstance()->query($sql)->fetchAll();

        $this->view('home/index', [
            'pageTitle' => 'Trang chủ',
            'products' => $products,
        ]);
    }
}
