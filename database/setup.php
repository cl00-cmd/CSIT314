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
     VALUES (:username, :full_name, :email, :password_hash, :role, "active")'
);
$profileInsert = $pdo->prepare(
    'INSERT INTO user_profiles (user_id, phone, organisation, city, biography, status)
     VALUES (:user_id, :phone, :organisation, :city, :biography, "active")'
);

$users = [
    ['admin01', 'Admin One', 'admin01@example.com', 'user_admin'],
    ['fr01', 'Fund Raiser One', 'fr01@example.com', 'fund_raiser'],
    ['donor01', 'Donor One', 'donor01@example.com', 'donor'],
    ['pm01', 'Platform Manager One', 'pm01@example.com', 'platform_manager'],
];
$userIds = [];
foreach ($users as [$username, $fullName, $email, $role]) {
    $userInsert->execute([
        'username' => $username,
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => $role,
    ]);
    $userId = (int) $pdo->lastInsertId();
    $userIds[$username] = $userId;
    $profileInsert->execute([
        'user_id' => $userId,
        'phone' => '+65 9000 0000',
        'organisation' => 'FundSphere',
        'city' => 'Singapore',
        'biography' => 'Demo profile for the CSIT314 fundraising system.',
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

$campaigns = [
    ['School Supplies Drive', 2, 'Education', 'completed', 5000, 4200, '-90 days', '-30 days'],
    ['Clinic Equipment Fund', 5, 'Health Care', 'completed', 8000, 7600, '-120 days', '-20 days'],
    ['Community Meals', 1, 'Community Support', 'active', 3000, 900, '-15 days', null],
];

foreach ($campaigns as [$title, $categoryId, $serviceType, $status, $goal, $current, $startOffset, $endOffset]) {
    $campaignInsert->execute([
        'fundraiser_user_id' => $userIds['fr01'],
        'category_id' => $categoryId,
        'title' => $title,
        'media' => '',
        'service_type' => $serviceType,
        'story' => 'Demo fundraising activity for local testing.',
        'funding_goal' => $goal,
        'current_amount' => $current,
        'status' => $status,
        'start_date' => date('Y-m-d', strtotime($startOffset)),
        'end_date' => $endOffset === null ? null : date('Y-m-d', strtotime($endOffset)),
    ]);
}

echo "Database schema created and seeded.\n";
echo "Demo logins:\n";
echo "admin01 / password123\n";
echo "fr01 / password123\n";
echo "donor01 / password123\n";
echo "pm01 / password123\n";
