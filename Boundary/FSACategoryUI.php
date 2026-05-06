<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/platform_shared.php';

use App\Controller\FSACreateCategoryC;
use App\Controller\FSASearchCategoryC;
use App\Controller\FSASuspendCategoryC;
use App\Controller\FSAUpdateCategoryC;
use App\Controller\FSAViewCategoryC;

// BCE routes for FSA category management:
// Boundary/FSACategoryUI.php -> Controller/FSACreateCategoryC.php -> Entity/FSACategory.php.
// Boundary/FSACategoryUI.php -> Controller/FSAViewCategoryC.php -> Entity/FSACategory.php.
// Boundary/FSACategoryUI.php -> Controller/FSAUpdateCategoryC.php -> Entity/FSACategory.php.
// Boundary/FSACategoryUI.php -> Controller/FSASuspendCategoryC.php -> Entity/FSACategory.php.
// Boundary/FSACategoryUI.php -> Controller/FSASearchCategoryC.php -> Entity/FSACategory.php.
require_login(['platform_manager']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $result = match ($action) {
        'create' => (new FSACreateCategoryC())->createCategory($_POST),
        'update' => (new FSAUpdateCategoryC())->updateCategory($_POST),
        'toggle_status' => (new FSASuspendCategoryC())->suspendCategory((int) ($_POST['categoryID'] ?? 0)),
        default => ['success' => false, 'message' => 'Unknown FSA category action.'],
    };

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('FSACategoryUI.php');
}

// Boundary -> Controller: pass the search keyword to the search controller.
$keyword = trim((string) ($_GET['keyword'] ?? ''));
$categories = (new FSASearchCategoryC())->searchCategory($keyword);
$viewCategory = isset($_GET['view_id']) ? (new FSAViewCategoryC())->getCategory((int) $_GET['view_id']) : null;
$editCategory = isset($_GET['edit_id']) ? (new FSAViewCategoryC())->getCategory((int) $_GET['edit_id']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_platform_topbar('Categories', 'categories'); ?>

    <main class="page-shell">
        <?php render_platform_flash_if_any(); ?>

        <section class="layout-grid">
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>Create Category</h2>
                    </div>
                </div>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="create">
                    <label class="field">
                        <span>Category Name</span>
                        <input type="text" name="categoryName" required>
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
                        <h2>Update Category</h2>
                    </div>
                    <?php if ($editCategory !== null): ?>
                        <span class="pill">Editing #<?= e((string) $editCategory['categoryID']) ?></span>
                    <?php endif; ?>
                </div>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="categoryID" value="<?= e((string) ($editCategory['categoryID'] ?? '')) ?>">
                    <label class="field">
                        <span>Category Name</span>
                        <input type="text" name="categoryName" value="<?= e($editCategory['categoryName'] ?? '') ?>" required>
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <option value="active" <?= selected_if($editCategory['status'] ?? '', 'active') ?>>active</option>
                            <option value="inactive" <?= selected_if($editCategory['status'] ?? '', 'inactive') ?>>inactive</option>
                            <option value="suspended" <?= selected_if($editCategory['status'] ?? '', 'suspended') ?>>suspended</option>
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

        <?php if ($viewCategory !== null): ?>
            <section class="panel">
                <div class="panel__header">
                    <div>
                        <h2>View Category</h2>
                    </div>
                </div>
                <div class="layout-grid">
                    <article class="card card--soft">
                        <h3><?= e($viewCategory['categoryName']) ?></h3>
                        <p><strong>Category ID:</strong> <?= e((string) $viewCategory['categoryID']) ?></p>
                        <p><strong>Status:</strong> <?= e($viewCategory['status']) ?></p>
                    </article>
                    <article class="card card--soft">
                        <h3>Description</h3>
                        <p><?= e($viewCategory['description'] ?? '-') ?></p>
                    </article>
                </div>
            </section>
        <?php endif; ?>

        <section class="panel">
            <div class="panel__header panel__header--stack">
                <div>
                    <h2>Search and Manage Categories</h2>
                </div>
                <form method="get" class="inline-filters">
                    <input type="text" name="keyword" value="<?= e($keyword) ?>" placeholder="Enter search keyword">
                    <button type="submit" class="button button--ghost">Search</button>
                    <a class="button button--ghost" href="FSACategoryUI.php">Clear</a>
                </form>
            </div>

            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Campaigns</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <strong><?= e($category['categoryName']) ?></strong><br>
                                    <span class="muted">#<?= e((string) $category['categoryID']) ?></span>
                                </td>
                                <td><?= e($category['description'] ?? '-') ?></td>
                                <td><?= e($category['status']) ?></td>
                                <td><?= e((string) $category['campaignCount']) ?></td>
                                <td class="action-row">
                                    <a class="button button--ghost button--small" href="?view_id=<?= e((string) $category['categoryID']) ?>">View</a>
                                    <a class="button button--ghost button--small" href="?edit_id=<?= e((string) $category['categoryID']) ?>">Edit</a>
                                    <form method="post">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="categoryID" value="<?= e((string) $category['categoryID']) ?>">
                                        <button type="submit" class="button <?= ($category['status'] ?? '') === 'suspended' ? 'button--ghost' : 'button--primary' ?> button--small">
                                            <?= ($category['status'] ?? '') === 'suspended' ? 'Reactivate' : 'Suspend' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($categories === []): ?>
                            <tr>
                                <td colspan="5">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
