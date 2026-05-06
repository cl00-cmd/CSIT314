<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category creation.
// This Controller validates category input before calling Entity/FSACategory.php.
final class FSACreateCategoryC
{
    private FSACategory $fsaCategory;

    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    public function validateCategory(array $input): array
    {
        $categoryName = trim((string) ($input['categoryName'] ?? ''));
        if ($categoryName === '') {
            return ['success' => false, 'message' => 'Category name is required.'];
        }

        return [
            'success' => true,
            'categoryName' => $categoryName,
            'description' => trim((string) ($input['description'] ?? '')),
            'status' => (string) ($input['status'] ?? 'active'),
        ];
    }

    public function createCategory(array $input): array
    {
        $validated = $this->validateCategory($input);
        if (!$validated['success']) {
            return $validated;
        }

        try {
            $saved = $this->fsaCategory->saveCategory($validated);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to create the FSA category.'];
        }

        return [
            'success' => $saved,
            'message' => $saved ? 'FSA category created successfully.' : 'Unable to create the FSA category.',
        ];
    }
}
