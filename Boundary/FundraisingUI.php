<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FundraisingActivityC;
use App\Controller\FundraisingEditC;
use App\Controller\FundraisingSearchC;
use App\Controller\FundraisingSelectorC;
use App\Controller\FundraisingViewC;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingActivityC.php / FundraisingViewC.php
// / FundraisingEditC.php / FundraisingSelectorC.php / FundraisingSearchC.php
// -> Entity/FundraisingActivity.php.
require_login(['fund_raiser']);

$user = current_user();
$userId = (int) $user['id'];
$allowedCommands = ['create', 'view', 'update', 'delete', 'search'];
$command = (string) ($_GET['command'] ?? 'create');
if (!in_array($command, $allowedCommands, true)) {
    $command = 'create';
}

$createController = new FundraisingActivityC();
$viewController = new FundraisingViewC();
$editController = new FundraisingEditC();
$selectorController = new FundraisingSelectorC();
$searchController = new FundraisingSearchC();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $postCommand = (string) ($_POST['command'] ?? $command);
    $result = match ($postCommand) {
        'create' => $createController->saveActivity($userId, $_POST),
        'update' => $editController->saveChanges($userId, $_POST),
        'delete' => $selectorController->deleteActivity($userId, (int) ($_POST['activity_id'] ?? 0)),
        default => ['success' => false, 'message' => 'Unknown command.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);

    $redirectQuery = ['command' => $postCommand];
    if (($postCommand === 'update' || $postCommand === 'view') && (int) ($_POST['activity_id'] ?? 0) > 0) {
        $redirectQuery['activity_id'] = (int) ($_POST['activity_id'] ?? 0);
    }
    app_redirect('FundraisingUI.php', $redirectQuery);
}

$searchQuery = trim((string) ($_GET['search'] ?? ''));
$selectedActivityId = (int) ($_GET['activity_id'] ?? 0);
$activities = $command === 'search'
    ? $searchController->searchActivity($userId, $searchQuery)
    : $viewController->retrieveActivity($userId);
$selectedActivity = $selectedActivityId > 0 ? $viewController->getActivityDetails($userId, $selectedActivityId) : null;
$formOptions = in_array($command, ['create', 'update'], true)
    ? ($command === 'create' ? $createController->getFormOptions() : $editController->getFormOptions())
    : ['categories' => [], 'serviceTypes' => []];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundraisingUI</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_fundraiser_topbar('FundraisingUI', $command); ?>
    <main class="page-shell">
        <?php render_fundraiser_flash_if_any(); ?>

        <?php if ($command === 'create'): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>Create fundraising activity</h2>
                    </div>
                </div>

                <form method="post" class="form-grid">
                    <input type="hidden" name="command" value="create">

                    <label class="field field--full">
                        <span>Title</span>
                        <input type="text" name="title" required>
                    </label>
                    <label class="field field--full">
                        <span>Description</span>
                        <textarea name="description" rows="5" required></textarea>
                    </label>
                    <label class="field">
                        <span>Goal Amount</span>
                        <input type="number" min="1" step="1" name="goal_amount" required>
                    </label>
                    <label class="field">
                        <span>Media</span>
                        <input type="text" name="media" placeholder="Optional media reference">
                    </label>
                    <label class="field">
                        <span>Category</span>
                        <select name="category_id" required>
                            <?php foreach ($formOptions['categories'] as $category): ?>
                                <option value="<?= e((string) $category['id']) ?>"><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Service Type</span>
                        <select name="service_type" required>
                            <?php foreach ($formOptions['serviceTypes'] as $serviceType): ?>
                                <option value="<?= e($serviceType) ?>"><?= e($serviceType) ?></option>
                            <?php endforeach; ?>
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

                    <button type="submit" class="button button--primary">Create Activity</button>
                </form>
            </section>
        <?php elseif ($command === 'view'): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>View fundraising activity</h2>
                    </div>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?= e($activity['title']) ?></td>
                                    <td><?= e($activity['service_type']) ?></td>
                                    <td><?= e($activity['status']) ?></td>
                                    <td>
                                        <a class="button button--ghost button--small" href="FundraisingUI.php?command=view&activity_id=<?= e((string) $activity['id']) ?>">Display Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($selectedActivity !== null): ?>
                    <div class="layout-grid">
                        <article class="card card--soft">
                            <h3><?= e($selectedActivity['title']) ?></h3>
                            <p><?= e($selectedActivity['story']) ?></p>
                            <p><strong>Goal Amount:</strong> <?= e(format_currency($selectedActivity['funding_goal'])) ?></p>
                            <p><strong>Service Type:</strong> <?= e($selectedActivity['service_type']) ?></p>
                            <p><strong>Status:</strong> <?= e($selectedActivity['status']) ?></p>
                            <p><strong>Media:</strong> <?= e($selectedActivity['media'] ?? '-') ?></p>
                        </article>
                    </div>
                <?php endif; ?>
            </section>
        <?php elseif ($command === 'update'): ?>
            <section class="panel">
                <div class="panel__header panel__header--stack">
                    <div>
                        <h2>Update fundraising activity</h2>
                    </div>
                    <div class="table-shell">
                        <table>
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td><?= e($activity['title']) ?></td>
                                        <td><?= e($activity['service_type']) ?></td>
                                        <td><?= e($activity['status']) ?></td>
                                        <td>
                                            <a class="button button--ghost button--small" href="FundraisingUI.php?command=update&activity_id=<?= e((string) $activity['id']) ?>">Edit Activity</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($selectedActivity !== null): ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="command" value="update">
                        <input type="hidden" name="activity_id" value="<?= e((string) $selectedActivity['id']) ?>">

                        <label class="field field--full">
                            <span>Title</span>
                            <input type="text" name="title" value="<?= e($selectedActivity['title']) ?>" required>
                        </label>
                        <label class="field field--full">
                            <span>Description</span>
                            <textarea name="description" rows="5" required><?= e($selectedActivity['story']) ?></textarea>
                        </label>
                        <label class="field">
                            <span>Goal Amount</span>
                            <input type="number" min="1" step="1" name="goal_amount" value="<?= e((string) $selectedActivity['funding_goal']) ?>" required>
                        </label>
                        <label class="field">
                            <span>Media</span>
                            <input type="text" name="media" value="<?= e($selectedActivity['media'] ?? '') ?>" placeholder="Optional media reference">
                        </label>
                        <label class="field">
                            <span>Category</span>
                            <select name="category_id" required>
                                <?php foreach ($formOptions['categories'] as $category): ?>
                                    <option value="<?= e((string) $category['id']) ?>" <?= selected_if($selectedActivity['category_id'] ?? '', $category['id']) ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="field">
                            <span>Service Type</span>
                            <select name="service_type" required>
                                <?php foreach ($formOptions['serviceTypes'] as $serviceType): ?>
                                    <option value="<?= e($serviceType) ?>" <?= selected_if($selectedActivity['service_type'] ?? '', $serviceType) ?>>
                                        <?= e($serviceType) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="field">
                            <span>Status</span>
                            <select name="status">
                                <?php foreach (['active', 'paused', 'completed'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= selected_if($selectedActivity['status'] ?? '', $status) ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="field">
                            <span>Start Date</span>
                            <input type="date" name="start_date" value="<?= e($selectedActivity['start_date'] ?? '') ?>" required>
                        </label>
                        <label class="field">
                            <span>End Date</span>
                            <input type="date" name="end_date" value="<?= e($selectedActivity['end_date'] ?? '') ?>">
                        </label>

                        <button type="submit" class="button button--primary">Submit Updated Details</button>
                    </form>
                <?php else: ?>
                    <div class="flash flash--error">Choose a fundraising activity from the list to edit it.</div>
                <?php endif; ?>
            </section>
        <?php elseif ($command === 'delete'): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>Delete fundraising activity</h2>
                    </div>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?= e($activity['title']) ?></td>
                                    <td><?= e($activity['service_type']) ?></td>
                                    <td><?= e($activity['status']) ?></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="command" value="delete">
                                            <input type="hidden" name="activity_id" value="<?= e((string) $activity['id']) ?>">
                                            <button type="submit" class="button button--primary button--small">Confirm Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php else: ?>
            <section class="panel">
                <div class="panel__header panel__header--stack">
                    <div>
                        <h2>Search fundraising activity</h2>
                    </div>
                    <form method="get" class="inline-filters">
                        <input type="hidden" name="command" value="search">
                        <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Search title, description, service, or category">
                        <button type="submit" class="button button--ghost">Search</button>
                    </form>
                </div>

                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Goal Amount</th>
                                <th>Commands</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?= e($activity['title']) ?></td>
                                    <td><?= e($activity['service_type']) ?></td>
                                    <td><?= e($activity['status']) ?></td>
                                    <td><?= e(format_currency($activity['funding_goal'])) ?></td>
                                    <td class="action-row">
                                        <a class="button button--ghost button--small" href="FundraisingUI.php?command=view&activity_id=<?= e((string) $activity['id']) ?>">View</a>
                                        <a class="button button--ghost button--small" href="FundraisingUI.php?command=update&activity_id=<?= e((string) $activity['id']) ?>">Update</a>
                                        <a class="button button--ghost button--small" href="FundraisingUI.php?command=delete&activity_id=<?= e((string) $activity['id']) ?>">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
