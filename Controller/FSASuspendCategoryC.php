<?php
declare(strict_types=1);

namespace App\Controller;

// Load the FSACategory Entity class.
use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category suspension/reactivation.
// This Controller validates the selected category before toggling its status through Entity/FSACategory.php.
final class FSASuspendCategoryC
{
    private FSACategory $fsaCategory;

    // Creates the FSACategory Entity object.
    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    // Validates the selected category ID.
    public function validateRequest(int $categoryID): array
    {
        if ($categoryID <= 0) {
            return [
                'success' => false,
                'message' => 'Please select a valid category to suspend.'
            ];
        }

        return [
            'success' => true,
            'categoryID' => $categoryID
        ];
    }

    // Suspends or reactivates the selected FSA category.
    public function suspendCategory(int $categoryID): array
    {
        $validated = $this->validateRequest($categoryID);

        // Stops the process when validation fails.
        if (!$validated['success']) {
            return $validated;
        }

        // Controller -> Entity to retrieve category details.
        $category = $this->fsaCategory->getCategory($categoryID);

        // Checks whether the category exists.
        if ($category === null) {
            return [
                'success' => false,
                'message' => 'FSA category not found.'
            ];
        }

        // Determines the next category status.
        $targetStatus = ($category['status'] ?? '') === 'suspended'
            ? 'active'
            : 'suspended';

        try {

            // Controller -> Entity to update category status.
            $updated = $this->fsaCategory->setCategoryStatus(
                $categoryID,
                $targetStatus
            );

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to update the FSA category status.'
            ];
        }

        return [
            'success' => $updated,
            'message' => $targetStatus === 'suspended'
                ? 'FSA category suspended successfully.'
                : 'FSA category reactivated successfully.',
        ];
    }
}