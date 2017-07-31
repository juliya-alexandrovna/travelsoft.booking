<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Orders;
use Bitrix\Main\Entity\ExpressionField;
use travelsoft\booking\stores\Users;

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

$getParams = array("order" => array($by => $order));

$usePageNavigation = true;
$navParams = CDBResult::GetNavParams(CAdminResult::GetNavSize(
                        $TABLE_ID, array('nPageSize' => 20)
        ));

if ($navParams['SHOW_ALL']) {

    $usePageNavigation = false;
} else {

    $navParams['PAGEN'] = (int) $navParams['PAGEN'];
    $navParams['SIZEN'] = (int) $navParams['SIZEN'];
}


if ($usePageNavigation) {

    $totalCount = Orders::get(array('select' => array(new ExpressionField('CNT', 'COUNT(1)'))), false)->fetch();

    $totalCount = (int) $totalCount['CNT'];

    if ($totalCount > 0) {

        $totalPages = ceil($totalCount / $navParams['SIZEN']);
        if ($navParams['PAGEN'] > $totalPages) {

            $navParams['PAGEN'] = $totalPages;
        }
        $getParams['limit'] = $navParams['SIZEN'];
        $getParams['offset'] = $navParams['SIZEN'] * ($navParams['PAGEN'] - 1);
    } else {

        $navParams['PAGEN'] = 1;
        $getParams['limit'] = $navParams['SIZEN'];
        $getParams['offset'] = 0;
    }
}

$arOrders = Orders::get($getParams);

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

if ($usePageNavigation) {

    $dbResult->NavStart($getParams['limit'], $navParams['SHOW_ALL'], $navParams['PAGEN']);
    $dbResult->NavRecordCount = $totalCount;
    $dbResult->NavPageCount = $totalPages;
    $dbResult->NavPageNomer = $navParams['PAGEN'];
} else {

    $dbResult->NavStart();
}

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
        "id" => "UF_CLIENT_NAME",
        "content" => "Клиент",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CLIENT_PHONE",
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
    
    if ($arResult['UF_USER_ID']) {
        
        $arUser = Users::getById($arResult['UF_USER_ID']);
    }

    if (strlen($arUser['NAME']) > 0) {
        $CNAME = $arUser['NAME'];
    }

    if (strlen($CNAME) > 0) {

        if (strlen($arUser['SECOND_NAME']) > 0) {
            $CNAME .= ' ' . $arUser['SECOND_NAME'];
        }

        if (strlen($arUser['LAST_NAME']) > 0) {
            $CNAME .= ' ' . $arUser['LAST_NAME'];
        }

        if (strlen($arUser['EMAIL']) > 0) {
            $CNAME .= '[' . $arUser['EMAIL'] . ']';
        }
    }

    $row->AddViewField("UF_CLIENT_NAME", '<a target="__blank" href="user_edit.php?lang=' . LANG . '&ID=' . $arResult['UF_USER_ID'] . '">' . $CNAME . '</a>');
    
    if ($arUser['PERSONAL_PHONE']) {
        
        $row->AddViewField("UF_CLIENT_PHONE", $arUser['PERSONAL_PHONE']);
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
        'TEXT' => "Создать заказ",
        'TITLE' => "Создание заказа",
        'LINK' => 'travelsoft_crm_booking_order_edit?lang=' . LANG,
        'ICON' => 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("Список заказов");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
