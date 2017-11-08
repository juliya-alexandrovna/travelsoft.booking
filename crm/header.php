<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Bitrix\Main\Loader::includeModule("travelsoft.booking");

if (!\travelsoft\booking\crm\Utils::access()) {

    $APPLICATION->AuthForm('Доступ запрещен');
}

