<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FSACategory Entity class.
use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller to view category records.
// This Controller retrieves category data from Entity/FSACategory.php.
final class FSAViewCategoryC
{
    private FSACategory $fsaCategory;

    // Creates the FSACategory Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    // Retrieves a selected FSA category record.
    public function getCategory(int $categoryID): ?array
    {
        // Controller -> Entity to retrieve category details.
        return $this->fsaCategory->getCategory($categoryID);
    }

    // Retrieves all FSA category records.
    public function retrieveCategory(): array
    {
        // Controller -> Entity to retrieve all categories.
        return $this->fsaCategory->retrieveCategory();
    }
}