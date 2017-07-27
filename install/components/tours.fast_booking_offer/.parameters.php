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
        "ORDER_LIST_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => "Страница списка заказов",
            "TYPE" => "STRING"
        ),
        "PROPERTY_HOTEL_CODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Код свойства размещения инфоблока турпредложения",
            "TYPE" => "STRING",
            "DEFAULT" => "HOTEL"
        ),
        "PROPERTY_POINT_DEPARTURE_CODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Код свойства пункта отправления инфоблока турпредложения",
            "TYPE" => "STRING",
            "DEFAULT" => "POINT_DEPARTURE"
        ),
        "PROPERTY_COUNTRY_CODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Код свойства страны инфоблока турпредложения",
            "TYPE" => "STRING",
            "DEFAULT" => "COUNTRY"
        ),
        "PROPERTY_FOOD_CODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Код свойства питания инфоблока турпредложения",
            "TYPE" => "STRING",
            "DEFAULT" => "FOOD"
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