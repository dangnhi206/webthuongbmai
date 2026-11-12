<?php
// app/core/Model.php
abstract class Model
{
    protected static string $table = '';

    protected static function db(): PDO
    {
        return Database::getInstance();
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $stmt = static::db()->query("SELECT * FROM " . static::$table . " ORDER BY " . $orderBy);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = static::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res === false ? null : $res;
    }

    public static function insert(array $data): int
    {
        $fields = array_keys($data);
        $columns = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);
        return (int) static::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $field) {
            $sets[] = "{$field} = :{$field}";
        }
        $setString = implode(', ', $sets);
        $data['id'] = $id;
        $sql = "UPDATE " . static::$table . " SET {$setString} WHERE id = :id";
        $stmt = static::db()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        $stmt = static::db()->prepare("DELETE FROM " . static::$table . " WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
