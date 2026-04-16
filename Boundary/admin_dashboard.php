<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UASearchAccController;
use App\Controller\UASearchProfileC;

// Dashboard boundary that summarises the User Admin BCE area and links to each sequence flow.
require_login(['user_admin']);

$searchUserController = new UASearchAccController();
$searchProfileController = new UASearchProfileC();

$accounts = $searchUserController->searchUserAccount();
$profiles = $searchProfileController->searchProfile();
$suspendedAccounts = count(array_filter($accounts, static fn (array $row): bool => ($row['status'] ?? '') === 'suspended'));
$suspendedProfiles = count(array_filter($profiles, static fn (array $row): bool => ($row['status'] ?? '') === 'suspended'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('User Admin Dashboard', 'admin_dashboard.php'); ?>

    <main class="page-shell">
        <?php render_flash_if_any(); ?>

        <section class="stats-grid">
            <article class="stat-card">
                <span>User Accounts</span>
                <strong><?= e((string) count($accounts)) ?></strong>
            </article>
            <article class="stat-card">
                <span>User Profiles</span>
                <strong><?= e((string) count($profiles)) ?></strong>
            </article>
            <article class="stat-card">
                <span>Suspended Accounts</span>
                <strong><?= e((string) $suspendedAccounts) ?></strong>
            </article>
            <article class="stat-card">
                <span>Suspended Profiles</span>
                <strong><?= e((string) $suspendedProfiles) ?></strong>
            </article>
        </section>

        <section class="shortcut-grid">
            <a class="shortcut-card" href="UACreateAccount.php">
                <p class="section-label">Create Account</p>
                <h2>Create user accounts and profiles</h2>
                <p class="muted">UACreateAccount -> UACreateAccountC -> Account</p>
            </a>
            <a class="shortcut-card" href="UASearchAcc.php">
                <p class="section-label">Search / View User Account</p>
                <h2>Search and review user accounts</h2>
                <p class="muted">UASearchAcc and UAViewAcc -> UASearchAccController and UAViewAccC -> Account</p>
            </a>
            <a class="shortcut-card" href="UserAdminPg.php">
                <p class="section-label">Suspend User Account</p>
                <h2>Suspend or reactivate account access</h2>
                <p class="muted">UserAdminPg -> UserAdminC -> UserAccount</p>
            </a>
            <a class="shortcut-card" href="UACreateProfile.php">
                <p class="section-label">Create User Profile</p>
                <h2>Create user profile role types</h2>
                <p class="muted">UACreateProfile -> UACreateProfileC -> Profile</p>
            </a>
            <a class="shortcut-card" href="UASearchProfile.php">
                <p class="section-label">Search / View User Profile</p>
                <h2>Search and review user profiles</h2>
                <p class="muted">UASearchProfile and UAViewProfile -> UASearchProfileC and UAViewProfileC -> Profile</p>
            </a>
            <a class="shortcut-card" href="UASuspendProfile.php">
                <p class="section-label">Suspend User Profile</p>
                <h2>Suspend or reactivate user profiles</h2>
                <p class="muted">UASuspendProfile -> UASuspendProfileC -> Profile</p>
            </a>
        </section>
    </main>
</body>
</html>
