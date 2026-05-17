<?php
declare(strict_types=1);

// Load system setup, shared fundraiser layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fundraiser_shared.php';

use App\Controller\FundraisingActivityC;
use App\Controller\FundraisingEditC;
use App\Controller\FundraisingSearchC;
use App\Controller\FundraisingSelectorC;
use App\Controller\FundraisingViewC;

// BCE route:
// Boundary/FundraisingUI.php -> Controller/FundraisingActivityC.php -> Entity/FundraisingActivity.php.
// Boundary/FundraisingUI.php -> Controller/FundraisingViewC.php -> Entity/FundraisingActivity.php.
// Boundary/FundraisingUI.php -> Controller/FundraisingEditC.php -> Entity/FundraisingActivity.php.
// Boundary/FundraisingUI.php -> Controller/FundraisingSelectorC.php -> Entity/FundraisingActivity.php.
// Boundary/FundraisingUI.php -> Controller/FundraisingSearchC.php -> Entity/FundraisingActivity.php.
// This Boundary handles the Fund Raiser create, view, update, delete, and search flows.
require_login(['fund_raiser']);

// Retrieves the logged-in fundraiser.
$user = current_user();
$userId = (int) $user['id'];

// Gets the selected command and checks if it is valid.
$allowedCommands = ['create', 'view', 'update', 'delete', 'search'];
$command = (string) ($_GET['command'] ?? 'create');

if (!in_array($command, $allowedCommands, true)) {
    $command = 'create';
}

// Boundary -> Controller for each fundraising activity function.
$createController = new FundraisingActivityC();
$viewController = new FundraisingViewC();
$editController = new FundraisingEditC();
$selectorController = new FundraisingSelectorC();
$searchController = new FundraisingSearchC();

// Handles create, update, and delete form submissions.
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

// Gets search input, current date, and selected activity ID.
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$today = date('Y-m-d');
$selectedActivityId = (int) ($_GET['activity_id'] ?? 0);

// Retrieves activities based on the selected command.
$activities = $command === 'search'
    ? $searchController->searchActivity($userId, $searchQuery)
    : $viewController->retrieveActivity($userId);

// Gets selected fundraising activity details if an activity is chosen.
$selectedActivity = $selectedActivityId > 0
    ? $viewController->getActivityDetails($userId, $selectedActivityId)
    : null;

// Gets form options for create or update forms.
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

    <!-- Fundraiser top navigation bar -->
    <?php render_fundraiser_topbar('FundraisingUI', $command); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_fundraiser_flash_if_any(); ?>

        <!-- Create fundraising activity section -->
        <?php if ($command === 'create'): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>Create fundraising activity</h2>
                    </div>
                </div>

                <!-- Create fundraising activity form -->
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

                            <!-- Display available categories -->
                            <?php foreach ($formOptions['categories'] as $category): ?>
                                <option value="<?= e((string) $category['id']) ?>">
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </label>

                    <label class="field">
                        <span>Service Type</span>
                        <select name="service_type" required>

                            <!-- Display available service types -->
                            <?php foreach ($formOptions['serviceTypes'] as $serviceType): ?>
                                <option value="<?= e($serviceType) ?>">
                                    <?= e($serviceType) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </label>

                    <label class="field">
                        <span>Start Date</span>
                        <input type="date" name="start_date" min="<?= e($today) ?>" required>
                    </label>

                    <label class="field">
                        <span>End Date</span>
                        <input type="date" name="end_date">
                    </label>

                    <button type="submit" class="button button--primary">
                        Create Activity
                    </button>
                </form>
            </section>

        <!-- View fundraising activity section -->
        <?php elseif ($command === 'view'): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>View fundraising activity</h2>
                    </div>

                    <?php if ($selectedActivity !== null): ?>
                        <a class="button button--ghost" href="FundraisingUI.php?command=view">
                            Back to Activities
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Display activity list when no activity is selected -->
                <?php if ($selectedActivity === null): ?>
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
                                            <a class="button button--ghost button--small"
                                               href="FundraisingUI.php?command=view&activity_id=<?= e((string) $activity['id']) ?>">
                                                Display Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Display selected activity details -->
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

        <!-- Update fundraising activity section -->
        <?php elseif ($command === 'update'): ?>
            <section class="panel">
                <div class="panel__header panel__header--stack">
                    <div>
                        <h2>Update fundraising activity</h2>
                    </div>

                    <?php if ($selectedActivity !== null): ?>
                        <a class="button button--ghost" href="FundraisingUI.php?command=update">
                            Back to Activities
                        </a>
                    <?php endif; ?>

                    <!-- Display activity list when no activity is selected for editing -->
                    <?php if ($selectedActivity === null): ?>
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
                                                <a class="button button--ghost button--small"
                                                   href="FundraisingUI.php?command=update&activity_id=<?= e((string) $activity['id']) ?>">
                                                    Edit Activity
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Update selected fundraising activity form -->
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

                                <!-- Display categories and select the current category -->
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

                                <!-- Display service types and select the current service type -->
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

                                <!-- Display available activity statuses -->
                                <?php foreach (['active', 'paused', 'completed'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= selected_if($selectedActivity['status'] ?? '', $status) ?>>
                                        <?= e($status) ?>
                                    </option>
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

                        <button type="submit" class="button button--primary">
                            Submit Updated Details
                        </button>
                    </form>

                <!-- Show message when no activity is selected for update -->
                <?php else: ?>
                    <div class="flash flash--error">
                        Choose a fundraising activity from the list to edit it.
                    </div>
                <?php endif; ?>
            </section>

        <!-- Delete fundraising activity section -->
        <?php elseif ($command === 'delete'): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>Delete fundraising activity</h2>
                    </div>

                    <a class="button button--ghost" href="FundraisingUI.php?command=search">
                        Back to Search
                    </a>
                </div>

                <!-- Display selected activity before delete confirmation -->
                <?php if ($selectedActivity !== null): ?>
                    <article class="card card--soft">
                        <h3><?= e($selectedActivity['title']) ?></h3>
                        <p><?= e($selectedActivity['story']) ?></p>
                        <p><strong>Goal Amount:</strong> <?= e(format_currency($selectedActivity['funding_goal'])) ?></p>
                        <p><strong>Service Type:</strong> <?= e($selectedActivity['service_type']) ?></p>
                        <p><strong>Status:</strong> <?= e($selectedActivity['status']) ?></p>

                        <!-- Confirm delete form -->
                        <form method="post" class="action-row">
                            <input type="hidden" name="command" value="delete">
                            <input type="hidden" name="activity_id" value="<?= e((string) $selectedActivity['id']) ?>">

                            <button type="submit" class="button button--primary">
                                Confirm Delete
                            </button>
                        </form>
                    </article>

                <!-- Show message when no activity is selected for delete -->
                <?php else: ?>
                    <div class="flash flash--error">
                        Choose a fundraising activity from Search Activity before deleting it.
                    </div>
                <?php endif; ?>
            </section>

        <!-- Search fundraising activity section -->
        <?php else: ?>
            <section class="panel">
                <div class="panel__header panel__header--stack">
                    <div>
                        <h2>Search fundraising activity</h2>
                    </div>

                    <!-- Search fundraising activity form -->
                    <form method="get" class="inline-filters">
                        <input type="hidden" name="command" value="search">
                        <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Search title, description, service, or category">

                        <button type="submit" class="button button--ghost">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Search result table -->
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

                            <!-- Display fundraising activities matching the search -->
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?= e($activity['title']) ?></td>
                                    <td><?= e($activity['service_type']) ?></td>
                                    <td><?= e($activity['status']) ?></td>
                                    <td><?= e(format_currency($activity['funding_goal'])) ?></td>

                                    <!-- Activity command buttons -->
                                    <td class="action-row">
                                        <a class="button button--ghost button--small"
                                           href="FundraisingUI.php?command=view&activity_id=<?= e((string) $activity['id']) ?>">
                                            View
                                        </a>

                                        <a class="button button--ghost button--small"
                                           href="FundraisingUI.php?command=update&activity_id=<?= e((string) $activity['id']) ?>">
                                            Update
                                        </a>

                                        <a class="button button--ghost button--small"
                                           href="FundraisingUI.php?command=delete&activity_id=<?= e((string) $activity['id']) ?>">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Show message when no fundraising activities are found -->
                            <?php if ($activities === []): ?>
                                <tr>
                                    <td colspan="5">
                                        No fundraising activities found.
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>