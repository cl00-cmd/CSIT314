<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\CategoryEntity;
use App\Entity\ReportEntity;

final class PlatformController
{
    private CategoryEntity $categoryEntity;
    private ReportEntity $reportEntity;

    public function __construct()
    {
        $this->categoryEntity = new CategoryEntity();
        $this->reportEntity = new ReportEntity();
    }

    public function getDashboardData(array $filters = []): array
    {
        $period = $filters['period'] ?? 'monthly';
        $search = trim((string) ($filters['search'] ?? ''));

        return [
            'period' => $period,
            'categories' => $this->categoryEntity->getAll($search),
            'summary' => $this->reportEntity->getSummary($period),
            'breakdown' => $this->reportEntity->getCategoryBreakdown($period),
        ];
    }

    public function getCategory(int $categoryId): ?array
    {
        return $this->categoryEntity->getById($categoryId);
    }

    public function createCategory(array $input): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            return ['success' => false, 'message' => 'Category name is required.'];
        }

        try {
            $this->categoryEntity->create([
                'name' => $name,
                'description' => trim((string) ($input['description'] ?? '')),
                'status' => (string) ($input['status'] ?? 'active'),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to create the category.'];
        }

        return ['success' => true, 'message' => 'Category created successfully.'];
    }

    public function updateCategory(array $input): array
    {
        $categoryId = (int) ($input['category_id'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));
        if ($categoryId <= 0 || $name === '') {
            return ['success' => false, 'message' => 'Please select a valid category to update.'];
        }

        try {
            $this->categoryEntity->update($categoryId, [
                'name' => $name,
                'description' => trim((string) ($input['description'] ?? '')),
                'status' => (string) ($input['status'] ?? 'active'),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to update the category.'];
        }

        return ['success' => true, 'message' => 'Category updated successfully.'];
    }
}
