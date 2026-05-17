<?php
declare(strict_types=1);

// Starts the session when running in the browser and no session has started yet.
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Defines the root folder of the application.
define('APP_ROOT', __DIR__);

// Automatically loads App classes when they are used.
spl_autoload_register(static function (string $className): void {
    // Only autoload classes that belong to the App namespace.
    if (strpos($className, 'App\\') !== 0) {
        return;
    }

    // Removes the App\ prefix from the class name.
    $relativeClass = substr($className, 4);

    // Splits the namespace into main folder and file path.
    $parts = explode('\\', $relativeClass, 2);

    if (count($parts) !== 2) {
        return;
    }

    [$namespaceRoot, $fileName] = $parts;

    // Maps namespace names to actual project folders.
    $directoryMap = [
        'Config' => 'config',
        'Controller' => 'Controller',
        'Entity' => 'Entity',
    ];

    // Stops if the namespace folder is not supported.
    if (!isset($directoryMap[$namespaceRoot])) {
        return;
    }

    // Builds the full file path for the class.
    $filePath = APP_ROOT . DIRECTORY_SEPARATOR
        . $directoryMap[$namespaceRoot] . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $fileName) . '.php';

    // Loads the class file if it exists.
    if (is_file($filePath)) {
        require_once $filePath;
    }
});

// Loads shared helper functions used across the application.
require_once APP_ROOT . '/config/helpers.php';