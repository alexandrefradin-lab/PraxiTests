<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ── Serve static build assets ─────────────────────────────────────────────────
// OVH shared hosting (openresty) cannot follow symlinks for static files,
// so every request reaches PHP. We intercept /build/ paths here and stream
// the file directly with the proper Content-Type — no Laravel overhead.
(function () {
    $uri  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    if (strncmp($uri, '/build/', 7) !== 0) return;
    $file = __DIR__ . $uri;
    if (!is_file($file)) return;
    static $mime = [
        'js'    => 'application/javascript; charset=utf-8',
        'mjs'   => 'application/javascript; charset=utf-8',
        'css'   => 'text/css; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'map'   => 'application/json',
        'json'  => 'application/json',
    ];
    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=31536000, immutable');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
})();
// ─────────────────────────────────────────────────────────────────────────────

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
