<?php

require_once '../config/env.php';
require_once('../chatwork/Chatwork.php');

date_default_timezone_set('Asia/Tokyo');
(new DotEnv('../.env'))->load();

ini_set('display_errors', getenv('APP_DEBUG'));
ini_set('display_startup_errors', getenv('APP_DEBUG'));
error_reporting(E_ALL);

$token = isset($_SERVER['HTTP_X_CHATWORKTOKEN']) ? $_SERVER['HTTP_X_CHATWORKTOKEN'] : getenv('CW_API_TOKEN');
$chatwork = new Chatwork($token);
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$endpoint = isset($_REQUEST['endpoint']) ? $_REQUEST['endpoint'] : 'me';
unset($_REQUEST['endpoint']);
$params = isset($_REQUEST) ? $_REQUEST : [];

$response = $chatwork->request($method, $endpoint, $params);

return $response;