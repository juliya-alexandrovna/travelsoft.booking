<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "BOOKING_REQUEST" => array(
            "PARENT" => "BASE",
            "NAME" => "Параметры необходимые для оформления заказа",
            "TYPE" => "STRING",
            "DEFAULT" => '={$_REQUEST["BOOKING_REQUEST"]}'
        ),
        "BOOKING_URL" => array(
            "PARENT" => "BASE",
            "NAME" => "URL перехода на страницу бронирования",
            "TYPE" => "STRING"
        ),
        "STYLE" => array(
            "PARENT" => "BASE",
            "NAME" => "СSS для костомной стилизации кнопки",
            "TYPE" => "STRING"
        ),
        "USE_FRAME_MODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Открывать страницу бронирования в попапе",
            "TYPE" => "CHECKBOX"
        )
    )
);
?>