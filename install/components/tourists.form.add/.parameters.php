<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "ORDER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID путевки",
            "TYPE" => "STRING"
        ),
        "ORDER_LIST_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => "Страница списка зазазов",
            "TYPE" => "STRING"
        ),
        "ADDITING_ALLOWED_DAYS" => array(
            "PARENT" => "BASE",
            "NAME" => "Количество дней до начала тура для запрета добавления/редактирования информации по туристам",
            "TYPE" => "STRING",
            "DEFAULT" => "14"
        )
    )
);
?>