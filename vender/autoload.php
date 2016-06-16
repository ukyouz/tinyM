<?php
//autoload.php

// these 2 line for test. should be deleted after public.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

function autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = __DIR__ . "/../". str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    
    require $fileName;
}

spl_autoload_register('autoload');