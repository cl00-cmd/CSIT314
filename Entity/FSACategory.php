<?php
declare(strict_types=1);

namespace App\Entity;

use App\Config\Database;
use PDO;

// Entity layer for Platform Manager FSA category data.
// Called by the FSA category Controllers only; this class is the database-facing part of the BCE flow.
final class FSACategory
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function saveCategory(array $categoryData): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO categories (name, description, status)
             VALUES (:category_name, :description, :status)'
        );

        return $statement->execute([
            'category_name' => $categoryData['categoryName'],
            'description' => $categoryData['description'],
            'status' => $categoryData['status'] ?? 'active',
        ]);
    }

    public function retrieveCategory(int $categoryID = 0): array
    {
        if ($categoryID > 0) {
            $category = $this->getCategory($categoryID);
            return $category === null ? [] : [$category];
        }

        return $this->searchCategory('');
    }

    public function getCategory(int $categoryID): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id AS categoryID, name AS categoryName, description, status, created_at
             FROM categories
             WHERE id = :category_id
             LIMIT 1'
        );
        $statement->execute(['category_id' => $categoryID]);

        return $statement->fetch() ?: null;
    }

    public function updateCategory(int $categoryID, array $categoryData): bool
    {
        $statement = $this->db->prepare(
            'UPDATE categories
             SET name = :category_name,
                 description = :description,
                 status = :status
             WHERE id = :category_id'
        );

        return $statement->execute([
            'category_id' => $categoryID,
            'category_name' => $categoryData['categoryName'],
            'description' => $categoryData['description'],
            'status' => $categoryData['status'],
        ]);
    }

    public function suspendCategory(int $categoryID): bool
    {
        return $this->setCategoryStatus($categoryID, 'suspended');
    }

    public function setCategoryStatus(int $categoryID, string $status): bool
    {
        $statement = $this->db->prepare(
            'UPDATE categories
             SET status = :status
             WHERE id = :category_id'
        );

        return $statement->execute([
            'category_id' => $categoryID,
            'status' => $status,
        ]);
    }

    public function searchCategory(string $keyword): array
    {
        $sql = 'SELECT c.id AS categoryID, c.name AS categoryName, c.description, c.status, c.created_at,
                       COUNT(cp.id) AS campaignCount
                FROM categories c
                LEFT JOIN campaigns cp ON cp.category_id = c.id';

        $parameters = [];
        if ($keyword !== '') {
            $sql .= ' WHERE c.name LIKE :name_term OR c.description LIKE :description_term OR c.status LIKE :status_term';
            $term = '%' . $keyword . '%';
            $parameters = [
                'name_term' => $term,
                'description_term' => $term,
                'status_term' => $term,
            ];
        }

        $sql .= ' GROUP BY c.id, c.name, c.description, c.status, c.created_at
                  ORDER BY c.name ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }
}
