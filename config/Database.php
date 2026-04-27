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

    public static function ensureCampaignCompletionColumn(): void
    {
        $pdo = self::getConnection();
        $columnCheck = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "campaigns"
               AND COLUMN_NAME = "completed_at"'
        );
        $columnCheck->execute();
        if ((int) $columnCheck->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE campaigns ADD COLUMN completed_at DATETIME NULL AFTER end_date');
            $pdo->exec('CREATE INDEX idx_campaigns_completed_at ON campaigns (completed_at)');
        }

        $pdo->exec(
            'UPDATE campaigns c
             LEFT JOIN (
                SELECT reached.campaign_id, MIN(reached.donated_at) AS completed_at
                FROM (
                    SELECT d1.campaign_id, d1.id, d1.donated_at,
                           (
                               SELECT COALESCE(SUM(d2.amount), 0)
                               FROM donations d2
                               WHERE d2.campaign_id = d1.campaign_id
                                 AND (
                                     d2.donated_at < d1.donated_at
                                     OR (d2.donated_at = d1.donated_at AND d2.id <= d1.id)
                                 )
                           ) AS amount_at_donation
                    FROM donations d1
                ) reached
                INNER JOIN campaigns c2 ON c2.id = reached.campaign_id
                WHERE reached.amount_at_donation >= c2.funding_goal
                GROUP BY reached.campaign_id
             ) completion ON completion.campaign_id = c.id
             SET c.completed_at = COALESCE(completion.completed_at, c.end_date, c.created_at)
             WHERE c.status = "completed"
               AND c.completed_at IS NULL'
        );
    }
}
