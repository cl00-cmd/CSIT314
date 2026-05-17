<?php
declare(strict_types=1);

namespace App\Config;

// Load PDO database support.
use PDO;

final class Database
{
    // Stores the shared PDO database connection.
    private static ?PDO $connection = null;

    // Retrieves database configuration settings.
    public static function settings(): array
    {
        // Reads database settings from environment variables.
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT');
        $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER');
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

        // Returns default values when environment variables are missing.
        return [
            'host' => $host !== false && $host !== null && $host !== '' ? $host : '127.0.0.1',
            'port' => (int) ($port !== false && $port !== null && $port !== '' ? $port : 3306),
            'name' => $name !== false && $name !== null && $name !== '' ? $name : 'csit314_fundraising',
            'user' => $user !== false && $user !== null && $user !== '' ? $user : 'root',
            'password' => $password !== false && $password !== null ? $password : '',
        ];
    }

    // Creates the database automatically if it does not exist.
    public static function createDatabaseIfMissing(): void
    {
        $settings = self::settings();

        // Builds the database server connection string.
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=utf8mb4',
            $settings['host'],
            $settings['port']
        );

        // Creates temporary PDO connection without selecting a database.
        $pdo = new PDO($dsn, $settings['user'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Escapes database name before executing SQL.
        $databaseName = str_replace('`', '``', $settings['name']);

        // Creates the database if it does not already exist.
        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$databaseName}`
             CHARACTER SET utf8mb4
             COLLATE utf8mb4_unicode_ci"
        );
    }

    // Returns the shared PDO database connection.
    public static function getConnection(): PDO
    {
        // Reuses existing database connection when available.
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Ensures the database exists before connecting.
        self::createDatabaseIfMissing();

        $settings = self::settings();

        // Builds the database connection string.
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $settings['host'],
            $settings['port'],
            $settings['name']
        );

        // Creates the main PDO database connection.
        self::$connection = new PDO($dsn, $settings['user'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }

    // Runs the database schema SQL file.
    public static function runSchemaFile(string $schemaPath): void
    {
        // Ensures the database exists before running the schema.
        self::createDatabaseIfMissing();

        // Reads the schema SQL file.
        $sql = file_get_contents($schemaPath);

        if ($sql === false) {
            throw new \RuntimeException('Unable to read schema file: ' . $schemaPath);
        }

        $pdo = self::getConnection();

        // Splits SQL statements using semicolons.
        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];

        // Executes each SQL statement separately.
        foreach ($statements as $statement) {
            $trimmed = trim($statement);

            if ($trimmed !== '') {
                $pdo->exec($trimmed);
            }
        }
    }
}