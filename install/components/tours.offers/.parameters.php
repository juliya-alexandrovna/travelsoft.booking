<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if (!\Bitrix\Main\Loader::includeModule('new.travelsoft.currency')) {
    return;
}

$arCurrencies = array();
foreach (\travelsoft\currency\stores\Currencies::get() as $arCurrency) {
    
    $arCurrencies[$arCurrency['UF_ISO']] = $arCurrency['UF_ISO'];
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID",
            "TYPE" => "STRING",
            "MULTIPLE" => "Y"
        ),
        "DATES" => array(
            "PARENT" => "BASE",
            "NAME" => 'Даты заездов (dd.mm.yyyy)',
            "TYPE" => "STRING",
            "MULTIPLE" => "Y"
        ),
        "GLOBAL_FILTER_NAME" => array(
            "PARENT" => "BASE",
            "NAME" => 'Название фильтра',
            "TYPE" => "STRING"
        ),
        "SHOW_CURRENCY_ISO" => array(
            "PARENT" => "BASE",
            "NAME" => 'В каких валютах отображать цену',
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arCurrencies
        ),
    )
);
?>