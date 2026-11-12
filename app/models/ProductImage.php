<?php
// app/models/ProductImage.php
class ProductImage extends Model
{
    protected static string $table = 'product_images';

    public static function forProduct(int $productId): array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM product_images
            WHERE product_id = :pid
            ORDER BY is_primary DESC, sort_order ASC, id ASC'
        );
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll();
    }

    public static function countForProduct(int $productId): int
    {
        $stmt = static::db()->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = :pid');
        $stmt->execute(['pid' => $productId]);
        return (int) $stmt->fetchColumn();
    }

    public static function nextSortOrder(int $productId): int
    {
        $stmt = static::db()->prepare(
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images
            WHERE product_id = :pid'
        );
        $stmt->execute(['pid' => $productId]);
        return (int) $stmt->fetchColumn();
    }

    public static function setPrimary(int $imageId, int $productId): void
    {
        $db = static::db();
        $db->beginTransaction();
        try {
            $check = $db->prepare('SELECT COUNT(*) FROM product_images WHERE id = :id AND product_id = :pid');
            $check->execute(['id' => $imageId, 'pid' => $productId]);
            if ((int) $check->fetchColumn() === 0) {
                $db->rollBack();
                return;
            }

            $db->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = :pid')
               ->execute(['pid' => $productId]);

            $db->prepare('UPDATE product_images SET is_primary = 1 WHERE id = :id AND product_id = :pid')
               ->execute(['id' => $imageId, 'pid' => $productId]);

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function deleteImage(int $imageId, int $productId): void
    {
        $db = static::db();
        $stmt = $db->prepare('SELECT * FROM product_images WHERE id = :id AND product_id = :pid LIMIT 1');
        $stmt->execute(['id' => $imageId, 'pid' => $productId]);
        $image = $stmt->fetch();
        if ($image === false) {
            return;
        }

        $db->beginTransaction();
        try {
            $db->prepare('DELETE FROM product_images WHERE id = :id')->execute(['id' => $imageId]);

            if ((int) $image['is_primary'] === 1) {
                $nextId = $db->prepare(
                    'SELECT id FROM product_images WHERE product_id = :pid
                    ORDER BY sort_order ASC, id ASC LIMIT 1'
                );
                $nextId->execute(['pid' => $productId]);
                $nextImageId = $nextId->fetchColumn();
                if ($nextImageId) {
                    $db->prepare('UPDATE product_images SET is_primary = 1 WHERE id = :id')
                       ->execute(['id' => $nextImageId]);
                }
            }
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        ImageService::deleteFiles($image['file_name']);
    }

    public static function deleteAllForProduct(int $productId): void
    {
        $images = self::forProduct($productId);
        foreach ($images as $image) {
            ImageService::deleteFiles($image['file_name']);
        }
        static::db()->prepare('DELETE FROM product_images WHERE product_id = :pid')
            ->execute(['pid' => $productId]);
    }

    public static function updateAlt(int $imageId, int $productId, string $altText): void
    {
        static::db()->prepare(
            'UPDATE product_images SET alt_text = :alt WHERE id = :id AND product_id = :pid'
        )->execute(['alt' => $altText, 'id' => $imageId, 'pid' => $productId]);
    }

    public static function swapOrder(int $imageId, int $productId, string $direction): void
    {
        $images = self::forProduct($productId);
        $ordered = array_values(array_filter($images, fn($i) => (int) $i['is_primary'] === 0));
        $index = null;
        foreach ($ordered as $i => $img) {
            if ((int) $img['id'] === $imageId) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            return;
        }
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWith < 0 || $swapWith >= count($ordered)) {
            return;
        }

        $a = $ordered[$index];
        $b = $ordered[$swapWith];
        $db = static::db();
        $db->beginTransaction();
        try {
            $db->prepare('UPDATE product_images SET sort_order = :s WHERE id = :id')
               ->execute(['s' => $b['sort_order'], 'id' => $a['id']]);
            $db->prepare('UPDATE product_images SET sort_order = :s WHERE id = :id')
               ->execute(['s' => $a['sort_order'], 'id' => $b['id']]);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
