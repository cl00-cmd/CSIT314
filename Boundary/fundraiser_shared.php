<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// Shared helpers for the Fund Raiser boundaries so the BCE pages use one command-style navigation.
function fundraiser_nav_items(): array
{
    return [
        'dashboard' => ['path' => 'fundraiser_dashboard.php', 'label' => 'Dashboard'],
        'create' => ['path' => 'FundraisingUI.php?command=create', 'label' => 'Create Activity'],
        'view' => ['path' => 'FundraisingUI.php?command=view', 'label' => 'View Activity'],
        'update' => ['path' => 'FundraisingUI.php?command=update', 'label' => 'Update Activity'],
        'delete' => ['path' => 'FundraisingUI.php?command=delete', 'label' => 'Delete Activity'],
        'search' => ['path' => 'FundraisingUI.php?command=search', 'label' => 'Search Activity'],
        'views' => ['path' => 'FRViewsUI.php', 'label' => 'View Count'],
        'shortlists' => ['path' => 'FRshortlistUI.php', 'label' => 'Shortlists'],
        'history_service' => ['path' => 'FRHistorySearchUI.php', 'label' => 'History by Service'],
        'history_date' => ['path' => 'FRHistoryDateSearchUI.php', 'label' => 'History by Date'],
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
            <a class="button button--ghost" href="FRLogoutUI.php">Logout</a>
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
