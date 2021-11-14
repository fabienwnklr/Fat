<?php

namespace app\core\db;

use app\core\Model;
use app\core\Application;

abstract class DbModel extends Model
{
    abstract public static function tableName(): string;

    abstract public function attributes(): array;

    abstract public static function primaryKey(): string;
    
    public function save(): bool
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($attr) => ":$attr", $attributes);
        $statement = self::prepare("INSERT INTO $tableName ( " . implode(',', $attributes) . ") VALUES (" . implode(',', $params) . ")");


        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        
        return true;
    }

    public static function findOne($where = null)
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        // SELECT * FROM $tableName WHERE $sql
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        
        foreach ($where as $key => $value) {
            $statement->bindValue(":$key", $value);
        }

        $statement->execute();
        return $statement->fetchObject(static::class);
    }

    /**
     * shortcut to prepapre sql
     *
     * @param string $sql
     * @return \PDOStatement|false
     */
    public static function prepare(string $sql): \PDOStatement|false
    {
        return Application::$app->db->pdo->prepare($sql);
    }
}