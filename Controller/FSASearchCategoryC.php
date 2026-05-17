<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FSACategory Entity class.
use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category search.
// This Controller validates the keyword before calling Entity/FSACategory.php.
final class FSASearchCategoryC
{
    private FSACategory $fsaCategory;

    // Creates the FSACategory Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    // Validates and trims the category search keyword.
    public function validateKeyword(string $keyword): array
    {
        return [
            'success' => true,
            'keyword' => trim($keyword),
        ];
    }

    // Searches FSA categories using the validated keyword.
    public function searchCategory(string $keyword): array
    {
        $validated = $this->validateKeyword($keyword);

        // Stops the search when validation fails.
        if (!$validated['success']) {
            return [];
        }

        // Controller -> Entity to retrieve matching categories.
        return $this->fsaCategory->searchCategory($validated['keyword']);
    }
}