<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controller\DoneeController;

require_login(['donee']);

$controller = new DoneeController();
$user = current_user();
$userId = (int) $user['id'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $campaignId = (int) ($_POST['campaign_id'] ?? 0);

    $result = match ($action) {
        'save_favourite' => $controller->saveFavourite($userId, $campaignId),
        'remove_favourite' => $controller->removeFavourite($userId, $campaignId),
        'donate' => $controller->donate($userId, $_POST),
        default => ['success' => false, 'message' => 'Unknown action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('donee_dashboard.php', $campaignId > 0 ? ['campaign_id' => $campaignId] : []);
}

$filters = [
    'search' => trim((string) ($_GET['search'] ?? '')),
    'category_id' => (string) ($_GET['category_id'] ?? ''),
    'from' => (string) ($_GET['from'] ?? ''),
    'to' => (string) ($_GET['to'] ?? ''),
];

$selectedCampaign = isset($_GET['campaign_id']) ? $controller->viewCampaign($userId, (int) $_GET['campaign_id']) : null;
$dashboard = $controller->getDashboardData($userId, $filters);
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donee Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <header class="topbar">
        <div>
            <p class="eyebrow">Donee Workspace</p>
            <h1>Search, save, and donate</h1>
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
                <span>Saved Campaigns</span>
                <strong><?= e((string) $dashboard['summary']['favourite_count']) ?></strong>
            </article>
            <article class="stat-card">
                <span>Total Donations</span>
                <strong><?= e((string) $dashboard['summary']['donation_count']) ?></strong>
            </article>
            <article class="stat-card">
                <span>Donation Value</span>
                <strong><?= e(format_currency($dashboard['summary']['donation_amount'])) ?></strong>
            </article>
        </section>

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <p class="section-label">Discover Campaigns</p>
                    <h2>Search and shortlist fundraising activities</h2>
                </div>

                <form method="get" class="inline-filters">
                    <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Search title, story, service">
                    <select name="category_id">
                        <option value="">All categories</option>
                        <?php foreach ($dashboard['categories'] as $category): ?>
                            <option value="<?= e((string) $category['id']) ?>" <?= selected_if($filters['category_id'], $category['id']) ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="from" value="<?= e($filters['from']) ?>">
                    <input type="date" name="to" value="<?= e($filters['to']) ?>">
                    <button type="submit" class="button button--ghost">Filter</button>
                </form>
            </div>

            <div class="campaign-grid">
                <?php foreach ($dashboard['campaigns'] as $campaign): ?>
                    <article class="campaign-card">
                        <div class="campaign-card__top">
                            <div>
                                <h3><?= e($campaign['title']) ?></h3>
                                <p class="muted"><?= e($campaign['fundraiser_name']) ?> · <?= e($campaign['category_name']) ?></p>
                            </div>
                            <span class="pill"><?= e($campaign['status']) ?></span>
                        </div>
                        <p><?= e(substr($campaign['story'], 0, 160)) ?>...</p>
                        <div class="campaign-meta">
                            <span>Views: <?= e((string) $campaign['view_count']) ?></span>
                            <span>Shortlists: <?= e((string) $campaign['shortlist_count']) ?></span>
                            <span>Raised: <?= e(format_currency($campaign['current_amount'])) ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress__bar" style="width: <?= e((string) progress_percent($campaign)) ?>%"></div>
                        </div>
                        <div class="action-row">
                            <a class="button button--ghost button--small" href="?campaign_id=<?= e((string) $campaign['id']) ?>">View</a>
                            <?php if ((int) $campaign['is_favourite'] === 1): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="remove_favourite">
                                    <input type="hidden" name="campaign_id" value="<?= e((string) $campaign['id']) ?>">
                                    <button type="submit" class="button button--ghost button--small">Remove Favourite</button>
                                </form>
                            <?php else: ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="save_favourite">
                                    <input type="hidden" name="campaign_id" value="<?= e((string) $campaign['id']) ?>">
                                    <button type="submit" class="button button--primary button--small">Save Favourite</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if ($selectedCampaign !== null): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Campaign Details</p>
                        <h2><?= e($selectedCampaign['title']) ?></h2>
                    </div>
                    <span class="pill"><?= e($selectedCampaign['status']) ?></span>
                </div>

                <div class="detail-grid">
                    <div class="card card--soft">
                        <p class="muted"><?= e($selectedCampaign['fundraiser_name']) ?> · <?= e($selectedCampaign['category_name']) ?></p>
                        <p><?= e($selectedCampaign['story']) ?></p>
                        <div class="campaign-meta">
                            <span>Views: <?= e((string) $selectedCampaign['view_count']) ?></span>
                            <span>Shortlists: <?= e((string) $selectedCampaign['shortlist_count']) ?></span>
                            <span>Goal: <?= e(format_currency($selectedCampaign['funding_goal'])) ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress__bar" style="width: <?= e((string) progress_percent($selectedCampaign)) ?>%"></div>
                        </div>
                    </div>

                    <form method="post" class="card form-stack">
                        <input type="hidden" name="action" value="donate">
                        <input type="hidden" name="campaign_id" value="<?= e((string) $selectedCampaign['id']) ?>">

                        <div>
                            <p class="section-label">Donate</p>
                            <h3>Support this campaign</h3>
                        </div>

                        <label class="field">
                            <span>Amount</span>
                            <input type="number" name="amount" min="1" step="0.01" required>
                        </label>
                        <label class="field">
                            <span>Message</span>
                            <textarea name="message" rows="4"></textarea>
                        </label>

                        <button type="submit" class="button button--primary">Submit Donation</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <section class="layout-grid">
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Favourite List</p>
                        <h2>Saved fundraising activities</h2>
                    </div>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Category</th>
                                <th>Fundraiser</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dashboard['favourites'] as $favourite): ?>
                                <tr>
                                    <td><?= e($favourite['title']) ?></td>
                                    <td><?= e($favourite['category_name']) ?></td>
                                    <td><?= e($favourite['fundraiser_name']) ?></td>
                                    <td><?= e($favourite['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Donation History</p>
                        <h2>Search donation history and FSA progress</h2>
                    </div>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Progress</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dashboard['history'] as $donation): ?>
                                <tr>
                                    <td><?= e($donation['campaign_title']) ?></td>
                                    <td><?= e($donation['category_name']) ?></td>
                                    <td><?= e(format_currency($donation['amount'])) ?></td>
                                    <td><?= e((string) progress_percent($donation)) ?>%</td>
                                    <td><?= e(format_date($donation['donated_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
