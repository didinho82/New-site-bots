<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

define('APP_ROOT', dirname(__DIR__));

$configFile = APP_ROOT . '/config.php';
$config = file_exists($configFile)
    ? require $configFile
    : require APP_ROOT . '/config.php.example';

require __DIR__ . '/helpers.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/Telegram.php';
require __DIR__ . '/Repo.php';
require __DIR__ . '/ScriptRunner.php';

$pdo  = Database::connect($config);
$repo = new Repo($pdo);
$auth = new Auth($pdo);

return [$config, $pdo, $repo, $auth];
