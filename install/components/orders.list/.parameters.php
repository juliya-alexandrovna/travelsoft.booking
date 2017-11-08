<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arTemplatesList = CComponentUtil::GetTemplatesList('bitrix:main.pagenavigation');

foreach ($arTemplatesList as $arTemplate) {
    $arTemplates[$arTemplate["NAME"]] = $arTemplate["NAME"];
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "PAGE_ORDERS_COUNT" => array(
            "PARENT" => "BASE",
            "NAME" => "Количество заказов на страницу",
            "TYPE" => "STRING"
        ),
        "DETAIL_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => "Детальная страница заказа",
            "TYPE" => "STRING",
            "DEFAULT" => "detail.php?ORDER_ID=#ORDER_ID#"
        ),
        "PAGE_TEMPLATE" => array(
            "PARENT" => "BASE",
            "NAME" => "Шаблон постраничной навигации",
            "TYPE" => "LIST",
            "VALUES" => $arTemplates
        )
    )
);
?>