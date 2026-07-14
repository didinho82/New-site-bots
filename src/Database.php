<?php

/**
 * Camada de acesso ao banco de dados baseada em PDO.
 * Cria o schema automaticamente na primeira execução.
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function connect(array $config): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = $config['db_dsn'];
        $isSqlite = str_starts_with($dsn, 'sqlite:');

        if ($isSqlite) {
            $path = substr($dsn, strlen('sqlite:'));
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }

        $pdo = new PDO($dsn, $config['db_user'] ?? '', $config['db_pass'] ?? '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        if ($isSqlite) {
            $pdo->exec('PRAGMA journal_mode = WAL');
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        self::$pdo = $pdo;
        self::migrate($pdo, $isSqlite);

        return $pdo;
    }

    private static function migrate(PDO $pdo, bool $isSqlite): void
    {
        $pk = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'BIGINT AUTO_INCREMENT PRIMARY KEY';

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id $pk,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS bots (
            id $pk,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL,
            telegram_id TEXT,
            username TEXT,
            first_name TEXT,
            webhook_secret TEXT NOT NULL,
            webhook_set INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS commands (
            id $pk,
            bot_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            script TEXT NOT NULL DEFAULT '',
            enabled INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
    }
}
