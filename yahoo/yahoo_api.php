<?php

require_once '../vendor/autoload.php';
require_once '../config/env.php';

date_default_timezone_set('Asia/Tokyo');
(new DotEnv('../.env'))->load();

use YConnect\Constant\OIDConnectDisplay;
use YConnect\Constant\OIDConnectPrompt;
use YConnect\Constant\OIDConnectScope;
use YConnect\Constant\ResponseType;
use YConnect\Credential\ClientCredential;
use YConnect\YConnectClient;

switch ($_GET['mode']) {
    case 'login':
        showConsentScreen();
        break;

    case 'token':
        require_once '../config/DBConfig.php';
        getAccessToken();
        break;
}
die();

function showConsentScreen() {
    $store = isset($_GET['store']) ? $_GET['store'] : 'AUTOSTYLE';
    $redirectUri = getenv('Y_CALLBACK_URL') . $store;
    
    $cred = new ClientCredential($_GET['id'], $_GET['secret']);
    $client = new YConnectClient($cred);
    $client->requestAuth(
        $redirectUri,
        getenv('Y_STATE'),
        getenv('Y_NONCE'),
        ResponseType::CODE,
        [OIDConnectScope::OPENID],
        OIDConnectDisplay::SMART_PHONE,
        [OIDConnectPrompt::DEFAULT_PROMPT]
    );
}

function getAccessToken() {
    $db = new Database;
    $db->connect();
    $store = isset($_GET['store']) ? $_GET['store'] : 'AUTOSTYLE';
    $yahooMall = $db->findBy('ext_y_mall_setting', 'y_store_id', $store);
    $redirect_uri = getenv('Y_CALLBACK_URL') . $store;
    $client_id = $yahooMall->y_application_id;
    $client_secret = $yahooMall->y_secret;

    $cred = new ClientCredential($client_id, $client_secret);
    $client = new YConnectClient($cred);
    $code = $client->getAuthorizationCode(getenv('Y_STATE'));
    if ($code) {
        $client->requestAccessToken(
            $redirect_uri,
            $code
        );
        $data = [
            'y_access_token' => $client->getAccessToken(),
            'y_access_token_update_date' => date('Y-m-d H:i:s'),
            'y_refresh_token' => $client->getRefreshToken(),
            'y_refresh_token_update_date' => date('Y-m-d H:i:s'),
            'update_date' => date('Y-m-d H:i:s')
        ];
        $rowCount = $db->update('ext_y_mall_setting', 'y_store_id', $store, $data);
        if ($rowCount > 0) {
            header("Location: " . getenv('AS_Y_MALL_URL') . $yahooMall->ID);
            die();
        };
    }
}

?>