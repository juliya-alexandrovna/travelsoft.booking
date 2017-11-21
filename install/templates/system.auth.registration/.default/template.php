<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

ShowMessage($arParams["~AUTH_RESULT"]);

$APPLICATION->IncludeComponent(
        "bitrix:main.register", "bluebird", array(
    "AUTH" => "Y",
    "REQUIRED_FIELDS" => array(
        0 => "EMAIL",
        1 => "NAME",
        2 => "SECOND_NAME",
        3 => "LAST_NAME",
        4 => "PERSONAL_PHONE",
    ),
    "SET_TITLE" => "N",
    "SHOW_FIELDS" => array(
        0 => "EMAIL",
        2 => "NAME",
        3 => "SECOND_NAME",
        4 => "LAST_NAME",
        5 => "PERSONAL_BIRTHDAY",
        6 => "PERSONAL_PHONE",
    ),
    "SUCCESS_PAGE" => $APPLICATION->GetCurPageParam('', array('backurl')),
    "USER_PROPERTY" => array(
        0 => "UF_BIK",
        1 => "UF_ACCOUNT_CURRENCY",
        2 => "UF_ACTUAL_ADDRESS",
        3 => "UF_OKPO",
        4 => "UF_UNP",
        5 => "UF_CHECKING_ACCOUNT",
        6 => "UF_BANK_CODE",
        7 => "UF_BANK_ADDRESS",
        8 => "UF_BANK_NAME",
        9 => "UF_LEGAL_ADDRESS",
        10 => "UF_LEGAL_NAME",
        11 => "UF_PASS_NUMBER",
        12 => "UF_PASS_ISSUED_BY",
        13 => "UF_PASS_DATE_ISSUE",
        14 => "UF_PASS_ADDRESS",
        15 => "UF_PASS_ACTEND",
        16 => "UF_PASS_PERNUM",
        17 => "UF_PASS_SERIES"
    ),
    "USER_PROPERTY_NAME" => "",
    "USE_BACKURL" => "Y",
    "AUTH_URL" => $arResult["AUTH_AUTH_URL"],
    "COMPONENT_TEMPLATE" => "bluebird"
        ), false
);
