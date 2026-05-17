<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FSACategory Entity class.
use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category updates.
// This Controller validates update data before calling Entity/FSACategory.php.
final class FSAUpdateCategoryC
{
    private FSACategory $fsaCategory;

    // Creates the FSACategory Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    // Validates the category update input fields.
    public function validateCategory(array $input): array
    {
        $categoryID = (int) ($input['categoryID'] ?? 0);
        $categoryName = trim((string) ($input['categoryName'] ?? ''));

        if ($categoryID <= 0 || $categoryName === '') {
            return [
                'success' => false,
                'message' => 'Please select a valid category to update.'
            ];
        }

        return [
            'success' => true,
            'categoryID' => $categoryID,
            'categoryName' => $categoryName,
            'description' => trim((string) ($input['description'] ?? '')),
            'status' => (string) ($input['status'] ?? 'active'),
        ];
    }

    // Updates the selected FSA category after validation.
    public function updateCategory(array $input): array
    {
        $validated = $this->validateCategory($input);

        if (!$validated['success']) {
            return $validated;
        }

        try {
            // Controller -> Entity to update category details.
            $updated = $this->fsaCategory->updateCategory(
                (int) $validated['categoryID'],
                $validated
            );
        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to update the FSA category.'
            ];
        }

        return [
            'success' => $updated,
            'message' => $updated
                ? 'FSA category updated successfully.'
                : 'Unable to update the FSA category.',
        ];
    }
}