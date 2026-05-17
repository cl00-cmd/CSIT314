<?php
declare(strict_types=1);

namespace App\Controller;

// Load Entity classes used by this Controller.
use App\Entity\CategoryEntity;
use App\Entity\ReportEntity;

// BCE route:
// Boundary/platform_dashboard.php calls this Controller.
// This Controller calls Entity/CategoryEntity.php and Entity/ReportEntity.php.
final class PlatformController
{
    private CategoryEntity $categoryEntity;
    private ReportEntity $reportEntity;

    // Creates Entity objects for category and report data.
    public function __construct()
    {
        // Controller -> Entity for category management.
        $this->categoryEntity = new CategoryEntity();

        // Controller -> Entity for daily/weekly/monthly report data.
        $this->reportEntity = new ReportEntity();
    }

    // Retrieves dashboard summary, category list, and report breakdown data.
    public function getDashboardData(array $filters = []): array
    {
        $period = $filters['period'] ?? 'monthly';
        $search = trim((string) ($filters['search'] ?? ''));

        return [
            'period' => $period,

            // Controller -> Entity to retrieve category records.
            'categories' => $this->categoryEntity->getAll($search),

            // Controller -> Entity to retrieve report summary.
            'summary' => $this->reportEntity->getSummary($period),

            // Controller -> Entity to retrieve category report breakdown.
            'breakdown' => $this->reportEntity->getCategoryBreakdown($period),
        ];
    }

    // Retrieves one selected category record.
    public function getCategory(int $categoryId): ?array
    {
        // Controller -> Entity to retrieve category details.
        return $this->categoryEntity->getById($categoryId);
    }

    // Validates and creates a new category.
    public function createCategory(array $input): array
    {
        $name = trim((string) ($input['name'] ?? ''));

        if ($name === '') {
            return [
                'success' => false,
                'message' => 'Category name is required.'
            ];
        }

        try {

            // Controller -> Entity to create category.
            $this->categoryEntity->create([
                'name' => $name,
                'description' => trim((string) ($input['description'] ?? '')),
                'status' => (string) ($input['status'] ?? 'active'),
            ]);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to create the category.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Category created successfully.'
        ];
    }

    // Validates and updates an existing category.
    public function updateCategory(array $input): array
    {
        $categoryId = (int) ($input['category_id'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));

        if ($categoryId <= 0 || $name === '') {
            return [
                'success' => false,
                'message' => 'Please select a valid category to update.'
            ];
        }

        try {

            // Controller -> Entity to update category details.
            $this->categoryEntity->update($categoryId, [
                'name' => $name,
                'description' => trim((string) ($input['description'] ?? '')),
                'status' => (string) ($input['status'] ?? 'active'),
            ]);

        } catch (\Throwable) {
            return [
                'success' => false,
                'message' => 'Unable to update the category.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Category updated successfully.'
        ];
    }
}