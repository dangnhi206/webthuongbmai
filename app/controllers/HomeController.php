<?php
// app/controllers/HomeController.php
class HomeController extends Controller
{
    public function index(): void
    {
        $categories = Category::all('name ASC');
        $featuredProducts = Product::getFeatured(8);
        $this->view('home/index', [
            'pageTitle' => 'Trang chủ',
            'categories' => $categories,
            'featuredProducts' => $featuredProducts
        ]);
    }
}
