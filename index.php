<?php
// index.php — Front Controller untuk EduLearn MVC

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/error_handler.php';

// PSR-4 Autoloader Sederhana
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Global Helpers
require_once __DIR__ . '/app/Helpers/functions.php';

// Start Session
\App\Core\Middleware::startSession();

// Initialize Application Router
$app = new \App\Core\App();
