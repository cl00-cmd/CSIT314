<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_ROOT', __DIR__);

spl_autoload_register(static function (string $className): void {
    if (strpos($className, 'App\\') !== 0) {
        return;
    }

    $relativeClass = substr($className, 4);
    $parts = explode('\\', $relativeClass, 2);
    if (count($parts) !== 2) {
        return;
    }

    [$namespaceRoot, $fileName] = $parts;
    $directoryMap = [
        'Config' => 'config',
        'Controller' => 'Controller',
        'Entity' => 'Entity',
    ];

    if (!isset($directoryMap[$namespaceRoot])) {
        return;
    }

    $filePath = APP_ROOT . DIRECTORY_SEPARATOR . $directoryMap[$namespaceRoot] . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $fileName) . '.php';
    if (is_file($filePath)) {
        require_once $filePath;
    }
});

require_once APP_ROOT . '/config/helpers.php';
