<?php 

require_once './../bootstrap/autoload.php';
require_once './../config/DBConfig.php';
require_once './../helpers/Client.php';

$client = new Client(SEARCH_TRADE_MULTI, 'post');

$params['ShopID'] = $_POST['ShopID'] ?? "tshop00054873";
$params['ShopPass'] = $_POST['ShopPass'] ?? "vdc87rhk";
$params['OrderID'] = $_POST['OrderID'] ?? "AS0000083-1";
$params['PayType'] = $_POST['PayType'] ?? "0";
$query = http_build_query($params);
$client->setHeader([
	'Content-Type: application/x-www-form-urlencoded'
]);
$client->request($query);

$clientStatus = $client->status;
if($clientStatus != 200) {
	echo 'GMO決済情報を参照できません。
		システム管理者にご連絡ください。';
	exit();
}

$body = $client->body;
parse_str($body, $result);
$result = is_array($result) ? $result : [];

$status = $result['Status'] ?? NULL;
$payType = $result['PayType'] ?? NULL;
$accessID = $result['AccessID'] ?? NULL;
$accessPass = $result['AccessPass'] ?? NULL;
$amount = $result['Amount'] ?? NULL;
$orderID = $params['OrderID'] ?? NULL;
$finishDate = $result['FinishDate'] ?? NULL;
$request = [];
$request[':orderID'] = $orderID;
$request[':accessID'] = $result['AccessID'] ?? NULL;
$request[':accessPass'] = $result['AccessPass'] ?? NULL;
$request[':status'] = $result['Status'] ?? NULL;
$request[':tranID'] = $result['TranID'] ?? NULL;
$request[':tranDate'] = $result['Trandate'] ?? NULL;
$request[':forward'] = $result['Forward'] ?? NULL;
$request[':method'] = $result['Method'] ?? NULL;
$request[':payTimes'] = $result['PayTimes'] ?? NULL;
$request[':approve'] = $result['Approve'] ?? NULL;
$request[':cvsCode'] = $result['CvsCode'] ?? NULL;
$request[':cvsConfNo'] = $result['CvsConfNo'] ?? NULL;
$request[':cvsReceiptNo'] = $result['CvsReceiptNo'] ?? NULL;
$request[':paymentTerm'] = $result['PaymentTerm'] ?? NULL;
$request[':receiptURL'] = $result['ReceiptURL'] ?? NULL;
$request[':payType'] = $result['PayType'] ?? NULL;
$request[':finishDate'] = $result['FinishDate'] ?? NULL;
$request[':payInfoNo'] = $result['PayInfoNo'] ?? NULL;
$request[':payMethod'] = $result['PayMethod'] ?? NULL;
$request[':cancelAmount'] = $result['CancelAmount'] ?? NULL;
$request[':cancelTax'] = $result['CancelTax'] ?? NULL;
$request[':docomoSettlementCode'] = $result['DocomoSettlementCode'] ?? NULL;
$request[':sbTrackingId'] = $result['SbTrackingId'] ?? NULL;
$request[':rakutenChargeID'] = $result['RakutenChargeID'] ?? NULL;
$request[':payPayTrackingID'] = $result['PayPayTrackingID'] ?? NULL;
$request[':payPayOrderID'] = $result['PayPayOrderID'] ?? NULL;
$request[':checkString'] = $result['CheckString'] ?? NULL;
// $request[':clientField1'] = $result['ClientField1'] ?? NULL;
$request[':clientField2'] = $result['ClientField2'] ?? NULL;
$request[':clientField3'] = $result['ClientField3'] ?? NULL;
$request[':errCode'] = $result['ErrCode'] ?? NULL;
$request[':errInfo'] = $result['ErrInfo'] ?? NULL;
$request[':processDate'] = $result['ProcessDate'] ?? date('Y-m-d H:i:s');
$request[':paymentDate'] = $request[':tranDate'];
$request[':paymentConfirmDate'] = 'NOW()';
switch($request[':payType']) {
	case '0': // クレジット the 
		$request[':tranDate'] = $result['TranDate'] ?? NULL;
		break;
	case '3': // コンビニ cua hang tien loi
		$request[':cvsCode'] = $result['Convinience'] ?? NULL;
		$request[':cvsConfNo'] = $result['ConfNo'] ?? NULL;
		$request[':cvsReceiptNo'] = $result['ReceiptNo'] ?? NULL;
		$request[':paymentDate'] = $finishDate ? date('Y-m-d H:i:s', $finishDate) : NULL;
		$request[':paymentConfirmDate'] = "NOW()";
		break;
	case '8': // au
		$request[':cancelAmount'] = $result['AuCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $result['AuCancelTax'] ?? NULL;
		break;
	case '9': // docomo
		$request[':cancelAmount'] = $result['DocomoCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $result['AuCancelTax'] ?? NULL;
		break;
	case '11': // SoftBank 
		$request[':cancelAmount'] = $result['SbCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $result['SbCancelTax'] ?? NULL; 
		break;
	case '45': // PayPay 
		$request[':cancelAmount'] = $result['PayPayCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $result['PayPayCancelTax'] ?? NULL; 
		break;
	case '50': // rakutenPay 
	case '99': // rakutenPay 
}

$db = new Database;
$conn = $db->connect();
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
$sql = "SELECT * FROM trn_order_payment od_p WHERE od_p.p_order_id = :orderID";
$sth = $conn->prepare($sql);

$sth->execute([':orderID' => $orderID]);
$res = $sth->fetch();
$dbAccessPass = $res?->accesspass;
$request[':accessPass'] = preg_match('/[0-9]|[A-Z]|[a-z]$/', $dbAccessPass) ? $dbAccessPass : $accessPass;

// update od_p
$sql = "
	UPDATE trn_order_payment SET 
		accessid = COALESCE(accessid, :accessID),
		accesspass = :accessPass,
		status = COALESCE(status, :status),
		tranid = COALESCE(tranid, :tranID),
		trandate = COALESCE(trandate, :tranDate),
		forward = COALESCE(forward, :forward),
		method = COALESCE(method, :method),
		paytimes = COALESCE(paytimes, :payTimes),
		approve = COALESCE(approve, :approve),
		cvscode = COALESCE(cvscode, :cvsCode),
		cvsconfno = COALESCE(cvsconfno, :cvsConfNo),
		cvsreceiptno = COALESCE(cvsreceiptno, :cvsReceiptNo),
		paymentterm = COALESCE(paymentterm, :paymentTerm),
		cvsreceipturl = COALESCE(cvsreceipturl, :receiptURL),
		paytype = COALESCE(paytype, :payType),
		finishdate = COALESCE(finishdate, :finishDate),
		payment_date = COALESCE(payment_date, :paymentDate),
		aupayinfono = COALESCE(aupayinfono, :payInfoNo),
		aupaymethod = COALESCE(aupaymethod, :payMethod),
		cancelamount = COALESCE(cancelamount, :cancelAmount),
		canceltax = COALESCE(canceltax, :cancelTax),
		docomosettlementcode = COALESCE(docomosettlementcode, :docomoSettlementCode),
		sbtrackingid = COALESCE(sbtrackingid, :sbTrackingId),
		rakutenchargeid = COALESCE(rakutenchargeid, :rakutenChargeID),
		paypaytrackingid  = COALESCE(paypaytrackingid, :payPayTrackingID),
		paypayorderid = COALESCE(paypayorderid, :payPayOrderID),
		checkstring = COALESCE(checkstring, :checkString),
		clientfield2 = COALESCE(clientfield2, :clientField2),
		clientfield3 = COALESCE(clientfield3, :clientField3),
		errcode = COALESCE(errcode, :errCode),
		errinfo = COALESCE(errinfo, :errInfo),
		payment_confirm_date = COALESCE(payment_confirm_date, :paymentConfirmDate),
		processdate = COALESCE(processdate, :processDate)
	WHERE p_order_id = :orderID
";
$sth = $conn->prepare($sql);
$sth->execute($request);

if(!in_array($status, ['CANCEL','VOID','RETURN','RETURNX'])) {
	echo 'この注文のGMO決済は既にキャンセルされています。 <br>	
		再注文対応を行ってください。';
	exit();
}
$db = new Database;
$conn = $db->connect();
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
$sql = "SELECT amount, tax FROM trn_order_payment od_p WHERE od_p.p_order_id = :OrderID";
$sth = $conn->prepare($sql);

$sth->execute([':OrderID' => $orderID]);
$res = $sth->fetch();
$dbAmount = $res?->amount ?? 0;
$dbTax = $res?->tax ?? 0;
$dbTotalAmount = $dbAmount + $dbTax;
if($dbTotalAmount > $amount) {
	if($payType != 3 || ($payType == 3 && $finishDate)) {
		echo '決済金額が不足しています。 <br>
		再決済対応を行ってください。';
	} else {
		echo '決済金額が不足しています。 <br>
		追加決済（増分）対応を行ってください。';
	}
	exit();
} elseif($dbTotalAmount < $amount) {
	$changeData = [];
	$changeData['ShopID'] = $params['ShopID'];
	$changeData['ShopPass'] = $params['ShopPass'];
	$changeData['AccessID'] = $accessID;
	$changeData['AccessPass'] = $accessPass;
	$changeData['OrderID'] = $orderID;
	$changeData['Amount'] = $dbTotalAmount;
	$changeData['Tax'] = 0;
	$changeUrl = match ($payType) {
		'0' => CHANGE_TRAN,
		'50' => RAKUTEN_CHANGE,
		default => null,
	};
	if($changeUrl) {
		$client = new Client($changeUrl, 'post');
		$client->setHeader([
			'Content-Type: application/x-www-form-urlencoded'
		]);
		$client->request(http_build_query($changeData));
		$msg = '決済金額を減額できませんでした。 <br>
				GMO決済状況をご確認ください';
		if($client->status != 200 ){
		    // エラー
		    createLog('local.Error ', $client->body);
		    echo $msg;
		    return false;
		}
		$dataMap = explode('&', $client->body);
		$data = [];
		foreach ($dataMap as $value) {
		    $splitArray = explode('=', $value, 2);
		    if (2 == count($splitArray)) {
		        $data[$splitArray[0]] = $splitArray[1];
		    }
		}
		if( array_key_exists( 'ErrCode', $data ) ){
		    // エラー
			createLog('local.Error ', $data);
			echo $msg;
		    return false;
		}
	}
}


$endpoint = match ($payType) {
	'0' => ALTER_TRAN,
	'8' => AU_CANCEL_RETURN,
	'9' => DOCOMO_CANCEL_RETURN,
	'11' => SB_CANCEL,
	'45' => PAYPAY_CANCEL_RETURN,
	'50' => RAKUTEN_CANCEL_RETURN,
	default => null,
};

$formData = [];
$formData['ShopID'] = $params['ShopID'];
$formData['ShopPass'] = $params['ShopPass'];
$formData['OrderID'] = $orderID;
$formData['AccessID'] = $accessID;
$formData['AccessPass'] = $accessPass;
$formData['Amount'] = $dbTotalAmount;
if($payType == '0') {
	$formData['JobCd'] = 'SALES';
}
if(!$endpoint) {
	return false;
}

$client = new Client($endpoint, 'post');
$client->setHeader([
	'Content-Type: application/x-www-form-urlencoded'
]);
$client->request(http_build_query($formData));

$msg = '決済金額が不足しています。 <br>
追加決済（増分）対応を行ってください。';
if($client->status != 200 ){
    // エラー
    createLog('local.Error ', $client->body);
    echo $msg;
    return false;
}

// レスポンスのエラーチェック
$dataMap = explode('&', $client->body);
$data = [];
foreach ($dataMap as $value) {
    $splitArray = explode('=', $value, 2);
    if (2 == count($splitArray)) {
        $data[$splitArray[0]] = $splitArray[1];
    }
}
if( array_key_exists( 'ErrCode', $data ) ){
    // エラー
	createLog('local.Error ', $data);
	echo $msg;
    return false;
}

// 正常

return true;
