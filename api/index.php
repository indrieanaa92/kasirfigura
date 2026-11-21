<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure writable /tmp storage & bootstrap cache directories exist on Vercel serverless filesystem.
$tmpStorage = '/tmp/storage';
$directories = [
    $tmpStorage . '/app/public',
    $tmpStorage . '/bootstrap',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/cache/laravel-excel',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
];

foreach ($directories as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

$envOverrides = [
    'APP_NAME' => 'Inul Wijaya Frame',
    'STORE_NAME' => 'INUL WIJAYA FRAME',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:Jv7JrWk2XUW/y4ZQfJ/aaoYH4QvH2SJzaJcFWndgyag=',
    'APP_LOCALE' => 'id',
    'APP_FALLBACK_LOCALE' => 'id',
    'LOG_CHANNEL' => 'stderr',
    'SESSION_DRIVER' => 'cookie',
    'SESSION_LIFETIME' => '720',
    'SESSION_ENCRYPT' => 'true',
    'SESSION_SECURE_COOKIE' => 'true',
    'SESSION_HTTP_ONLY' => 'true',
    'SESSION_SAME_SITE' => 'lax',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'DB_CONNECTION' => 'pgsql',
    'DB_HOST' => 'aws-1-ap-south-1.pooler.supabase.com',
    'DB_PORT' => '6543',
    'DB_DATABASE' => 'postgres',
    'DB_USERNAME' => 'postgres.ytrkyhjbffxsrktbpktr',
    'DB_PASSWORD' => 'kasirfigura',
    'DB_SSLMODE' => 'require',
    'DB_EMULATE_PREPARES' => 'true',
    'VIEW_COMPILED_PATH' => $tmpStorage . '/framework/views',
    'APP_SERVICES_CACHE' => $tmpStorage . '/bootstrap/services.php',
    'APP_PACKAGES_CACHE' => $tmpStorage . '/bootstrap/packages.php',
    'APP_CONFIG_CACHE' => $tmpStorage . '/bootstrap/config.php',
    'APP_ROUTES_CACHE' => $tmpStorage . '/bootstrap/routes.php',
    'APP_EVENTS_CACHE' => $tmpStorage . '/bootstrap/events.php',
];

foreach ($envOverrides as $key => $value) {
    $current = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($current === false || $current === '') {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}

// Serverless critical: always enforce cookie session and in-memory cache to prevent Supabase pooler lockups
putenv('SESSION_DRIVER=cookie');
$_ENV['SESSION_DRIVER'] = 'cookie';
$_SERVER['SESSION_DRIVER'] = 'cookie';

putenv('CACHE_STORE=array');
$_ENV['CACHE_STORE'] = 'array';
$_SERVER['CACHE_STORE'] = 'array';

require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($tmpStorage);

$app->handleRequest(Request::capture());
