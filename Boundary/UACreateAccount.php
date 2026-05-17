<?php
declare(strict_types=1);

// Load system setup, shared admin layout, and helper functions
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/admin_shared.php';

use App\Controller\UACreateAccountC;
use App\Controller\UACreateProfileC;

// BCE route for the main create-account action:
// Boundary/UACreateAccount.php -> Controller/UACreateAccountC.php -> Entity/Account.php.
// This Boundary only talks to Controllers; it does not call Entity classes directly.
require_login(['user_admin']);

// Handles create account form submission.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    // Boundary -> Control: send the submitted account/profile data to the controller.
    $controller = new UACreateAccountC();
    $result = $controller->createAccount($_POST);

    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    app_redirect('UACreateAccount.php');
}

// Boundary -> Controller to retrieve active profile roles for the role dropdown.
$profileController = new UACreateProfileC();

// Only active profile roles are shown because suspended roles should not be assigned to new accounts.
$profileTypes = $profileController->listProfiles(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UACreateAccount</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

    <!-- User Admin top navigation bar -->
    <?php render_admin_topbar('UACreateAccount', 'UACreateAccount.php'); ?>

    <main class="page-shell">

        <!-- Display success or error message if available -->
        <?php render_flash_if_any(); ?>

        <!-- Create user account section -->
        <section class="panel">
            <div class="panel__header">
                <div>
                    <h2>Create a user account so that more users can join in</h2>
                </div>
            </div>

            <!-- Create user account form -->
            <form method="post" class="form-grid">
                <label class="field">
                    <span>Username</span>
                    <input type="text" name="username" required>
                </label>

                <label class="field">
                    <span>Full Name</span>
                    <input type="text" name="full_name" required>
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" required>
                </label>

                <label class="field">
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>

                <label class="field">
                    <span>Role</span>
                    <select name="role">

                        <!-- Display active user profile roles -->
                        <?php foreach ($profileTypes as $profileType): ?>
                            <option value="<?= e($profileType['role_code']) ?>">
                                <?= e($profileType['role_label']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </label>

                <!-- Status fields are intentionally hidden from this Boundary page.
                     The Controller defaults new account/profile records to active. -->

                <label class="field">
                    <span>Phone</span>
                    <input type="text" name="phone">
                </label>

                <label class="field">
                    <span>Organisation</span>
                    <input type="text" name="organisation">
                </label>

                <label class="field">
                    <span>City</span>
                    <input type="text" name="city">
                </label>

                <label class="field field--full">
                    <span>Biography</span>
                    <textarea name="biography" rows="5"></textarea>
                </label>

                <button type="submit" class="button button--primary">
                    Create Account
                </button>
            </form>
        </section>
    </main>
</body>
</html>