<?php

$startTime = microtime(true);
$fileDir = dirname(__FILE__);

require __DIR__.'/vendor/autoload.php';
require($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize(__DIR__, $fileDir);
XenForo_Application::set('page_start_time', $startTime);

XenForo_CssOutput::run();
