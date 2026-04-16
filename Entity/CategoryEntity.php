<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

final class CategoryEntity
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getById(int $categoryId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, description, status, created_at
             FROM categories
             WHERE id = :category_id
             LIMIT 1'
        );
        $statement->execute(['category_id' => $categoryId]);

        return $statement->fetch() ?: null;
    }

    public function getAll(string $keyword = '', bool $activeOnly = false): array
    {
        $sql = 'SELECT c.id, c.name, c.description, c.status, c.created_at,
                       COUNT(cp.id) AS campaign_count
                FROM categories c
                LEFT JOIN campaigns cp ON cp.category_id = c.id';

        $conditions = [];
        $parameters = [];
        if ($keyword !== '') {
            $conditions[] = '(c.name LIKE :term OR c.description LIKE :term)';
            $parameters['term'] = '%' . $keyword . '%';
        }
        if ($activeOnly) {
            $conditions[] = "c.status = 'active'";
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY c.id, c.name, c.description, c.status, c.created_at
                  ORDER BY c.name ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO categories (name, description, status)
             VALUES (:name, :description, :status)'
        );

        return $statement->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);
    }

    public function update(int $categoryId, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE categories
             SET name = :name,
                 description = :description,
                 status = :status
             WHERE id = :category_id'
        );

        return $statement->execute([
            'category_id' => $categoryId,
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);
    }
}
