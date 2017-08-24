<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

header('Content-Type: application/json');

Bitrix\Main\Loader::includeModule("travelsoft.booking");

if (!travelsoft\booking\crm\Utils::access()) {
    
    echo \Bitrix\Main\Web\Json::encode(array('error' => 'access denided'));
    die;
}

$arResponse = array('error' => null, 'items' => array(), 'result' => null);
