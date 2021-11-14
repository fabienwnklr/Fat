<?php

namespace app\core\db;

use PDO;
use app\core\Application;

/**
 * Database class
 * @author Fabien Winkler
 * @version 1.0
 * 
 */
class Database
{
    public \PDO $pdo;

    /**
     * @method __construtor
     *
     * @param array $config
     * @return void
     * 
     */
    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';

        $this->pdo = new \PDO($dsn, $user, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Function to create table into database
     * @param string $tableName
     * @param array $fields
     * @return bool
     * @throws \PDOException
     */
    public function createTable(string $tableName, array $fields): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS $tableName (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
            foreach ($fields as $field) {
                $sql .= $field['name'] . ' ' . $field['type'] . ' ' . $field['constraint'] . ', ';
            }
            $sql = rtrim($sql, ', ');
            $sql .= ' , created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP';
            $sql .= ') ENGINE=InnoDB;';

            $this->pdo->prepare($sql);
            $this->pdo->exec($sql);

            return true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Apply all migrations to the database
     *
     * @return void
     */
    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR . '/migrations');

        $toApplyMigrations = array_diff($files, $appliedMigrations);

        foreach ($toApplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') :
                continue;
            endif;

            require_once Application::$ROOT_DIR . '/migrations/' . $migration;

            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Applying migration $migration");
            $migrationBackup = $instance->up();
            
            if (!empty($migrationBackup)) {
                $this->log("Error during migration : $migrationBackup");
                return;
            }
            $this->log("Applied migration $migration");
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log('All migrations are already applied');
        }
    }

    /**
     * Create the migrations table if it doesn't exist
     *
     * @return void
     * 
     */
    public function createMigrationsTable()
    {
        try {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )');
        } catch (\PDOException $e) {
            $this->log('Error creating migrations table: ' . $e->getMessage());
        }
    }

    /**
     * Get all migrations that have been applied to the database
     *
     * @return array|false
     */
    public function getAppliedMigrations(): array|false
    {
        try {
            $statement = $this->pdo->prepare('SELECT name FROM migrations');
            $statement->execute();

            return $statement->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Save the migrations to the database
     *
     * @param array $migrations
     * @return void
     */
    public function saveMigrations(array $migrations): void
    {
        try {
            $str = implode(",", array_map(fn ($m) => "('$m')", $migrations));

            $statement = $this->pdo->prepare("INSERT INTO migrations (name) VALUES $str");
            $statement->execute();
        } catch (\PDOException $e) {
            $this->log('Error during insertion of migrations : ' . $e->getMessage());
        }
    }

    /**
     * Shortcut for prepare sql query
     *
     * @param string $sql
     * @return PDOStatement|false
     */
    public function prepare(string $sql): \PDOStatement|false
    {
        return $this->pdo->prepare($sql);
    }

    protected function log($message)
    {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    }
}
