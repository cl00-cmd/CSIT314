<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UAUpdateProfileC;

// BCE route:
// Boundary/UAUpdateProfile.php -> Controller/UAUpdateProfileC.php -> Entity/Profile.php.
// This page updates profile roles from profile_types, not individual user profile records.
require_login(['user_admin']);

// Boundary -> Controller.
$controller = new UAUpdateProfileC();
$roleCode = trim((string) ($_GET['role_code'] ?? $_POST['role_code'] ?? ''));
$profile = $roleCode !== '' ? $controller->searchProfile($roleCode) : null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Boundary -> Control: submit role edits and then reload the same profile role.
    $result = $controller->updateProfile($_POST);
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UAUpdateProfile.php', ['role_code' => (string) ($_POST['role_code'] ?? '')]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UAUpdateProfile</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <?php render_admin_topbar('UAUpdateProfile', 'UAUpdateProfile.php'); ?>
    <main class="page-shell">
        <?php render_flash_if_any(); ?>
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2><?= $profile !== null ? e($profile['role_label']) : 'Choose a profile role from UASearchProfile' ?></h2>
                </div>
            </div>

            <?php if ($profile !== null): ?>
                <form method="post" class="form-grid">
                    <input type="hidden" name="role_code" value="<?= e((string) $profile['role_code']) ?>">
                    <label class="field">
                        <span>Role Code</span>
                        <input type="text" value="<?= e($profile['role_code']) ?>" disabled>
                    </label>
                    <label class="field">
                        <span>Role Name</span>
                        <input type="text" name="role_label" value="<?= e($profile['role_label']) ?>" required>
                    </label>
                    <label class="field">
                        <span>Status</span>
                        <select name="status">
                            <option value="active" <?= selected_if($profile['status'], 'active') ?>>active</option>
                            <option value="suspended" <?= selected_if($profile['status'], 'suspended') ?>>suspended</option>
                        </select>
                    </label>
                    <button type="submit" class="button button--primary">Update Profile Role</button>
                </form>
            <?php elseif ($roleCode !== ''): ?>
                <div class="flash flash--error">
                    Profile role not found.
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
