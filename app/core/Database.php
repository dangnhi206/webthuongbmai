<?php
// app/core/Database.php
// Ket noi CSDL bang PDO, chi tao MOT ket noi duy nhat dung chung (singleton)
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                error_log('Loi ket noi CSDL: ' . $e->getMessage());
                http_response_code(500);
                die('Xin loi, he thong dang gap su co. Vui long thu lai sau.');
            }
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
