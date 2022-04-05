<?php 

define( 'BASE_PATH' , dirname(__DIR__));
define( 'TMP_PATH' , dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp');
define( 'LOG_PATH' , TMP_PATH . DIRECTORY_SEPARATOR . 'logs');

define( 'SEARCH_TRADE_MULTI' , 'https://pt01.mul-pay.jp/payment/SearchTradeMulti.idPass');
define( 'ALTER_TRAN' , 'https://pt01.mul-pay.jp/payment/AlterTran.idPass');
define( 'AU_CANCEL_RETURN' , 'https://pt01.mul-pay.jp/payment/AuCancelReturn.idPass');
define( 'DOCOMO_CANCEL_RETURN' , 'https://pt01.mul-pay.jp/payment/DocomoCancelReturn.idPass');
define( 'SB_CANCEL' , 'https://pt01.mul-pay.jp/payment/SbCancel.idPass');
define( 'PAYPAY_CANCEL_RETURN' , 'https://pt01.mul-pay.jp/payment/PaypayCancelReturn.idPass');
define( 'RAKUTEN_CANCEL_RETURN' , 'https://pt01.mulpay.jp/payment/RakutenpayCancelReturn.idPass');
define( 'CVS_CANCEL' , 'https://pt01.mul-pay.jp/payment/CvsCancel.idPass');
define( 'CHANGE_TRAN' , 'https://pt01.mul-pay.jp/payment/ChangeTran.idPass');
define( 'RAKUTEN_CHANGE' , 'https://pt01.mulpay.jp/payment/RakutenpayChange.idPass');
