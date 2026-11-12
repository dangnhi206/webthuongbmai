<?php
// app/services/ImageService.php
class ImageService
{
    public static function validate(array $file): array
    {
        $errors = [];
        $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = self::uploadErrorMessage($errorCode);
            return $errors;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
            $errors[] = 'Định dạng tệp không được hỗ trợ (chỉ nhận ' . implode(', ', UPLOAD_ALLOWED_EXT) . ').';
        }

        if (($file['size'] ?? 0) > UPLOAD_MAX_SIZE) {
            $errors[] = 'Dung lượng vượt quá ' . (int) (UPLOAD_MAX_SIZE / 1024 / 1024) . ' MB.';
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($realMime, $allowedMime, true)) {
            $errors[] = 'Tệp không phải ảnh hợp lệ (phát hiện loại thật: ' . $realMime . ', ví dụ tệp .php đổi đuôi thành .jpg sẽ bị chặn tại đây).';
        }

        $dimensions = @getimagesize($file['tmp_name']);
        if ($dimensions === false) {
            $errors[] = 'Không đọc được ảnh, tệp có thể bị hỏng hoặc không phải ảnh.';
        } else {
            [$width, $height] = $dimensions;
            $shortSide = min($width, $height);
            $longSide = max($width, $height);
            if ($shortSide < UPLOAD_MIN_SIDE) {
                $errors[] = "Ảnh quá nhỏ (cạnh ngắn {$shortSide}px, yêu cầu tối thiểu " . UPLOAD_MIN_SIDE . "px).";
            }
            if ($longSide > UPLOAD_MAX_SIDE) {
                $errors[] = "Ảnh quá lớn (cạnh dài {$longSide}px, vượt tối đa " . UPLOAD_MAX_SIDE . "px).";
            }
        }

        return $errors;
    }

    public static function processAndSave(array $file): array
    {
        $raw = file_get_contents($file['tmp_name']);
        $source = $raw === false ? false : @imagecreatefromstring($raw);
        if ($source === false) {
            throw new RuntimeException('Khong tao duoc tai nguyen anh tu tep tai len.');
        }

        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);

        $fileName = uniqid('prod_', true) . '.jpg';

        self::saveResized($source, $srcWidth, $srcHeight, IMAGE_MAIN_WIDTH, self::mainPath($fileName));
        self::saveResized($source, $srcWidth, $srcHeight, IMAGE_THUMB_WIDTH, self::thumbPath($fileName));

        imagedestroy($source);

        return [
            'file_name' => $fileName,
            'original_name' => $file['name'],
        ];
    }

    public static function deleteFiles(string $fileName): void
    {
        foreach ([self::mainPath($fileName), self::thumbPath($fileName)] as $path) {
            if (file_exists($path) && !@unlink($path)) {
                error_log('Khong xoa duoc file anh: ' . $path);
            }
        }
    }

    private static function saveResized($source, int $srcWidth, int $srcHeight, int $targetWidth, string $destPath): void
    {
        $ratio = min(1, $targetWidth / $srcWidth);
        $destWidth = max(1, (int) round($srcWidth * $ratio));
        $destHeight = max(1, (int) round($srcHeight * $ratio));

        $dest = imagecreatetruecolor($destWidth, $destHeight);
        $white = imagecolorallocate($dest, 255, 255, 255);
        imagefill($dest, 0, 0, $white);
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
        imagejpeg($dest, $destPath, 85);
        imagedestroy($dest);
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Tệp vượt quá dung lượng cho phép của máy chủ.',
            UPLOAD_ERR_PARTIAL => 'Tệp chỉ tải lên được một phần, vui lòng thử lại.',
            UPLOAD_ERR_NO_FILE => 'Chưa chọn tệp.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Lỗi hệ thống khi lưu tệp tải lên.',
            default => 'Lỗi tải tệp không xác định.',
        };
    }

    public static function mainPath(string $fileName): string
    {
        return __DIR__ . '/../../public/uploads/products/' . $fileName;
    }

    public static function thumbPath(string $fileName): string
    {
        return __DIR__ . '/../../public/uploads/products/thumbs/' . $fileName;
    }
}
