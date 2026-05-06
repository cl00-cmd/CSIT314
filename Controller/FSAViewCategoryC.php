<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller to view category records.
// This Controller retrieves category data from Entity/FSACategory.php.
final class FSAViewCategoryC
{
    private FSACategory $fsaCategory;

    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    public function getCategory(int $categoryID): ?array
    {
        return $this->fsaCategory->getCategory($categoryID);
    }

    public function retrieveCategory(): array
    {
        return $this->fsaCategory->retrieveCategory();
    }
}
