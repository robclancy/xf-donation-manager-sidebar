<?php

$startTime = microtime(true);
$fileDir = __DIR__.'/../../xenforo/install';
$rootPath = realpath($fileDir . '/..');
chdir($rootPath);

require($rootPath . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($rootPath . '/library');

XenForo_Application::initialize(__DIR__.'/../', $rootPath, false);
XenForo_Application::set('page_start_time', $startTime);

XenForo_Phrase::setPhrases(require($fileDir . '/language_en.php'));
XenForo_Template_Install::setFilePath($fileDir . '/templates');

$fc = new XenForo_FrontController(new XenForo_Dependencies_Install());
$fc->run();
