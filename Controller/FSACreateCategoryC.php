<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FSACategory Entity class.
use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category creation.
// This Controller validates category input before calling Entity/FSACategory.php.
final class FSACreateCategoryC
{
    private FSACategory $fsaCategory;

    // Creates the FSACategory Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    // Validates the category input fields.
    public function validateCategory(array $input): array
    {
        $categoryName = trim((string) ($input['categoryName'] ?? ''));

        // Checks whether the category name is empty.
        if ($categoryName === '') {
            return [
                'success' => false,
                'message' => 'Category name is required.'
            ];
        }

        return [
            'success' => true,
            'categoryName' => $categoryName,
            'description' => trim((string) ($input['description'] ?? '')),
            'status' => (string) ($input['status'] ?? 'active'),
        ];
    }

    // Creates a new FSA category after validation.
    public function createCategory(array $input): array
    {
        $validated = $this->validateCategory($input);

        // Stops category creation when validation fails.
        if (!$validated['success']) {
            return $validated;
        }

        try {

            // Controller -> Entity to save the new category.
            $saved = $this->fsaCategory->saveCategory($validated);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to create the FSA category.'
            ];
        }

        return [
            'success' => $saved,
            'message' => $saved
                ? 'FSA category created successfully.'
                : 'Unable to create the FSA category.',
        ];
    }
}