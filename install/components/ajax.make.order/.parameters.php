<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID",
            "TYPE" => "STRING"
        ),
        "USE_AJAX_MODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Включить в режиме ajax",
            "TYPE" => "CHECKBOX",
            "REFRESH" => "Y"
        )
    )
);

if ($arCurrentValues['USE_AJAX_MODE'] == 'Y') {
    
    $arComponentParameters['PARAMETERS']['AJAX_PATH'] = array(
        
        "PARENT" => "BASE",
        "NAME" => "Относительный путь к ajax-обработчику",
        "TYPE" => "CHECKBOX",
        "REFRESH" => "Y"
    );
}
?>