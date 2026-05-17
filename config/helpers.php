<?php
declare(strict_types=1);

// Redirects the user to another page.
function app_redirect(string $path, array $query = []): void
{
    $location = $path;

    // Adds query string parameters when provided.
    if ($query !== []) {
        $location .= '?' . http_build_query($query);
    }

    // Sends redirect header only when running in the browser.
    if (PHP_SAPI !== 'cli') {
        header('Location: ' . $location);
        exit;
    }
}

// Returns the currently logged-in user from the session.
function current_user(): ?array
{
    return $_SESSION['auth_user'] ?? null;
}

// Ensures the user is logged in and has permission to access the page.
function require_login(array $allowedRoles = []): void
{
    $user = current_user();

    // Redirects to login page when no user session exists.
    if ($user === null) {
        set_flash('error', 'Please log in first.');
        app_redirect('login.php');
    }

    // Redirects user when their role is not allowed.
    if ($allowedRoles !== [] && !in_array($user['role'], $allowedRoles, true)) {
        set_flash('error', 'You do not have permission to open that page.');
        redirect_to_dashboard_for_role($user['role']);
    }
}

// Redirects users to their role dashboard.
function redirect_to_dashboard_for_role(string $role): void
{
    app_redirect(role_dashboard_path($role));
}

// Returns the dashboard page path based on user role.
function role_dashboard_path(string $role): string
{
    // Login is shared; only the post-login dashboard changes by role.
    return match ($role) {
        'user_admin' => 'admin_dashboard.php',
        'fund_raiser' => 'fundraiser_dashboard.php',
        'donor' => 'donor_dashboard.php',
        'platform_manager' => 'platform_dashboard.php',
        default => 'login.php',
    };
}

// Stores flash message into the session.
function set_flash(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

// Retrieves and removes flash message from the session.
function pull_flash(): ?array
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];

    unset($_SESSION['flash_message']);

    return $flash;
}

// Escapes output to prevent HTML injection.
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Returns selected attribute when values match.
function selected_if(mixed $actualValue, mixed $expectedValue): string
{
    return (string) $actualValue === (string) $expectedValue ? 'selected' : '';
}

// Formats currency values for display.
function format_currency(float|string|null $amount): string
{
    return '$' . number_format((float) $amount, 2);
}

// Formats dates into a readable format.
function format_date(?string $dateString): string
{
    if ($dateString === null || $dateString === '') {
        return '-';
    }

    $timestamp = strtotime($dateString);

    return $timestamp === false
        ? $dateString
        : date('d M Y', $timestamp);
}

// Calculates fundraising progress percentage.
function progress_percent(array $campaign): float
{
    $goal = (float) ($campaign['funding_goal'] ?? 0);
    $current = (float) ($campaign['current_amount'] ?? 0);

    if ($goal <= 0) {
        return 0.0;
    }

    return min(100.0, round(($current / $goal) * 100, 1));
}

// Returns the CSS class for flash message styling.
function flash_class(?string $type): string
{
    return match ($type) {
        'success' => 'flash flash--success',
        'error' => 'flash flash--error',
        default => 'flash',
    };
}