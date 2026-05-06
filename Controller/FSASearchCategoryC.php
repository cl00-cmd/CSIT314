<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category search.
// This Controller validates the keyword before calling Entity/FSACategory.php.
final class FSASearchCategoryC
{
    private FSACategory $fsaCategory;

    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    public function validateKeyword(string $keyword): array
    {
        return [
            'success' => true,
            'keyword' => trim($keyword),
        ];
    }

    public function searchCategory(string $keyword): array
    {
        $validated = $this->validateKeyword($keyword);
        if (!$validated['success']) {
            return [];
        }

        return $this->fsaCategory->searchCategory($validated['keyword']);
    }
}
