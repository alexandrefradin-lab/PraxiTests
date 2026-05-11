<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Détection install
if (!file_exists(__DIR__ . '/../.env') || !file_exists(__DIR__ . '/../storage/app/.installed')) {
    if (!str_ends_with($_SERVER['REQUEST_URI'] ?? '', 'install.php')) {
        header('Location: install.php');
        exit;
    }
}

// Maintenance mode
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
