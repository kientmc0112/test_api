<?php

require_once '../config/env.php';
require_once('../rakuten/Rakuten.php');

date_default_timezone_set('Asia/Tokyo');
(new DotEnv('../.env'))->load();

ini_set('display_errors', getenv('APP_DEBUG'));
ini_set('display_startup_errors', getenv('APP_DEBUG'));
error_reporting(E_ALL);

$secretKey = isset($_SERVER['SERVICE_SECRET']) && isset($_SERVER['LICENSE_KEY']) ? base64_encode($_SERVER['SERVICE_SECRET'] . ':' . $_SERVER['LICENSE_KEY']) : base64_encode('SP402021_d8kZdqMXW9l8qjQy:SL402021_3i1EDmCIcO5XsRST');
// var_dump($secretKey);
$rakuten = new Rakuten($secretKey);

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'POST';
var_dump($method);
$endpoint = isset($_REQUEST['endpoint']) ? $_REQUEST['endpoint'] : 'order/searchOrder/';
var_dump($endpoint);
// unset($_REQUEST['endpoint']);
// $params = isset($_REQUEST) ? $_REQUEST : [
//   'orderProgressList' => 100,
//   'dateType' => 1,
//   'OrderStatus' => 2,
//   'startDatetime' => date('Y-m-d H:i:s')->sub(new DateInterval("PT1H")),
//   'endDatetime' => date('Y-m-d H:i:s')
// ];
// var_dump($params);

// $response = $rakuten->request($method, $endpoint, $params);

// print_r($response);