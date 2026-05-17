<?php
declare(strict_types=1);

namespace App\Entity;

// Load database connection and PDO.
use App\Config\Database;
use PDO;

// Entity layer for FSA category data.
// Called by Fund Raiser Controllers, Donor BCE Controllers,
// and Controller/PlatformController.php.
final class CategoryEntity
{
    private PDO $db;

    // Creates the database connection.
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Retrieves one category record by category ID.
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

    // Retrieves all category records, with optional keyword and active-only filters.
    public function getAll(string $keyword = '', bool $activeOnly = false): array
    {
        $sql = 'SELECT c.id, c.name, c.description, c.status, c.created_at,
                       COUNT(cp.id) AS campaign_count
                FROM categories c
                LEFT JOIN campaigns cp ON cp.category_id = c.id';

        $conditions = [];
        $parameters = [];

        // Adds keyword search condition when keyword is entered.
        if ($keyword !== '') {
            $conditions[] = '(c.name LIKE :name_term OR c.description LIKE :description_term)';
            $term = '%' . $keyword . '%';
            $parameters['name_term'] = $term;
            $parameters['description_term'] = $term;
        }

        // Shows only active categories when required.
        if ($activeOnly) {
            $conditions[] = "c.status = 'active'";
        }

        // Adds WHERE clause if there are search conditions.
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY c.id, c.name, c.description, c.status, c.created_at
                  ORDER BY c.name ASC';

        // Executes category search query.
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    // Creates a new category record.
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

    // Updates an existing category record.
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