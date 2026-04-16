<?php
declare(strict_types=1);

namespace App\Config;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function settings(): array
    {
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT');
        $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER');
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

        return [
            'host' => $host !== false && $host !== null && $host !== '' ? $host : '127.0.0.1',
            'port' => (int) ($port !== false && $port !== null && $port !== '' ? $port : 3306),
            'name' => $name !== false && $name !== null && $name !== '' ? $name : 'csit314_fundraising',
            'user' => $user !== false && $user !== null && $user !== '' ? $user : 'root',
            'password' => $password !== false && $password !== null ? $password : '',
        ];
    }

    public static function createDatabaseIfMissing(): void
    {
        $settings = self::settings();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=utf8mb4',
            $settings['host'],
            $settings['port']
        );

        $pdo = new PDO($dsn, $settings['user'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $databaseName = str_replace('`', '``', $settings['name']);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        self::createDatabaseIfMissing();
        $settings = self::settings();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $settings['host'],
            $settings['port'],
            $settings['name']
        );

        self::$connection = new PDO($dsn, $settings['user'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }

    public static function runSchemaFile(string $schemaPath): void
    {
        self::createDatabaseIfMissing();
        $sql = file_get_contents($schemaPath);
        if ($sql === false) {
            throw new \RuntimeException('Unable to read schema file: ' . $schemaPath);
        }

        $pdo = self::getConnection();
        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];
        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if ($trimmed !== '') {
                $pdo->exec($trimmed);
            }
        }
    }
}
