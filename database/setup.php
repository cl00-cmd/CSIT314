<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Config\Database;

$pdo = Database::getConnection();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['donations', 'campaign_views', 'favourites', 'campaigns', 'user_profiles', 'users', 'categories', 'profile_types'] as $table) {
    $pdo->exec('DROP TABLE IF EXISTS ' . $table);
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

Database::runSchemaFile(__DIR__ . '/schema.sql');

$passwordHash = password_hash('password123', PASSWORD_DEFAULT);
$profileTypes = [
    ['user_admin', 'User Admin'],
    ['fund_raiser', 'Fund Raiser'],
    ['donor', 'Donor'],
    ['platform_manager', 'Platform Manager'],
];

$profileTypeInsert = $pdo->prepare(
    'INSERT INTO profile_types (role_code, role_label, status) VALUES (:role_code, :role_label, "active")'
);
foreach ($profileTypes as [$roleCode, $roleLabel]) {
    $profileTypeInsert->execute([
        'role_code' => $roleCode,
        'role_label' => $roleLabel,
    ]);
}

$categoryInsert = $pdo->prepare(
    'INSERT INTO categories (name, description, status) VALUES (:name, :description, "active")'
);
foreach (['Community Support', 'Education', 'Emergency Relief', 'Environment', 'Health Care', 'Shelter and Housing'] as $category) {
    $categoryInsert->execute([
        'name' => $category,
        'description' => $category . ' fundraising activities.',
    ]);
}

$userInsert = $pdo->prepare(
    'INSERT INTO users (username, full_name, email, password_hash, role, status)
     VALUES (:username, :full_name, :email, :password_hash, :role, :status)'
);
$profileInsert = $pdo->prepare(
    'INSERT INTO user_profiles (user_id, phone, organisation, city, biography, status)
     VALUES (:user_id, :phone, :organisation, :city, :biography, "active")'
);

$users = [];

for ($i = 1; $i <= 3; $i++) {
    $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $users[] = ["admin{$suffix}", "User Admin {$suffix}", "admin{$suffix}@example.com", 'user_admin', 'active'];
}

for ($i = 1; $i <= 5; $i++) {
    $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $users[] = ["pm{$suffix}", "Platform Manager {$suffix}", "pm{$suffix}@example.com", 'platform_manager', 'active'];
}

for ($i = 1; $i <= 46; $i++) {
    $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $users[] = ["fr{$suffix}", "Fund Raiser {$suffix}", "fr{$suffix}@example.com", 'fund_raiser', 'active'];
}

for ($i = 1; $i <= 46; $i++) {
    $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $users[] = ["donor{$suffix}", "Donor {$suffix}", "donor{$suffix}@example.com", 'donor', 'active'];
}

$userIds = [];
foreach ($users as [$username, $fullName, $email, $role, $status]) {
    $userInsert->execute([
        'username' => $username,
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => $role,
        'status' => $status,
    ]);
    $userId = (int) $pdo->lastInsertId();
    $userIds[$username] = $userId;
    $profileInsert->execute([
        'user_id' => $userId,
        'phone' => '+65 9' . str_pad((string) $userId, 7, '0', STR_PAD_LEFT),
        'organisation' => match ($role) {
            'user_admin' => 'FundSphere User Operations',
            'platform_manager' => 'FundSphere Platform Team',
            'fund_raiser' => 'Community Partner ' . str_pad((string) (($userId % 12) + 1), 2, '0', STR_PAD_LEFT),
            default => 'Individual Supporter',
        },
        'city' => 'Singapore',
        'biography' => $fullName . ' test profile for the CSIT314 fundraising system.',
    ]);
}

$campaignInsert = $pdo->prepare(
    'INSERT INTO campaigns (
        fundraiser_user_id, category_id, title, media, service_type, story,
        funding_goal, current_amount, status, start_date, end_date
     ) VALUES (
        :fundraiser_user_id, :category_id, :title, :media, :service_type, :story,
        :funding_goal, :current_amount, :status, :start_date, :end_date
     )'
);

$campaignThemes = [
    ['School Supplies Drive', 2, 'Education'],
    ['Clinic Equipment Fund', 5, 'Health Care'],
    ['Community Meals', 1, 'Community Support'],
    ['Flood Recovery Kits', 3, 'Emergency Relief'],
    ['Urban Garden Renewal', 4, 'Environment'],
    ['Family Shelter Support', 6, 'Shelter and Housing'],
    ['After School Mentoring', 2, 'Education'],
    ['Senior Wellness Visits', 5, 'Health Care'],
    ['Neighbourhood Food Pantry', 1, 'Community Support'],
    ['Clean Water Response', 3, 'Emergency Relief'],
];

$campaignIds = [];
for ($i = 1; $i <= 46; $i++) {
    $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $fundraiserId = $userIds["fr{$suffix}"];

    for ($campaignNumber = 1; $campaignNumber <= 2; $campaignNumber++) {
        $theme = $campaignThemes[($i + $campaignNumber - 2) % count($campaignThemes)];
        [$baseTitle, $categoryId, $serviceType] = $theme;
        $status = ($i + $campaignNumber) % 4 === 0 ? 'completed' : 'active';
        $startOffset = '-' . (20 + $i + ($campaignNumber * 9)) . ' days';
        $endOffset = $status === 'completed' ? '-' . (2 + ($i % 18)) . ' days' : null;
        $goal = 2500 + (($i + $campaignNumber) * 175);

        $title = $baseTitle . ' ' . $suffix . '-' . $campaignNumber;
        $story = sprintf(
            '%s is coordinating %s with local volunteers, donor updates, and tracked progress for test data review.',
            "Fund Raiser {$suffix}",
            strtolower($baseTitle)
        );

        $campaignInsert->execute([
            'fundraiser_user_id' => $fundraiserId,
            'category_id' => $categoryId,
            'title' => $title,
            'media' => '',
            'service_type' => $serviceType,
            'story' => $story,
            'funding_goal' => $goal,
            'current_amount' => 0,
            'status' => $status,
            'start_date' => date('Y-m-d', strtotime($startOffset)),
            'end_date' => $endOffset === null ? null : date('Y-m-d', strtotime($endOffset)),
        ]);

        $campaignIds[] = (int) $pdo->lastInsertId();
    }
}

$favouriteInsert = $pdo->prepare(
    'INSERT INTO favourites (donor_user_id, campaign_id, created_at)
     VALUES (:donor_user_id, :campaign_id, :created_at)'
);
$viewInsert = $pdo->prepare(
    'INSERT INTO campaign_views (campaign_id, viewer_user_id, viewed_at)
     VALUES (:campaign_id, :viewer_user_id, :viewed_at)'
);
$donationInsert = $pdo->prepare(
    'INSERT INTO donations (campaign_id, donor_user_id, amount, message, donated_at)
     VALUES (:campaign_id, :donor_user_id, :amount, :message, :donated_at)'
);
$campaignTotalUpdate = $pdo->prepare(
    'UPDATE campaigns
     SET current_amount = current_amount + :amount
     WHERE id = :campaign_id'
);

for ($i = 1; $i <= 46; $i++) {
    $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $donorId = $userIds["donor{$suffix}"];
    $primaryCampaignIndex = ($i * 2) % count($campaignIds);

    for ($activityNumber = 0; $activityNumber < 3; $activityNumber++) {
        $campaignId = $campaignIds[($primaryCampaignIndex + $activityNumber * 7) % count($campaignIds)];
        $viewedAt = date('Y-m-d H:i:s', strtotime('-' . (($i + $activityNumber) % 21) . ' days'));

        $viewInsert->execute([
            'campaign_id' => $campaignId,
            'viewer_user_id' => $donorId,
            'viewed_at' => $viewedAt,
        ]);

        if ($activityNumber < 2) {
            $favouriteInsert->execute([
                'donor_user_id' => $donorId,
                'campaign_id' => $campaignId,
                'created_at' => $viewedAt,
            ]);
        }

        $amount = 25 + (($i + $activityNumber) % 8) * 15;
        $donatedAt = date('Y-m-d H:i:s', strtotime('-' . (($i + $activityNumber * 3) % 28) . ' days'));
        $donationInsert->execute([
            'campaign_id' => $campaignId,
            'donor_user_id' => $donorId,
            'amount' => $amount,
            'message' => 'Test donation from Donor ' . $suffix . ' to show supporter activity.',
            'donated_at' => $donatedAt,
        ]);
        $campaignTotalUpdate->execute([
            'campaign_id' => $campaignId,
            'amount' => $amount,
        ]);
    }
}

echo "Database schema created and seeded.\n";
echo "Seeded 100 users: 3 user admins, 5 platform managers, 46 fund raisers, and 46 donors.\n";
echo "Seeded " . count($campaignIds) . " fundraising activities plus donor donations, favourites, and views.\n";
echo "Demo logins:\n";
echo "admin01 / password123\n";
echo "fr01 / password123\n";
echo "donor01 / password123\n";
echo "pm01 / password123\n";
