<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controller\FundraiserController;

require_login(['fund_raiser']);

$controller = new FundraiserController();
$user = current_user();
$userId = (int) $user['id'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $result = match ($action) {
        'create_campaign' => $controller->createCampaign($userId, $_POST),
        'update_campaign' => $controller->updateCampaign($userId, $_POST),
        default => ['success' => false, 'message' => 'Unknown action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('fundraiser_dashboard.php');
}

$filters = [
    'service_type' => trim((string) ($_GET['service_type'] ?? '')),
    'from' => (string) ($_GET['from'] ?? ''),
    'to' => (string) ($_GET['to'] ?? ''),
];
$dashboard = $controller->getDashboardData($userId, $filters);
$editCampaign = isset($_GET['edit_id']) ? $controller->getCampaignForEdit($userId, (int) $_GET['edit_id']) : null;
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Raiser Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <header class="topbar">
        <div>
            <p class="eyebrow">Fund Raiser Workspace</p>
            <h1>Manage Fundraising Activity</h1>
        </div>
        <nav class="topbar__nav">
            <span class="pill">Signed in as <?= e($user['full_name']) ?></span>
            <a class="button button--ghost" href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="page-shell">
        <?php if ($flash !== null): ?>
            <div class="<?= e(flash_class($flash['type'] ?? null)) ?>">
                <?= e($flash['message'] ?? '') ?>
            </div>
        <?php endif; ?>

        <section class="stats-grid">
            <article class="stat-card">
                <span>Total Campaigns</span>
                <strong><?= e((string) $dashboard['summary']['total_campaigns']) ?></strong>
            </article>
            <article class="stat-card">
                <span>Total Views</span>
                <strong><?= e((string) $dashboard['summary']['total_views']) ?></strong>
            </article>
            <article class="stat-card">
                <span>Total Shortlists</span>
                <strong><?= e((string) $dashboard['summary']['total_shortlists']) ?></strong>
            </article>
            <article class="stat-card">
                <span>Total Raised</span>
                <strong><?= e(format_currency($dashboard['summary']['total_raised'])) ?></strong>
            </article>
        </section>

        <section class="layout-grid">
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Create FSA</p>
                        <h2>Launch a new fundraising activity</h2>
                    </div>
                </div>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="create_campaign">

                    <label class="field field--full">
                        <span>Title</span>
                        <input type="text" name="title" required>
                    </label>
                    <label class="field">
                        <span>Category</span>
                        <select name="category_id" required>
                            <?php foreach ($dashboard['categories'] as $category): ?>
                                <option value="<?= e((string) $category['id']) ?>"><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Service Type</span>
                        <select name="service_type" required>
                            <?php foreach ($dashboard['service_types'] as $serviceType): ?>
                                <option value="<?= e($serviceType) ?>"><?= e($serviceType) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Funding Goal</span>
                        <input type="number" min="1" step="0.01" name="funding_goal" required>
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <option value="active">active</option>
                            <option value="paused">paused</option>
                            <option value="completed">completed</option>
                        </select>
                    </label>
                    <label class="field">
                        <span>Start Date</span>
                        <input type="date" name="start_date" required>
                    </label>
                    <label class="field">
                        <span>End Date</span>
                        <input type="date" name="end_date">
                    </label>
                    <label class="field field--full">
                        <span>Story</span>
                        <textarea name="story" rows="5" required></textarea>
                    </label>

                    <button type="submit" class="button button--primary">Create Campaign</button>
                </form>
            </section>

            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Update FSA</p>
                        <h2>Edit an existing fundraising activity</h2>
                    </div>
                    <?php if ($editCampaign !== null): ?>
                        <span class="pill">Editing campaign #<?= e((string) $editCampaign['id']) ?></span>
                    <?php endif; ?>
                </div>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="update_campaign">
                    <input type="hidden" name="campaign_id" value="<?= e((string) ($editCampaign['id'] ?? '')) ?>">

                    <label class="field field--full">
                        <span>Title</span>
                        <input type="text" name="title" value="<?= e($editCampaign['title'] ?? '') ?>" required>
                    </label>
                    <label class="field">
                        <span>Category</span>
                        <select name="category_id" required>
                            <?php foreach ($dashboard['categories'] as $category): ?>
                                <option value="<?= e((string) $category['id']) ?>" <?= selected_if($editCampaign['category_id'] ?? '', $category['id']) ?>>
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Service Type</span>
                        <select name="service_type" required>
                            <?php foreach ($dashboard['service_types'] as $serviceType): ?>
                                <option value="<?= e($serviceType) ?>" <?= selected_if($editCampaign['service_type'] ?? '', $serviceType) ?>>
                                    <?= e($serviceType) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Funding Goal</span>
                        <input type="number" min="1" step="0.01" name="funding_goal" value="<?= e((string) ($editCampaign['funding_goal'] ?? '')) ?>" required>
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <?php foreach (['active', 'paused', 'completed'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= selected_if($editCampaign['status'] ?? '', $status) ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Start Date</span>
                        <input type="date" name="start_date" value="<?= e($editCampaign['start_date'] ?? '') ?>" required>
                    </label>
                    <label class="field">
                        <span>End Date</span>
                        <input type="date" name="end_date" value="<?= e($editCampaign['end_date'] ?? '') ?>">
                    </label>
                    <label class="field field--full">
                        <span>Story</span>
                        <textarea name="story" rows="5" required><?= e($editCampaign['story'] ?? '') ?></textarea>
                    </label>

                    <button type="submit" class="button button--primary">Update Campaign</button>
                </form>
            </section>
        </section>

        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">Active Activities</p>
                    <h2>Track views, shortlists, and progress</h2>
                </div>
            </div>

            <div class="campaign-grid">
                <?php foreach ($dashboard['campaigns'] as $campaign): ?>
                    <article class="campaign-card">
                        <div class="campaign-card__top">
                            <div>
                                <h3><?= e($campaign['title']) ?></h3>
                                <p class="muted"><?= e($campaign['category_name']) ?> · <?= e($campaign['service_type']) ?></p>
                            </div>
                            <span class="pill"><?= e($campaign['status']) ?></span>
                        </div>
                        <p><?= e($campaign['story']) ?></p>
                        <div class="campaign-meta">
                            <span>Views: <?= e((string) $campaign['view_count']) ?></span>
                            <span>Shortlists: <?= e((string) $campaign['shortlist_count']) ?></span>
                            <span>Raised: <?= e(format_currency($campaign['current_amount'])) ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress__bar" style="width: <?= e((string) progress_percent($campaign)) ?>%"></div>
                        </div>
                        <a class="button button--ghost button--small" href="?edit_id=<?= e((string) $campaign['id']) ?>">Edit</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <p class="section-label">Completed History</p>
                    <h2>Search previous completed FSA by service and date period</h2>
                </div>

                <form method="get" class="inline-filters">
                    <select name="service_type">
                        <option value="">All services</option>
                        <?php foreach ($dashboard['service_types'] as $serviceType): ?>
                            <option value="<?= e($serviceType) ?>" <?= selected_if($filters['service_type'], $serviceType) ?>>
                                <?= e($serviceType) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="from" value="<?= e($filters['from']) ?>">
                    <input type="date" name="to" value="<?= e($filters['to']) ?>">
                    <button type="submit" class="button button--ghost">Filter</button>
                </form>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Service</th>
                            <th>Dates</th>
                            <th>Views</th>
                            <th>Shortlists</th>
                            <th>Raised</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard['history'] as $history): ?>
                            <tr>
                                <td><?= e($history['title']) ?></td>
                                <td><?= e($history['service_type']) ?></td>
                                <td><?= e(format_date($history['start_date'])) ?> - <?= e(format_date($history['end_date'])) ?></td>
                                <td><?= e((string) $history['view_count']) ?></td>
                                <td><?= e((string) $history['shortlist_count']) ?></td>
                                <td><?= e(format_currency($history['current_amount'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
