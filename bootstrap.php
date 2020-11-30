<?php

use Symfony\Component\Dotenv\Dotenv;

require_once 'helperfunctions.php';

ini_set('display_errors', 'On');
date_default_timezone_set('America/Chicago');

ini_set('session.gc_probability', 1);


$basepath = getBasePath();

if (!defined('BASEPATH')) define('BASEPATH', $basepath);
$autoload_path = $basepath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!file_exists($autoload_path)) {
    throw new RuntimeException('Class loader in not available at ' . $autoload_path . ' ; Please check.');
}

require $autoload_path;

if (!(\PHP_SESSION_ACTIVE === session_status())) {
    session_start();
}

// https://tracy.nette.org/en/guide
if (!defined('AJAX')) {
    Tracy\Debugger::enable();
    // Visual Studio Code
    Tracy\Debugger::$editor = 'vscode://file/%file:%line';
    Tracy\Debugger::$strictMode = true;
}

if (!array_key_exists('APP_ENV', $_SERVER)) {
    $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] ?? null;
}

if ('prod' !== $_SERVER['APP_ENV']) {
    if (!class_exists(Dotenv::class)) {
        throw new RuntimeException('The "APP_ENV" environment variable is not set to "prod". Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
    }

    (new Dotenv(false))->loadEnv(getBasePath() . DIRECTORY_SEPARATOR . '.env');
}



$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] ?: $_ENV['APP_ENV'] ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

$_SERVER['DATA_DIR'] = $_SERVER['DATA_DIR'] ?? $_ENV['DATA_DIR'] ?? 'data';
$_SERVER['SQL_DIR'] = $_SERVER['SQL_DIR'] ?? $_ENV['SQL_DIR'] ?? 'sql';



$tmp_path = sys_get_temp_dir();
tdump($tmp_path, 'temp path');


//$extra['constants'] = ['AJAX' => defined('AJAX')];
//define('APP_LOG_EXTRA',$extra);

$logs = [];

$logpath = $basepath . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'grids.log';
addlog('error', $logpath);

$handler = new \Monolog\ErrorHandler($logs['error']);
$handler->registerErrorHandler();
$handler->registerExceptionHandler();
$handler->registerFatalHandler();




$templates = $basepath . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
$twig_loader = new \Twig\Loader\FilesystemLoader($templates, $basepath);
$grid_templates = $templates . 'grids' . DIRECTORY_SEPARATOR;
$twig_loader->addPath($grid_templates . 'config', 'config');
$twig_loader->addPath($grid_templates . 'base', 'base');
$twig_loader->addPath($grid_templates . 'table', 'table');
$twig_loader->addPath($grid_templates . 'basic', 'basic');

$twig = new \Twig\Environment(
    $twig_loader,
    [
        'cache' => $basepath . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'twig' . DIRECTORY_SEPARATOR,
        'debug' => true,
        'strict_variables' => false,
    ]
);

$twig->addExtension(new \Twig\Extension\DebugExtension());

define('APP_NAME', getName());

// * APPLICATION CONFIG FUNCTIONS
function getBasePath()
{
    $dir = $basePath = \dirname(__FILE__);
    while (!file_exists($dir . DIRECTORY_SEPARATOR . 'composer.json')) {
        if ($dir === \dirname($dir)) {
            return $basePath;
        }
        $dir = \dirname($dir);
    }
    return $dir;
}

function getName()
{
    $name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename(getBasePath()));
    if (ctype_digit($name[0])) {
        $name = '_' . $name;
    }
    return $name;
}
