<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arCurrencies = array();
foreach (\travelsoft\currency\stores\Currencies::get() as $arCurrency) {
    
    $arCurrencies[$arCurrency['UF_ISO']] = $arCurrency['UF_ISO'];
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "OFFER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID предложения",
            "TYPE" => "STRING"
        ),
        "DATE" => array(
            "PARENT" => "BASE",
            "NAME" => "Дата начала услуги",
            "TYPE" => "STRING"
        ),
        "ORDER_DETAIL_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => "Страница детального просмотра заказа (можно использовать макрос #ORDER_ID#)",
            "TYPE" => "STRING"
        ),
        "CONVERT_IN_CURRENCY_ISO" => array(
            "PARENT" => "BASE",
            "NAME" => 'В какой валюте отображать цену',
            "TYPE" => "LIST",
            "VALUES" => $arCurrencies
        ),
        "INCLUDE_BOOTSTRAP" => array(
            "PARENT" => "BASE",
            "NAME" => "Подключить css bootstrap из CDN",
            "TYPE" => "CHECKBOX"
        ),
        "USE_AJAX_MODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Включить в режиме ajax",
            "TYPE" => "CHECKBOX",
            "REFRESH" => "Y"
        )
    )
);
?>