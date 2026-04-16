<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UpdateProfileController;
use App\Controller\ViewProfileDetailsController;

require_login(['user_admin']);

$viewController = new ViewProfileDetailsController();
$userId = (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0);
$profile = $userId > 0 ? $viewController->viewProfile($userId) : null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $controller = new UpdateProfileController();
    $result = $controller->updateProfile($_POST);
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('update_profilePg.php', ['id' => (int) ($_POST['user_id'] ?? 0)]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Profile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('Update User Profile', 'update_profilePg.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <p class="section-label">Update User Profile</p>
                    <h2><?= $profile !== null ? e($profile['full_name']) : 'Choose a user profile from View User Profiles' ?></h2>
                </div>
            </div>

            <?php if ($profile !== null): ?>
                <form method="post" class="form-grid">
                    <input type="hidden" name="user_id" value="<?= e((string) $profile['id']) ?>">
                    <label class="field">
                        <span>Phone</span>
                        <input type="text" name="phone" value="<?= e($profile['phone']) ?>">
                    </label>
                    <label class="field">
                        <span>Organisation</span>
                        <input type="text" name="organisation" value="<?= e($profile['organisation']) ?>">
                    </label>
                    <label class="field">
                        <span>City</span>
                        <input type="text" name="city" value="<?= e($profile['city']) ?>">
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <option value="active" <?= selected_if($profile['status'], 'active') ?>>active</option>
                            <option value="suspended" <?= selected_if($profile['status'], 'suspended') ?>>suspended</option>
                        </select>
                    </label>
                    <label class="field field--full">
                        <span>Biography</span>
                        <textarea name="biography" rows="6"><?= e($profile['biography']) ?></textarea>
                    </label>
                    <button type="submit" class="button button--primary">Update Profile</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
