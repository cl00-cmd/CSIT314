<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// Shared helpers for the User Admin boundaries so navigation and flash messages stay consistent.
function admin_nav_items(): array
{
    return [
        'admin_dashboard.php' => 'Dashboard',
        'UACreateAccount.php' => 'Create Account',
        'UASearchAcc.php' => 'Search Accounts',
        'UserAdminPg.php' => 'Suspend Account',
        'UACreateProfile.php' => 'Create Profile',
        'UASearchProfile.php' => 'Search Profiles',
        'UASuspendProfile.php' => 'Suspend Profile',
    ];
}

function render_admin_topbar(string $title, string $activePage): void
{
    $user = current_user();
    ?>
    <header class="topbar">
        <div class="topbar__brand">
            <p class="eyebrow">User Admin BCE</p>
            <h1><?= e($title) ?></h1>
        </div>
        <nav class="topbar__nav" aria-label="User admin navigation">
            <?php foreach (admin_nav_items() as $path => $label): ?>
                <a class="button <?= $activePage === $path ? 'button--primary' : 'button--ghost' ?>" href="<?= e($path) ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="topbar__actions">
            <span class="pill"><?= e($user['full_name'] ?? 'User Admin') ?></span>
            <a class="button button--ghost" href="logout.php">Logout</a>
        </div>
    </header>
    <?php
}

function render_flash_if_any(): void
{
    $flash = pull_flash();
    if ($flash === null) {
        return;
    }
    ?>
    <div class="<?= e(flash_class($flash['type'] ?? null)) ?>">
        <?= e($flash['message'] ?? '') ?>
    </div>
    <?php
}
