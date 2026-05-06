<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FSACategory;

// BCE route:
// Boundary/FSACategoryUI.php calls this Controller for category suspension/reactivation.
// This Controller validates the selected category before toggling its status through Entity/FSACategory.php.
final class FSASuspendCategoryC
{
    private FSACategory $fsaCategory;

    public function __construct()
    {
        // Controller -> Entity.
        $this->fsaCategory = new FSACategory();
    }

    public function validateRequest(int $categoryID): array
    {
        if ($categoryID <= 0) {
            return ['success' => false, 'message' => 'Please select a valid category to suspend.'];
        }

        return ['success' => true, 'categoryID' => $categoryID];
    }

    public function suspendCategory(int $categoryID): array
    {
        $validated = $this->validateRequest($categoryID);
        if (!$validated['success']) {
            return $validated;
        }

        $category = $this->fsaCategory->getCategory($categoryID);
        if ($category === null) {
            return ['success' => false, 'message' => 'FSA category not found.'];
        }

        $targetStatus = ($category['status'] ?? '') === 'suspended' ? 'active' : 'suspended';

        try {
            $updated = $this->fsaCategory->setCategoryStatus($categoryID, $targetStatus);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the FSA category status.'];
        }

        return [
            'success' => $updated,
            'message' => $targetStatus === 'suspended'
                ? 'FSA category suspended successfully.'
                : 'FSA category reactivated successfully.',
        ];
    }
}
