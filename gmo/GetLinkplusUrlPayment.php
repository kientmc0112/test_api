<?php 

require_once './../bootstrap/autoload.php';
require_once './../config/DBConfig.php';
require_once './../helpers/Client.php';

header("Content-Type: application/json");
$client = new Client('https://pt01.mul-pay.jp/payment/GetLinkplusUrlPayment.json', 'post');
$client->setHeader([
	'Content-Type: application/json'
]);
// JSON文字列例
// $param = [
//     'configid'          => 'ASall',
//     'transaction'           => [
//         'OrderID'             => 'AS0000099-04',
//         'Amount'             => '12500',
//         'Tax'                     => '0',
//         'ClientField1'      => 'AS0000099'
//     ],
//     'geturlparam'         => [
//         'ShopID'                => 'tshop00054873',
//         'ShopPass'            => 'vdc87rhk',
//         'GuideMailSendFlag'   => '0',
//         'ThanksMailSendFlag'  => '1',
//         'SendMailAddress'     => 'k_mizuno@ss-sunsystem.co.jp',
//         'CustomerName'        => '水野　健司',
//         'TemplateNo'          => '1'
//     ],
//     'customer'          => [
//         'MailAddress'             => 'k_mizuno@ss-sunsystem.co.jp',
//         'CustomerName'            => '水野　健司',
//         'CustomerKana'            => 'ミズノ　ケンジ',
//         'TelNo'                   => '0120777777'
//     ]
// ];

$raw = file_get_contents('php://input');

if($raw) {
	$params = $raw;
} else {
	$array = [];
	$array['configid'] = $_POST['configid'] ?? '';
	$array['transaction'] = $_POST['transaction'] ?? '';
	$array['geturlparam'] = $_POST['geturlparam'] ?? '';
	$array['customer'] = $_POST['customer'] ?? '';
	$params = json_encode( $array );
}

$rest = $client->request($raw);

http_response_code($rest->status);

echo $rest->body;
