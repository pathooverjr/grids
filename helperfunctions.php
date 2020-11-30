<?php

$isWindows = false;
if(strtolower(PHP_OS_FAMILY) === 'windows'){
    $isWindows = true;
}
// * GET/POST HELPER

function getkey($key)
{

    if (isset($_POST[$key])) {
        //logreq('#post #success', context_request($key,$_POST[$key]));
        return $_POST[$key];
    } elseif (isset($_GET[$key])) {
        //logreq('#get #success', context_request($key,$_GET[$key]));
        return $_GET[$key];
    }
    $request = ['#_post' => $_POST, '#_get' => $_GET];
    //$data = context_request($key,'notfound');
    //logreq('#getkey #fail', $data, $request);

    return null;
}


// * Tracy Debug dump
function tdump($var,$msg=null)
{
    if (!defined('AJAX')) 
    {
        if(empty($msg)) bdump($var);
        else bdump($var,$msg);
    }
}

// * LOGGING FUNCTIONS

function logreq($msg, $context = null)
{
    logit($msg, $context, 'req', 'info');
}

function logit($msg, $context = false, $logname = 'app', $type = 'info')
{
    if ($context && is_array($context)) return getlog($logname)->{$type}($msg, $context);
    getlog($logname)->{$type}($msg);
}
function haslog($logname)
{
    global $logs;
    if (array_key_exists($logname, $logs)) return true;
    return false;
}

function getlog($logname)
{
    global $logs;
    if (haslog($logname)) return $logs[$logname];
    else return addlog($logname);
}

function addlog($logname, $path = false, $extra = false)
{
    global $logs;
    $logfilepath=BASEPATH.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.$logname.'.log';

    if ($path) $logfilepath = $path;
    $logs[$logname] = new \Monolog\Logger($logname);
    $streamHandler = new \Monolog\Handler\StreamHandler($logfilepath, \Monolog\Logger::DEBUG);
    $streamHandler->setFormatter(new Monolog\Formatter\JsonFormatter());
    $logs[$logname]->pushHandler($streamHandler);
    $logs[$logname]->pushProcessor(function ($record) use ($extra) {
        $extra_always['env'] = $_ENV['APP_ENV'] ?? 'dev';
        $extra_always['version'] = $_ENV['APP_VERSION'] ?? '1.0';
        if (is_array($extra)) $extra_always = array_merge($extra_always, $extra);
        $record['extra'] = $extra_always;
        return $record;
    });
    return $logs[$logname];
}

function contextfl($file, $line)
{
    return ['file' => outfl($file, $line)];
}

function outfl($file, $line)
{
    return "vscode://file/${file}:${line}";
}

function out($var)
{
    return var_export($var, true);
}
