<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

require_once './../bootstrap/autoload.php';
require_once './../config/DBConfig.php';

createLog('local.INFO ', $_REQUEST);
$request = [];
$orderIds = $_REQUEST['OrderID'] ?? [];
$amount = $_REQUEST['Amount'] ?? NULL;
$tax = $_REQUEST['Tax'] ?? NULL;
$request[':accessID'] = $_REQUEST['AccessID'] ?? NULL;
$request[':accessPass'] = $_REQUEST['AccessPass'] ?? NULL;
$request[':status'] = $_REQUEST['Status'] ?? NULL;
$request[':tranID'] = $_REQUEST['TranID'] ?? NULL;
$request[':tranDate'] = $_REQUEST['Trandate'] ?? NULL;
$request[':forward'] = $_REQUEST['Forward'] ?? NULL;
$request[':method'] = $_REQUEST['Method'] ?? NULL;
$request[':payTimes'] = $_REQUEST['PayTimes'] ?? NULL;
$request[':approve'] = $_REQUEST['Approve'] ?? NULL;
$request[':cvsCode'] = $_REQUEST['CvsCode'] ?? NULL;
$request[':cvsConfNo'] = $_REQUEST['CvsConfNo'] ?? NULL;
$request[':cvsReceiptNo'] = $_REQUEST['CvsReceiptNo'] ?? NULL;
$request[':paymentTerm'] = $_REQUEST['PaymentTerm'] ?? NULL;
$request[':receiptURL'] = $_REQUEST['ReceiptURL'] ?? NULL;
$request[':payType'] = $_REQUEST['PayType'] ?? NULL;
$request[':finishDate'] = $_REQUEST['FinishDate'] ?? NULL;
$request[':payInfoNo'] = $_REQUEST['PayInfoNo'] ?? NULL;
$request[':payMethod'] = $_REQUEST['PayMethod'] ?? NULL;
$request[':cancelAmount'] = $_REQUEST['CancelAmount'] ?? NULL;
$request[':cancelTax'] = $_REQUEST['CancelTax'] ?? NULL;
$request[':docomoSettlementCode'] = $_REQUEST['DocomoSettlementCode'] ?? NULL;
$request[':sbTrackingId'] = $_REQUEST['SbTrackingId'] ?? NULL;
$request[':rakutenChargeID'] = $_REQUEST['RakutenChargeID'] ?? NULL;
$request[':payPayTrackingID'] = $_REQUEST['PayPayTrackingID'] ?? NULL;
$request[':payPayOrderID'] = $_REQUEST['PayPayOrderID'] ?? NULL;
$request[':checkString'] = $_REQUEST['CheckString'] ?? NULL;
// $request[':clientField1'] = $_REQUEST['ClientField1'] ?? NULL;
$request[':clientField2'] = $_REQUEST['ClientField2'] ?? NULL;
$request[':clientField3'] = $_REQUEST['ClientField3'] ?? NULL;
$request[':errCode'] = $_REQUEST['ErrCode'] ?? NULL;
$request[':errInfo'] = $_REQUEST['ErrInfo'] ?? NULL;
$request[':processDate'] = $_REQUEST['ProcessDate'] ?? date('Y-m-d H:i:s');
$request[':paymentDate'] = $request[':tranDate'];
$request[':paymentConfirmDate'] = 'NOW()';

switch($request[':payType']) {
	case '0': // クレジット the 
		$request[':tranDate'] = $_REQUEST['TranDate'] ?? NULL;
		break;
	case '3': // コンビニ cua hang tien loi
		$request[':cvsCode'] = $_REQUEST['Convinience'] ?? NULL;
		$request[':cvsConfNo'] = $_REQUEST['ConfNo'] ?? NULL;
		$request[':cvsReceiptNo'] = $_REQUEST['ReceiptNo'] ?? NULL;
		$request[':paymentDate'] = $finishDate ? date('Y-m-d H:i:s', $finishDate) : NULL;
		$request[':paymentConfirmDate'] = "NOW()";
		break;
	case '8': // au
		$request[':cancelAmount'] = $_REQUEST['AuCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $_REQUEST['AuCancelTax'] ?? NULL;
		break;
	case '9': // docomo
		$request[':cancelAmount'] = $_REQUEST['DocomoCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $_REQUEST['AuCancelTax'] ?? NULL;
		break;
	case '11': // SoftBank 
		$request[':cancelAmount'] = $_REQUEST['SbCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $_REQUEST['SbCancelTax'] ?? NULL; 
		break;
	case '45': // PayPay 
		$request[':cancelAmount'] = $_REQUEST['PayPayCancelAmount'] ?? NULL; 
		$request[':cancelTax'] = $_REQUEST['PayPayCancelTax'] ?? NULL; 
		break;
	case '50': // rakutenPay 
	case '99': // rakutenPay 
}

$orderIds = is_array($orderIds) ? $orderIds : [$orderIds];
$orderIdsStr = implode(',', array_map('addQuotes', $orderIds));

try {
	if(empty($orderIds)) {
		throw new Exception("GMO決済{$orderIdsStr}が正常に処理されていません。", 0);
	}
	$db = new Database;
	$conn = $db->connect();
	$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
	$sql = "SELECT p_order_id FROM trn_order_payment od_p WHERE od_p.p_order_id IN ({$orderIdsStr})";
	$sth = $conn->prepare($sql);

	$sth->execute();
	$res = $sth->fetchAll();
	$existOrders = [];
	foreach($res as $row) {
		$existOrders[] = $row->p_order_id;
	}

	$missings = array_diff($orderIds, $existOrders);
	$inExistOrders = implode(',', array_map('addQuotes', $existOrders));
	if(empty($existOrders)) {
		throw new Exception("GMO決済{$inExistOrders}が正常に処理されていません。", 0);
	}
	$sql = "
		UPDATE trn_order_payment SET 
			accessid = COALESCE(accessid, :accessID),
			accesspass = COALESCE(accesspass, :accessPass),
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
		WHERE p_order_id IN ({$inExistOrders})
	";
	$sth = $conn->prepare($sql);

	$sth->execute($request);

	if(!empty($missings)) {
		$missingsStr = implode(',', $missings);
		$transport = Transport::fromDsn(getenv('MAIL_DNS'));
		$mailer = new Mailer($transport);
		$email = (new Email())
		    ->from(getenv('MAIL_FROM'))
		    ->to(getenv('MAIL_TO'))
		    //->cc('cc@example.com')
		    //->bcc('bcc@example.com')
		    //->replyTo('fabien@example.com')
		    //->priority(Email::PRIORITY_HIGH)
		    ->subject('GMO決済連携不具合')
		    ->html("
		    	<p>GMO決済{$missingsStr}が正常に処理されていません。</p>
		    	<p>処理時間：{$request[':processDate']}</p>
	    	");

		$mailer->send($email);
	}

	http_response_code(200);
	header("Content-Type: application/json");
	echo "0";
} catch(\Exception $e) {
	$status = 400;
	createLog('local.Error ', $e->getMessage());
	header("Content-Type: application/json");
	http_response_code(400);
	echo json_encode([
		'status' => 400,
		'data' => $e->getMessage()
	]);
}

























 





