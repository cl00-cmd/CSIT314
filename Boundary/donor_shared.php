<?php
declare(strict_types=1);

// Load system setup and shared helper functions
require_once __DIR__ . '/../bootstrap.php';

// BCE route:
// Donor Boundary pages use this shared Boundary helper for navigation, flash messages, and logout.
// Logout follows the same shared method as Fund Raiser: Boundary/logout.php clears the session.
function donor_nav_items(): array
{
    return [
        'dashboard' => ['path' => 'donor_dashboard.php', 'label' => 'Dashboard'],
        'search' => ['path' => 'DSearchUI.php', 'label' => 'Search Activity'],
        'favourites' => ['path' => 'DFavouriteUI.php', 'label' => 'Favourite List'],
        'history' => ['path' => 'DonationHistoryUI.php', 'label' => 'Donation History'],
        'progress' => ['path' => 'DonorProgressUI.php', 'label' => 'Progress'],
    ];
}

// Displays the page title, donor navigation buttons, logged-in donor name, and logout button.
function render_donor_topbar(string $title, string $activeKey): void
{
    $user = current_user();
    ?>
    <header class="topbar donor-topbar">
        <div class="topbar__brand">
            <p class="eyebrow">Donor Workspace</p>
            <h1><?= e($title) ?></h1>
        </div>
        <nav class="topbar__nav" aria-label="Donor navigation">
            <?php foreach (donor_nav_items() as $key => $item): ?>
                <a class="button <?= $key === $activeKey ? 'button--primary' : 'button--ghost' ?>" href="<?= e($item['path']) ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="topbar__actions">
            <span class="pill"><?= e($user['full_name'] ?? 'Donor') ?></span>
            <a class="button button--ghost" href="logout.php">Logout</a>
        </div>
    </header>
    <?php
}

// Displays temporary success or error messages.
function render_donor_flash_if_any(): void
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
