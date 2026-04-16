<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;

$pdo = Database::getConnection();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['donations', 'campaign_views', 'favourites', 'campaigns', 'user_profiles', 'users', 'categories', 'profile_types'] as $table) {
    $pdo->exec('DROP TABLE IF EXISTS ' . $table);
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

Database::runSchemaFile(__DIR__ . '/schema.sql');

$columnCheck = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = :table_name
       AND COLUMN_NAME = :column_name'
);
$columnCheck->execute([
    'table_name' => 'user_profiles',
    'column_name' => 'status',
]);
if ((int) $columnCheck->fetchColumn() === 0) {
    $pdo->exec("ALTER TABLE user_profiles ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER biography");
}

$tableCheck = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = :table_name'
);
$tableCheck->execute(['table_name' => 'profile_types']);
if ((int) $tableCheck->fetchColumn() === 0) {
    $pdo->exec(
        "CREATE TABLE profile_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_code VARCHAR(50) NOT NULL UNIQUE,
            role_label VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['donations', 'campaign_views', 'favourites', 'campaigns', 'user_profiles', 'users', 'categories', 'profile_types'] as $table) {
    $pdo->exec('TRUNCATE TABLE ' . $table);
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$firstNames = ['Ava', 'Liam', 'Noah', 'Mia', 'Sofia', 'Ethan', 'Zara', 'Amir', 'Grace', 'Daniel', 'Nora', 'Ryan'];
$lastNames = ['Tan', 'Lim', 'Wong', 'Lee', 'Smith', 'Jones', 'Ali', 'Ng', 'Khan', 'Goh', 'Chong', 'Brown'];
$cities = ['Singapore', 'Johor Bahru', 'Kuala Lumpur', 'Sydney', 'Melbourne', 'Perth', 'Wollongong', 'Canberra'];
$organisations = ['HopeLink', 'CareBridge', 'BrightHands', 'BlueHarbour', 'GreenSteps', 'YouthSpark', 'UnityAid'];
$serviceTypes = ['Community Support', 'Education', 'Emergency Relief', 'Environment', 'Health Care', 'Shelter and Housing'];
$passwordHash = password_hash('password123', PASSWORD_DEFAULT);

$profileTypeInsert = $pdo->prepare(
    'INSERT INTO profile_types (role_code, role_label, status)
     VALUES (:role_code, :role_label, :status)'
);
foreach ([
    ['role_code' => 'user_admin', 'role_label' => 'User Admin', 'status' => 'active'],
    ['role_code' => 'fund_raiser', 'role_label' => 'Fund Raiser', 'status' => 'active'],
    ['role_code' => 'donor', 'role_label' => 'Donor', 'status' => 'active'],
    ['role_code' => 'platform_manager', 'role_label' => 'Platform Manager', 'status' => 'active'],
] as $roleRow) {
    $profileTypeInsert->execute($roleRow);
}

$categoryInsert = $pdo->prepare(
    'INSERT INTO categories (name, description, status) VALUES (:name, :description, :status)'
);
for ($i = 1; $i <= 100; $i++) {
    $categoryInsert->execute([
        'name' => sprintf('Category %03d', $i),
        'description' => 'Demo category generated for large-scale project testing.',
        'status' => $i % 10 === 0 ? 'inactive' : 'active',
    ]);
}

$userInsert = $pdo->prepare(
    'INSERT INTO users (username, full_name, email, password_hash, role, status)
     VALUES (:username, :full_name, :email, :password_hash, :role, :status)'
);
$profileInsert = $pdo->prepare(
    'INSERT INTO user_profiles (user_id, phone, organisation, city, biography, status)
     VALUES (:user_id, :phone, :organisation, :city, :biography, :status)'
);

$userIdsByRole = [
    'user_admin' => [],
    'fund_raiser' => [],
    'donor' => [],
    'platform_manager' => [],
];

$seedUsers = [
    ['username' => 'admin01', 'full_name' => 'Admin One', 'email' => 'admin01@example.com', 'role' => 'user_admin'],
    ['username' => 'admin02', 'full_name' => 'Admin Two', 'email' => 'admin02@example.com', 'role' => 'user_admin'],
    ['username' => 'fr01', 'full_name' => 'Fund Raiser One', 'email' => 'fr01@example.com', 'role' => 'fund_raiser'],
    ['username' => 'donor01', 'full_name' => 'Donor One', 'email' => 'donor01@example.com', 'role' => 'donor'],
    ['username' => 'pm01', 'full_name' => 'Platform Manager One', 'email' => 'pm01@example.com', 'role' => 'platform_manager'],
];

$roleTargets = [
    'user_admin' => 5,
    'fund_raiser' => 30,
    'donor' => 55,
    'platform_manager' => 10,
];

$allUsers = $seedUsers;
foreach ($roleTargets as $role => $count) {
    $existing = count(array_filter($seedUsers, static fn (array $user): bool => $user['role'] === $role));
    for ($i = $existing + 1; $i <= $count; $i++) {
        $fullName = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
        $username = match ($role) {
            'user_admin' => sprintf('admin%02d', $i),
            'fund_raiser' => sprintf('fr%02d', $i),
            'donor' => sprintf('donor%02d', $i),
            default => sprintf('pm%02d', $i),
        };
        $allUsers[] = [
            'username' => $username,
            'full_name' => $fullName,
            'email' => $username . '@example.com',
            'role' => $role,
        ];
    }
}

foreach ($allUsers as $index => $user) {
    $accountStatus = ($index % 17 === 0 && $user['role'] !== 'user_admin') ? 'suspended' : 'active';
    $profileStatus = ($index % 19 === 0 && $user['role'] !== 'user_admin') ? 'suspended' : 'active';

    $userInsert->execute([
        'username' => $user['username'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'password_hash' => $passwordHash,
        'role' => $user['role'],
        'status' => $accountStatus,
    ]);

    $userId = (int) $pdo->lastInsertId();
    $profileInsert->execute([
        'user_id' => $userId,
        'phone' => '+65 9' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
        'organisation' => $organisations[array_rand($organisations)],
        'city' => $cities[array_rand($cities)],
        'biography' => 'Demo profile generated for BCE fundraising project testing.',
        'status' => $profileStatus,
    ]);

    $userIdsByRole[$user['role']][] = $userId;
}

$campaignInsert = $pdo->prepare(
    'INSERT INTO campaigns (
        fundraiser_user_id, category_id, title, service_type, story,
        funding_goal, current_amount, status, start_date, end_date
     ) VALUES (
        :fundraiser_user_id, :category_id, :title, :service_type, :story,
        :funding_goal, 0, :status, :start_date, :end_date
     )'
);

$campaignIds = [];
for ($i = 1; $i <= 120; $i++) {
    $status = $i % 5 === 0 ? 'completed' : ($i % 9 === 0 ? 'paused' : 'active');
    $startDate = date('Y-m-d', strtotime('-' . random_int(5, 180) . ' days'));
    $endDate = $status === 'completed' ? date('Y-m-d', strtotime($startDate . ' +' . random_int(5, 45) . ' days')) : null;
    $campaignInsert->execute([
        'fundraiser_user_id' => $userIdsByRole['fund_raiser'][array_rand($userIdsByRole['fund_raiser'])],
        'category_id' => random_int(1, 100),
        'title' => sprintf('Campaign %03d', $i),
        'service_type' => $serviceTypes[array_rand($serviceTypes)],
        'story' => 'This is a large demo fundraising activity generated for system testing, filtering, and reporting.',
        'funding_goal' => random_int(5000, 50000),
        'status' => $status,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
    $campaignIds[] = (int) $pdo->lastInsertId();
}

$favouriteInsert = $pdo->prepare(
    'INSERT IGNORE INTO favourites (donor_user_id, campaign_id) VALUES (:donor_user_id, :campaign_id)'
);
for ($i = 0; $i < 180; $i++) {
    $favouriteInsert->execute([
        'donor_user_id' => $userIdsByRole['donor'][array_rand($userIdsByRole['donor'])],
        'campaign_id' => $campaignIds[array_rand($campaignIds)],
    ]);
}

$viewInsert = $pdo->prepare(
    'INSERT INTO campaign_views (campaign_id, viewer_user_id) VALUES (:campaign_id, :viewer_user_id)'
);
for ($i = 0; $i < 300; $i++) {
    $viewInsert->execute([
        'campaign_id' => $campaignIds[array_rand($campaignIds)],
        'viewer_user_id' => $userIdsByRole['donor'][array_rand($userIdsByRole['donor'])],
    ]);
}

$donationInsert = $pdo->prepare(
    'INSERT INTO donations (campaign_id, donor_user_id, amount, message) VALUES (:campaign_id, :donor_user_id, :amount, :message)'
);
$campaignUpdate = $pdo->prepare(
    'UPDATE campaigns SET current_amount = current_amount + :amount WHERE id = :campaign_id'
);
for ($i = 0; $i < 220; $i++) {
    $campaignId = $campaignIds[array_rand($campaignIds)];
    $amount = random_int(20, 800);
    $donationInsert->execute([
        'campaign_id' => $campaignId,
        'donor_user_id' => $userIdsByRole['donor'][array_rand($userIdsByRole['donor'])],
        'amount' => $amount,
        'message' => 'Demo donation generated for reporting and history filters.',
    ]);
    $campaignUpdate->execute([
        'campaign_id' => $campaignId,
        'amount' => $amount,
    ]);
}

echo "Database schema created and seeded.\n";
echo "Users: 100\n";
echo "Categories: 100\n";
echo "Campaigns: 120\n";
echo "Favourites: 180+\n";
echo "Views: 300\n";
echo "Donations: 220\n";
echo "\nDemo logins:\n";
echo "admin01 / password123\n";
echo "fr01 / password123\n";
echo "donor01 / password123\n";
echo "pm01 / password123\n";
