<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// BCE note:
// This file is a shared Fund Raiser Boundary helper used by
// Boundary/fundraiser_dashboard.php, Boundary/FundraisingUI.php,
// Boundary/FRViewsUI.php, Boundary/FRshortlistUI.php, and Boundary/FRHistorySearchUI.php.
// It builds the Fund Raiser navigation and points logout to Boundary/logout.php.
function fundraiser_nav_items(): array
{
    return [
        'dashboard' => ['path' => 'fundraiser_dashboard.php', 'label' => 'Dashboard'],
        'create' => ['path' => 'FundraisingUI.php?command=create', 'label' => 'Create Activity'],
        'search' => ['path' => 'FundraisingUI.php?command=search', 'label' => 'Search Activity'],
        'views' => ['path' => 'FRViewsUI.php', 'label' => 'View Count'],
        'shortlists' => ['path' => 'FRshortlistUI.php', 'label' => 'Shortlists'],
        'history' => ['path' => 'FRHistorySearchUI.php', 'label' => 'History'],
    ];
}

function render_fundraiser_topbar(string $title, string $activeKey): void
{
    $user = current_user();
    ?>
    <header class="topbar">
        <div class="topbar__brand">
            <h1><?= e($title) ?></h1>
        </div>
        <nav class="topbar__nav" aria-label="Fund raiser navigation">
            <?php foreach (fundraiser_nav_items() as $key => $item): ?>
                <a class="button <?= $activeKey === $key ? 'button--primary' : 'button--ghost' ?>" href="<?= e($item['path']) ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="topbar__actions">
            <span class="pill"><?= e($user['full_name'] ?? 'Fund Raiser') ?></span>
            <a class="button button--ghost" href="logout.php">Logout</a>
        </div>
    </header>
    <?php
}

function render_fundraiser_flash_if_any(): void
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
