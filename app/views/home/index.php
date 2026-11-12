<div class="p-5 mb-4 bg-light rounded-3 shadow-sm text-center">
    <div class="container-fluid py-3">
        <h1 class="display-5 fw-bold">Chào mừng đến với Shop Online</h1>
        <p class="fs-5 text-muted">Trải nghiệm mua sắm nhanh chóng, tiện lợi và an toàn.</p>
    </div>
</div>

<h2 class="h4 mb-3">Sản phẩm nổi bật</h2>
<div class="row row-cols-1 row-cols-md-4 g-4">
    <?php if (empty($featuredProducts)): ?>
        <div class="col-12"><p class="text-muted">Chưa có sản phẩm nào được hiển thị nổi bật.</p></div>
    <?php else: ?>
        <?php foreach ($featuredProducts as $prod): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="<?= e(product_image_url($prod['image'], 'thumb')) ?>" class="card-img-top" style="aspect-ratio: 1/1; object-fit: cover;" alt="<?= e($prod['name']) ?>">
                    <div class="card-body">
                        <span class="badge bg-light text-dark border mb-1"><?= e($prod['category_name']) ?></span>
                        <h5 class="card-title fs-6"><?= e($prod['name']) ?></h5>
                        <p class="card-text text-danger fw-bold"><?= format_vnd($prod['price']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
