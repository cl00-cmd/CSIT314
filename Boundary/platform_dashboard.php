<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controller\PlatformController;

// BCE route:
// Boundary/platform_dashboard.php -> Controller/PlatformController.php
// -> Entity/CategoryEntity.php and Entity/ReportEntity.php.
// This Boundary collects category/report input and sends it to the Controller.
require_login(['platform_manager']);

// Boundary -> Controller.
$controller = new PlatformController();
$user = current_user();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $result = match ($action) {
        'create_category' => $controller->createCategory($_POST),
        'update_category' => $controller->updateCategory($_POST),
        default => ['success' => false, 'message' => 'Unknown action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('platform_dashboard.php');
}

$filters = [
    'period' => (string) ($_GET['period'] ?? 'monthly'),
    'search' => trim((string) ($_GET['search'] ?? '')),
];
$dashboard = $controller->getDashboardData($filters);
$editCategory = isset($_GET['edit_id']) ? $controller->getCategory((int) $_GET['edit_id']) : null;
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Manager Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <header class="topbar">
        <div>
            <p class="eyebrow">Platform Management</p>
            <h1>Categories and reporting</h1>
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

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <p class="section-label">Reports</p>
                    <h2>Daily, weekly, and monthly performance summary</h2>
                </div>
                <form method="get" class="inline-filters">
                    <select name="period">
                        <option value="daily" <?= selected_if($dashboard['period'], 'daily') ?>>daily</option>
                        <option value="weekly" <?= selected_if($dashboard['period'], 'weekly') ?>>weekly</option>
                        <option value="monthly" <?= selected_if($dashboard['period'], 'monthly') ?>>monthly</option>
                    </select>
                    <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Search categories">
                    <button type="submit" class="button button--ghost">Apply</button>
                </form>
            </div>

            <div class="stats-grid">
                <article class="stat-card">
                    <span>New Campaigns</span>
                    <strong><?= e((string) $dashboard['summary']['new_campaigns']) ?></strong>
                </article>
                <article class="stat-card">
                    <span>Completed Campaigns</span>
                    <strong><?= e((string) $dashboard['summary']['completed_campaigns']) ?></strong>
                </article>
                <article class="stat-card">
                    <span>Donations</span>
                    <strong><?= e((string) $dashboard['summary']['donations_count']) ?></strong>
                </article>
                <article class="stat-card">
                    <span>Donation Value</span>
                    <strong><?= e(format_currency($dashboard['summary']['donations_value'])) ?></strong>
                </article>
            </div>
        </section>

        <section class="layout-grid">
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Create Category</p>
                        <h2>Add a new FSA category</h2>
                    </div>
                </div>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="create_category">
                    <label class="field">
                        <span>Name</span>
                        <input type="text" name="name" required>
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <option value="active">active</option>
                            <option value="inactive">inactive</option>
                        </select>
                    </label>
                    <label class="field field--full">
                        <span>Description</span>
                        <textarea name="description" rows="5"></textarea>
                    </label>
                    <button type="submit" class="button button--primary">Create Category</button>
                </form>
            </section>

            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Update Category</p>
                        <h2>Edit an existing category</h2>
                    </div>
                    <?php if ($editCategory !== null): ?>
                        <span class="pill">Editing #<?= e((string) $editCategory['id']) ?></span>
                    <?php endif; ?>
                </div>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="category_id" value="<?= e((string) ($editCategory['id'] ?? '')) ?>">
                    <label class="field">
                        <span>Name</span>
                        <input type="text" name="name" value="<?= e($editCategory['name'] ?? '') ?>" required>
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <option value="active" <?= selected_if($editCategory['status'] ?? '', 'active') ?>>active</option>
                            <option value="inactive" <?= selected_if($editCategory['status'] ?? '', 'inactive') ?>>inactive</option>
                        </select>
                    </label>
                    <label class="field field--full">
                        <span>Description</span>
                        <textarea name="description" rows="5"><?= e($editCategory['description'] ?? '') ?></textarea>
                    </label>
                    <button type="submit" class="button button--primary">Update Category</button>
                </form>
            </section>
        </section>

        <section class="layout-grid">
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Category Register</p>
                        <h2>Manage FSA categories</h2>
                    </div>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Campaigns</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dashboard['categories'] as $category): ?>
                                <tr>
                                    <td><?= e($category['name']) ?></td>
                                    <td><?= e($category['status']) ?></td>
                                    <td><?= e((string) $category['campaign_count']) ?></td>
                                    <td><a class="button button--ghost button--small" href="?edit_id=<?= e((string) $category['id']) ?>">Edit</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel__header">
                    <div>
                        <p class="section-label">Category Breakdown</p>
                        <h2>Highest activity by category</h2>
                    </div>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Campaigns</th>
                                <th>Raised</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dashboard['breakdown'] as $row): ?>
                                <tr>
                                    <td><?= e($row['name']) ?></td>
                                    <td><?= e((string) $row['campaign_count']) ?></td>
                                    <td><?= e(format_currency($row['amount_raised'])) ?></td>
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
