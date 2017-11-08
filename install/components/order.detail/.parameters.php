<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "ORDER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID заказа",
            "TYPE" => "STRING",
            "DEFAULT" => '={$_REQUEST["ORDER_ID"]}'
        )
    )
);
?>