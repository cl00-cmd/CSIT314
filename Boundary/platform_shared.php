<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

// BCE note:
// Shared Platform Manager Boundary helper used by the FSA category and report pages.
// It keeps the Platform Manager navigation consistent and uses the shared Boundary/logout.php.
function platform_nav_items(): array
{
    return [
        'dashboard' => ['path' => 'platform_dashboard.php', 'label' => 'Dashboard'],
        'categories' => ['path' => 'FSACategoryUI.php', 'label' => 'Categories'],
        'daily' => ['path' => 'DailyReportUI.php', 'label' => 'Daily Report'],
        'weekly' => ['path' => 'WeeklyReportUI.php', 'label' => 'Weekly Report'],
        'monthly' => ['path' => 'MonthlyReportUI.php', 'label' => 'Monthly Report'],
    ];
}

function render_platform_topbar(string $title, string $activeKey): void
{
    $user = current_user();
    ?>
    <header class="topbar">
        <div class="topbar__brand">
            <h1><?= e($title) ?></h1>
        </div>
        <nav class="topbar__nav" aria-label="Platform manager navigation">
            <?php foreach (platform_nav_items() as $key => $item): ?>
                <a class="button <?= $activeKey === $key ? 'button--primary' : 'button--ghost' ?>" href="<?= e($item['path']) ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="topbar__actions">
            <span class="pill"><?= e($user['full_name'] ?? 'Platform Manager') ?></span>
            <a class="button button--ghost" href="logout.php">Logout</a>
        </div>
    </header>
    <?php
}

function render_platform_flash_if_any(): void
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
