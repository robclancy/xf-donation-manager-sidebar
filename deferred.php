<?php

$startTime = microtime(true);
$fileDir = dirname(__FILE__);

require __DIR__.'/vendor/autoload.php';
require($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize(__DIR__, $fileDir);
XenForo_Application::set('page_start_time', $startTime);

$dependencies = new XenForo_Dependencies_Public();
$dependencies->preLoadData();

$remaining = XenForo_Model::create('XenForo_Model_Deferred')->run(false);
$output = array('moreDeferred' => ($remaining ? true : false));

header('Content-Type: application/json; charset=UTF-8');
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
echo json_encode($output);
