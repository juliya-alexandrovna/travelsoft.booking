<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Orders;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Bitrix\Main\Loader::includeModule("travelsoft.booking");

if (!travelsoft\booking\crmAccess()) {

    $APPLICATION->AuthForm('Доступ запрещен');
}

$TABLE_ID = "ORDERS_LIST";

$sort = new CAdminSorting($TABLE_ID, "ID", "DESC");
$list = new CAdminList($TABLE_ID, $sort);

if ($arOrdersId = $list->GroupAction()) {
    
    if ($_REQUEST['action_target'] == 'selected') {
        
        $arOrdersId = array_keys(Orders::get(array('select' => array('ID'))));
    }
    
    foreach ($arOrdersId as $ID) {
        
        switch ($_REQUEST['action']) {
            
            case "delete":
                
                Orders::delete($ID);
                
                break;
        }
    }
}

if ($_REQUEST["by"]) {

    $by = $_REQUEST["by"];
}

if ($_REQUEST["order"]) {

    $order = $_REQUEST["order"];
}

$arOrders = Orders::get(array("order" => array($by => $order)));

$arServicesId = array_map(function ($arItem) {
    return $arItem['UF_SERVICE_ID'];
}, $arOrders);

if ($arServicesId) {
    
    $arServices = travelsoft\booking\stores\Tours::get(array('filter' => array('ID' => $arServiceId), 'select' => array('ID', 'IBLOCK_ID')));
    
    if ($arServices && Bitrix\Main\Loader::includeModule('iblock')) {
        
        $arIblocksId = array_map(function ($arItem) {
            return $arItem['IBLOCK_ID'];
        }, $arServices);
                
        $dbIblocks = CIBlock::GetList(array(), array("ID" => $arIblocksId));
        while ($arRes = $dbIblocks->Fetch()) {
            
            $arIblocks[$arRes['ID']] = $arRes['IBLOCK_TYPE_ID'];
        }
    }
}

$arStatusesId = array_map(function ($arItem) {
    return $arItem['UF_STATUS_ID'];
}, $arOrders);

if ($arStatusesId) {
    $arStatuses = travelsoft\booking\stores\Statuses::get(array('filter' => array('ID' => $arStatusesId), 'select' => array('ID', 'UF_NAME')));
}

$dbResult = new CAdminResult($arOrders, $TABLE_ID);

$dbResult->NavStart();

$list->NavText($dbResult->GetNavPrint('Страницы'));

$list->AddHeaders(array(
    array(
        "id" => "ID",
        "content" => "Номер брони",
        "sort" => "ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_STATUS_ID",
        "content" => "Статус брони",
        "sort" => "UF_STATUS_ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_SERVICE_NAME",
        "content" => "Услуги",
        "sort" => "UF_SERVICE_NAME",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CNAME",
        "content" => "Клиент",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CPHONE",
        "content" => "Телефон клиента",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_DATE",
        "content" => "Дата создания брони",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_DATE_FROM",
        "content" => "Дата начала",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_DATE_TO",
        "content" => "Дата окончания",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_DURATION",
        "content" => "Продолжительность (дней)",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_ADULTS",
        "content" => "Количество взрослых",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CHILDREN",
        "content" => "Количество детей",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_COST",
        "content" => "Стоимость",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CURRENCY",
        "content" => "Валюта",
        "align" => "center",
        "default" => true
    )
));

while ($arResult = $dbResult->Fetch()) {

    $row = &$list->AddRow($arResult["ID"], $arResult);

    // ССЫЛКА НА ЭЛЕМЕНТ
    if (isset($arServices[$arResult['UF_SERVICE_ID']]) && isset($arIblocks[$arServices[$arResult['UF_SERVICE_ID']]['IBLOCK_ID']])) {

        $row->AddViewField("UF_SERVICE_NAME", '<a target="__blank" href="iblock_element_edit.php?IBLOCK_ID=' . $arServices[$arResult['UF_SERVICE_ID']]['IBLOCK_ID'] . '&type=' . $arIblocks[$arServices[$arResult['UF_SERVICE_ID']]['IBLOCK_ID']] . '&ID=' . $arServices[$arResult['UF_SERVICE_ID']]['ID'] . '&lang=' . LANG . '">' . $arResult["UF_SERVICE_NAME"] . '</a>');
    }

    $CNAME = $arResult['UF_CNAME'] . ' ' . $arResult['UF_CLAST_NAME'];

    if (strlen($arResult['UF_CEMAIL']) > 0) {
        $CNAME .= '<br>[' . $arResult['UF_CEMAIL'] . ']';
    }

    if ($arResult['UF_USER_ID']) {

        $row->AddViewField("UF_CNAME", '<a target="__blank" href="user_edit.php?lang=' . LANG . '&ID=' . $arResult['UF_USER_ID'] . '">' . $CNAME . '</a>');
    } else {

        $row->AddViewField("UF_CNAME", $CNAME);
    }
    
    if (isset($arStatuses[$arResult['UF_STATUS_ID']])) {

        $row->AddViewField("UF_STATUS_ID", $arStatuses[$arResult['UF_STATUS_ID']]["UF_NAME"]);
    }

    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => $list->ActionRedirect("travelsoft_crm_booking_order_edit.php?ORDER_ID=" . $arResult["ID"])
        ),
        array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Действительно хотите удалить бронь')) " . $list->ActionDoGroup($arResult["ID"], "delete")
        )
    ));
}

$list->AddFooter(array(
    array("title" => "Количество элементов", "value" => $dbResult->SelectedRowsCount()),
    array("counter" => true, "title" => "Количество выбранных элементов", "value" => 0)
));

$list->AddGroupActionTable(Array(
    "delete" => "Удалить"
));


$list->AddAdminContextMenu(array(array(
        'TEXT'	=> "Создать заказ",
        'TITLE'	=> "Создание заказа",
        'LINK'	=> 'travelsoft_crm_booking_order_edit?lang='.LANG,
        'ICON'	=> 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("Список заказов");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
