<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category updates.
// This Controller validates update data before calling Entity/FSACategory.php.
final class FSAUpdateCategoryC
{
    private FSACategory $fsaCategory;

    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    public function validateCategory(array $input): array
    {
        $categoryID = (int) ($input['categoryID'] ?? 0);
        $categoryName = trim((string) ($input['categoryName'] ?? ''));
        if ($categoryID <= 0 || $categoryName === '') {
            return ['success' => false, 'message' => 'Please select a valid category to update.'];
        }

        return [
            'success' => true,
            'categoryID' => $categoryID,
            'categoryName' => $categoryName,
            'description' => trim((string) ($input['description'] ?? '')),
            'status' => (string) ($input['status'] ?? 'active'),
        ];
    }

    public function updateCategory(array $input): array
    {
        $validated = $this->validateCategory($input);
        if (!$validated['success']) {
            return $validated;
        }

        try {
            $updated = $this->fsaCategory->updateCategory((int) $validated['categoryID'], $validated);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the FSA category.'];
        }

        return [
            'success' => $updated,
            'message' => $updated ? 'FSA category updated successfully.' : 'Unable to update the FSA category.',
        ];
    }
}
