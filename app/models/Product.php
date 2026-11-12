<?php
// app/models/Product.php
class Product extends Model
{
    protected static string $table = 'products';

    public static function skuExists(string $sku, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE sku = :sku';
        $params = ['sku' => $sku];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    public static function getFeatured(int $limit = 8): array
    {
        $sql = "SELECT p.*, c.name AS category_name, pi.file_name AS image
                FROM products p
                JOIN categories c ON c.id = p.category_id
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE p.status = 'active' AND p.is_featured = 1
                ORDER BY p.id DESC
                LIMIT :limit";
        $stmt = static::db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
