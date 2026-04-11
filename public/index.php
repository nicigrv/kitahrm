<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Auto-detect subdirectory installation for Apache webspace setups.
//
// When the root .htaccess rewrites a request to public/index.php via an
// internal redirect, Apache sets REDIRECT_URL to the original REQUEST_URI
// (e.g. /kitahrm/dashboard). REQUEST_URI itself then points to the rewritten
// target (/kitahrm/public/index.php). Symfony's Request uses REQUEST_URI +
// SCRIPT_NAME to compute baseUrl and pathInfo, so we restore the original URL.
if (isset($_SERVER['REDIRECT_URL']) && isset($_SERVER['REDIRECT_QUERY_STRING'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_URL']
        . (strlen($_SERVER['REDIRECT_QUERY_STRING']) ? '?' . $_SERVER['REDIRECT_QUERY_STRING'] : '');
} elseif (isset($_SERVER['REDIRECT_URL'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_URL'];
}

// Adjust SCRIPT_NAME so Symfony computes the correct baseUrl.
// When installed in /kitahrm/, SCRIPT_NAME is /kitahrm/public/index.php.
// Stripping "/public" gives /kitahrm/index.php so baseUrl=/kitahrm and
// pathInfo=/dashboard — correct for any install depth.
if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], '/public/index.php')) {
    $_SERVER['SCRIPT_NAME'] = str_replace('/public/index.php', '/index.php', $_SERVER['SCRIPT_NAME']);
}
if (isset($_SERVER['PHP_SELF']) && str_contains($_SERVER['PHP_SELF'], '/public/index.php')) {
    $_SERVER['PHP_SELF'] = str_replace('/public/index.php', '/index.php', $_SERVER['PHP_SELF']);
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
