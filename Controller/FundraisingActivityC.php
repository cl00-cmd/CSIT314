<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\FundraisingActivity;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingActivityC.php -> Entity/FundraisingActivity.php.
// This Controller validates and creates a new Fund Raiser activity.
final class FundraisingActivityC
{
    private FundraisingActivity $fundraisingActivity;

    public function __construct()
    {
        $this->fundraisingActivity = new FundraisingActivity();
    }

    public function validateDetails(array $input, bool $requireFutureStartDate = false): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $media = trim((string) ($input['media'] ?? ''));
        $goalAmount = (int) ($input['goal_amount'] ?? 0);
        $categoryId = (int) ($input['category_id'] ?? 0);
        $serviceType = trim((string) ($input['service_type'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'active'));
        $startDate = trim((string) ($input['start_date'] ?? ''));
        $endDate = trim((string) ($input['end_date'] ?? ''));

        if ($title === '' || $description === '' || $goalAmount <= 0 || $categoryId <= 0 || $serviceType === '' || $startDate === '') {
            return ['success' => false, 'message' => 'Please complete all required fundraising activity details.'];
        }

        if (!in_array($serviceType, $this->fundraisingActivity->listServiceTypes(), true)) {
            return ['success' => false, 'message' => 'Please select a valid service type.'];
        }

        if (!in_array($status, ['active', 'paused', 'completed'], true)) {
            return ['success' => false, 'message' => 'Please select a valid status.'];
        }

        if (strtotime($startDate) === false) {
            return ['success' => false, 'message' => 'Please enter a valid start date.'];
        }

        if ($requireFutureStartDate && $startDate < date('Y-m-d')) {
            return ['success' => false, 'message' => 'Start date cannot be before the current date.'];
        }

        if ($endDate !== '' && strtotime($endDate) !== false && strtotime($startDate) !== false && strtotime($endDate) < strtotime($startDate)) {
            return ['success' => false, 'message' => 'End date cannot be earlier than start date.'];
        }

        return [
            'success' => true,
            'data' => [
                'category_id' => $categoryId,
                'title' => $title,
                'story' => $description,
                'service_type' => $serviceType,
                'funding_goal' => $goalAmount,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'media' => $media,
            ],
        ];
    }

    public function createActivity(int $fundraiserUserId, array $details): bool
    {
        return $this->fundraisingActivity->setDetails($fundraiserUserId, $details);
    }

    public function saveActivity(int $fundraiserUserId, array $input): array
    {
        $validated = $this->validateDetails($input, true);
        if (!$validated['success']) {
            return $validated;
        }

        try {
            $saved = $this->createActivity($fundraiserUserId, $validated['data']);
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Unable to create the fundraising activity.'];
        }

        return [
            'success' => $saved,
            'message' => $saved ? 'Fundraising activity created successfully.' : 'Unable to create the fundraising activity.',
        ];
    }

    public function getFormOptions(): array
    {
        return [
            'categories' => $this->fundraisingActivity->listCategories(),
            'serviceTypes' => $this->fundraisingActivity->listServiceTypes(),
        ];
    }
}
